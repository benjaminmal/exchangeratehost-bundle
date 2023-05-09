<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateHostBundle\Client;

use Benjaminmal\ExchangeRateHostBundle\Exception\ClientException;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\ConvertCurrencyOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\EuVatRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\FluctuationDataOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\HistoricalRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\LatestRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\OptionInterface;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\SupportedSymbolsOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\TimeSeriesDataOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Output\FluctuationData;
use Benjaminmal\ExchangeRateHostBundle\Model\Output\SymbolData;
use Benjaminmal\ExchangeRateHostBundle\Model\Output\VatRates;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Webmozart\Assert\Assert;

final class ExchangeRateHostClient implements ExchangeRateHostClientInterface
{
    private const BASE_URL = 'https://api.exchangerate.host/';
    private const DATE_FORMAT = 'Y-m-d';

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly UriFactoryInterface $uriFactory,
        private readonly RequestFactoryInterface $requestFactory,
    ) {
    }

    public function convertCurrency(string $fromCurrency, string $toCurrency, int|float $amount, ?ConvertCurrencyOption $options = null): float
    {
        $uri = $this->createUri();
        $uri = $uri
            ->withPath('convert')
            ->withQuery($this->createQuery($options, ['from' => $fromCurrency, 'to' => $toCurrency, 'amount' => $amount]))
        ;

        $data = $this->getData($uri);
        $result = $data['result'] ?? throw new \UnexpectedValueException('Cannot found results.');
        Assert::float($result);

        return $result;
    }

    public function getLatestRates(?LatestRatesOption $options = null): \ArrayObject
    {
        $uri = $this->createUri();
        $uri = $uri
            ->withPath('latest')
            ->withQuery($this->createQuery($options))
        ;

        $data = $this->getData($uri);
        $rates = $data['rates'] ?? throw new \UnexpectedValueException('Cannot found rates.');
        Assert::isArray($rates);

        return new \ArrayObject($rates);
    }

    public function getFluctuationData(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, ?FluctuationDataOption $options = null): \ArrayObject
    {
        $uri = $this->createUri();
        $uri = $uri
            ->withPath('fluctuation')
            ->withQuery($this->createQuery($options, ['start_date' => $startDate, 'end_date' => $endDate]))
        ;
        $data = $this->getData($uri);
        $rates = $data['rates'] ?? throw new \UnexpectedValueException('Cannot found rates.');

        Assert::isArray($rates);

        $array = new \ArrayObject();
        foreach ($rates as $currency => $datum) {
            $fluctuationData = new FluctuationData();
            $fluctuationData->startRate = $datum['start_rate'] ?? null;
            $fluctuationData->endRate = $datum['end_rate'] ?? null;
            $fluctuationData->change = $datum['change'] ?? null;
            $fluctuationData->changePct = $datum['change_pct'] ?? null;

            $array[$currency] = $fluctuationData;
        }

        return $array;
    }

    public function getHistoricalRates(\DateTimeImmutable $date, ?HistoricalRatesOption $options = null): \ArrayObject
    {
        $uri = $this->createUri();
        $uri = $uri
            ->withPath($date->format(self::DATE_FORMAT))
            ->withQuery($this->createQuery($options))
        ;

        $data = $this->getData($uri);
        $rates = $data['rates'] ?? throw new \UnexpectedValueException('Cannot found rates.');
        Assert::isArray($rates);

        return new \ArrayObject($rates);
    }

    public function getTimeSeriesRates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, ?TimeSeriesDataOption $options = null): \ArrayObject
    {
        $uri = $this->createUri();
        $uri = $uri
            ->withPath('timeseries')
            ->withQuery($this->createQuery($options, ['start_date' => $startDate, 'end_date' => $endDate]))
        ;

        $data = $this->getData($uri);
        $rates = $data['rates'] ?? throw new \UnexpectedValueException('Cannot found rates.');
        Assert::isArray($rates);

        return new \ArrayObject($rates);
    }

    public function getSupportedCurrencies(?SupportedSymbolsOption $options = null): \ArrayObject
    {
        $uri = $this->createUri();
        $uri = $uri
            ->withPath('symbols')
            ->withQuery($this->createQuery($options))
        ;

        $data = $this->getData($uri);
        $symbols = $data['symbols'] ?? throw new \UnexpectedValueException('Cannot found symbols.');
        Assert::isArray($symbols);

        $symbolsDatas = new \ArrayObject();
        foreach ($symbols as $currency => $datum) {
            $symbolData = new SymbolData();
            $symbolData->description = $datum['description'] ?? null;
            $symbolData->code = $datum['code'] ?? null;

            $symbolsDatas[$currency] = $symbolData;
        }

        return $symbolsDatas;
    }

    public function getEuVatRates(?EuVatRatesOption $options = null): \ArrayObject
    {
        $uri = $this->createUri();
        $uri = $uri
            ->withPath('vat_rates')
            ->withQuery($this->createQuery($options))
        ;

        $data = $this->getData($uri);
        $rates = $data['rates'] ?? throw new \UnexpectedValueException('Cannot found rates.');

        Assert::isArray($rates);

        $vatRatesData = new \ArrayObject();
        foreach ($rates as $countryCode => $datum) {
            $vatRates = new VatRates();
            $vatRates->countryName = $datum['country_name'] ?? null;
            $vatRates->standardRate = $datum['standard_rate'] ?? null;
            $vatRates->reducedRates = $datum['reduced_rates'] ?? null;
            $vatRates->superReducedRates = $datum['super_reduced_rates'] ?? null;
            $vatRates->parkingRates = $datum['parking_rates'] ?? null;

            $vatRatesData[$countryCode] = $vatRates;
        }

        return $vatRatesData;
    }

    private function createUri(): UriInterface
    {
        return $this->uriFactory->createUri(self::BASE_URL);
    }

    /**
     * @param array<string, mixed> $other
     */
    private function createQuery(?OptionInterface $option, array $other = []): string
    {
        $data = [...$other, ...(array) $option];

        // Convert
        foreach ($data as $name => $value) {
            $data[$name] = match (true) {
                $value instanceof \DateTimeImmutable => $value->format(self::DATE_FORMAT),
                $value instanceof \Traversable => implode(',', iterator_to_array($value)),
                is_array($value) => implode(',', $value),
                null === $value => null,
                default => (string) $value,
            };
        }

        return http_build_query(array_filter($data), '', '&', \PHP_QUERY_RFC3986);
    }

    private function getData(UriInterface $uri): \ArrayObject
    {
        $request = $this->requestFactory->createRequest('GET', $uri);
        $response = $this->httpClient->sendRequest($request);
        if ($response->getStatusCode() >= 400) {
            throw new ClientException($response->getReasonPhrase(), $response->getStatusCode());
        }

        $data = json_decode((string) $response->getBody(), true, flags: \JSON_THROW_ON_ERROR);

        return new \ArrayObject($data);
    }
}
