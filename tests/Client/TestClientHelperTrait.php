<?php

declare(strict_types=1);

namespace Tests\Benjaminmal\ExchangeRateHostBundle\Client;

use Benjaminmal\ExchangeRateHostBundle\Client\ExchangeRateHostClient;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\ConvertCurrencyOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\FluctuationDataOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\HistoricalRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\LatestRatesOption;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

trait TestClientHelperTrait
{
    public function getJson(string $file): string
    {
        return file_get_contents(dirname(__DIR__) . '/files/' . $file . '.json');
    }

    public function getExpectedResponse(string $file): mixed
    {
        return require dirname(__DIR__) . '/files/' . $file . '.php';
    }

    public function createResponse(string $file): MockResponse
    {
        return new MockResponse($this->getJson($file), ['http_code' => 200, 'response_headers' => ['Content-Type: application/json']]);
    }

    public static function convertArguments(array $args): array
    {
        // Convert date
        foreach ($args as $key => $arg) {
            if (is_string($arg) && strtotime($arg)) {
                $args[$key] = \DateTimeImmutable::createFromFormat('Y-m-d', $arg);
            }
        }

        return $args;
    }

    public static function dataProvider(): array
    {
        return [
            ['response/convert', 'convertCurrency', ['USD', 'EUR', 1200], new ConvertCurrencyOption(places: 10, date: new \DateTimeImmutable('2020-12-01', new \DateTimeZone('UTC'))), 'https://api.exchangerate.host/convert?from=USD&to=EUR&amount=1200&date=2020-12-01&places=10', 1085.2598780252, 'convert_currency_e5d8bcc6f903040af595ffe97d24c6e3', ['USD', 'EUR', 1400]],
            ['response/convert_with_int', 'convertCurrency', ['USD', 'EUR', 1200], new ConvertCurrencyOption(places: 10, date: new \DateTimeImmutable('2020-12-01', new \DateTimeZone('UTC'))), 'https://api.exchangerate.host/convert?from=USD&to=EUR&amount=1200&date=2020-12-01&places=10', 1085, 'convert_currency_e5d8bcc6f903040af595ffe97d24c6e3', ['USD', 'EUR', 1400]],
            ['response/eu_vat', 'getEuVatRates', [], null, 'https://api.exchangerate.host/vat_rates', null, 'eu_vat_rates_1d11a4f652ab09181f9058d4bc9491a1', []],
            ['response/fluctuation', 'getFluctuationData', ['2020-01-01', '2020-01-04'], new FluctuationDataOption(symbols: new \ArrayObject(['EUR', 'USD'])), 'https://api.exchangerate.host/fluctuation?start_date=2020-01-01&end_date=2020-01-04&symbols=EUR%2CUSD', null, 'fluctuation_data_e0e0542edd98ca3378c399090973c1a6', ['2020-01-02', '2020-01-04']],
            ['response/historical', 'getHistoricalRates', ['2020-04-04'], new HistoricalRatesOption(symbols: 'EUR'), 'https://api.exchangerate.host/2020-04-04?symbols=EUR', null, 'historical_rates_e336dca0abcc1c240fa9b0c29e3ac8a8', ['2021-01-01']],
            ['response/latest', 'getLatestRates', [], new LatestRatesOption(symbols: new \ArrayObject(['EUR', 'USD'])), 'https://api.exchangerate.host/latest?symbols=EUR%2CUSD', null, 'latest_rates_241147c5f0f0d669f5983141620f8bcd', []],
            ['response/symbols', 'getSupportedCurrencies', [], null, 'https://api.exchangerate.host/symbols', null, 'supported_currencies_1d11a4f652ab09181f9058d4bc9491a1', []],
            ['response/timeseries', 'getTimeSeriesRates', ['2020-01-01', '2020-01-04'], null, 'https://api.exchangerate.host/timeseries?start_date=2020-01-01&end_date=2020-01-04', null, 'timeseries_rates_ceb7d8c1c5e7654fc17b3373c1d88232', ['2021-04-05', '2021-08-12']],
        ];
    }

    private function createClient(iterable $responses): ExchangeRateHostClient
    {
        $httpClient = new Psr18Client(new MockHttpClient($responses));
        $uriFactory = $requestFactory = new Psr17Factory();

        return new ExchangeRateHostClient($httpClient, $uriFactory, $requestFactory);
    }
}
