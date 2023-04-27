<?php

declare(strict_types=1);

namespace Tests\Benjaminmal\ExchangeRateHostBundle\Client;

use Benjaminmal\ExchangeRateHostBundle\Client\ExchangeRateHostClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

trait TestClientHelperTrait
{
    public function getJson(string $file): string
    {
        return file_get_contents(dirname(__DIR__) . '/files/response/' . $file . '.json');
    }

    public function getExpectedResponse(string $file): mixed
    {
        return require dirname(__DIR__) . '/files/response/' . $file . '.php';
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
            ['convert', 'convertCurrency', ['USD', 'EUR', 1200], null, 'https://api.exchangerate.host/convert?from=USD&to=EUR&amount=1200', 1085.974844, 'convert_currency_6e0c1e626b224ee8d555529a52e53890', ['USD', 'EUR', 1400]],
            ['eu_vat', 'getEuVatRates', [], null, 'https://api.exchangerate.host/vat_rates', null, 'eu_vat_rates_79d2874bde1128e7567da03c8ac34400', []],
            ['fluctuation', 'getFluctuationData', ['2020-01-01', '2020-01-04'], null, 'https://api.exchangerate.host/fluctuation?start_date=2020-01-01&end_date=2020-01-04', null, 'fluctuation_data_db5afa7005c937adb81d972163d39e7f', ['2020-01-02', '2020-01-04']],
            ['historical', 'getHistoricalRates', ['2020-04-04'], null, 'https://api.exchangerate.host/2020-04-04', null, 'historical_rates_3ceeca060d53ceb6e5b1dae4765578a3', ['2021-01-01']],
            ['latest', 'getLatestRates', [], null, 'https://api.exchangerate.host/latest', null, 'latest_rates_28023f3d60e571a5e59b7826c35eca27', []],
            ['symbols', 'getSupportedCurrencies', [], null, 'https://api.exchangerate.host/symbols', null, 'supported_currencies_55d7a5d5d65495c388b7ece10b947554', []],
            ['timeseries', 'getTimeSeriesRates', ['2020-01-01', '2020-01-04'], null, 'https://api.exchangerate.host/timeseries?start_date=2020-01-01&end_date=2020-01-04', null, 'timeseries_rates_db5afa7005c937adb81d972163d39e7f', ['2021-04-05', '2021-08-12']],
        ];
    }

    private function createClient(iterable $responses): ExchangeRateHostClient
    {
        $httpClient = new Psr18Client(new MockHttpClient($responses));
        $uriFactory = $requestFactory = new Psr17Factory();

        return new ExchangeRateHostClient($httpClient, $uriFactory, $requestFactory);
    }
}
