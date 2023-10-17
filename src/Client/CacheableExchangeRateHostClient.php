<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateHostBundle\Client;

use Benjaminmal\ExchangeRateHostBundle\Model\Option\ConvertCurrencyOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\EuVatRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\FluctuationDataOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\HistoricalRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\LatestRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\OptionInterface;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\SupportedSymbolsOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\TimeSeriesDataOption;
use Symfony\Contracts\Cache\CacheInterface;

final class CacheableExchangeRateHostClient implements ExchangeRateHostClientInterface
{
    public function __construct(
        private readonly ExchangeRateHostClientInterface $decoratedClient,
        private readonly CacheInterface $latestRatesPool,
        private readonly CacheInterface $convertCurrencyPool,
        private readonly CacheInterface $historicalRatesPool,
        private readonly CacheInterface $timeseriesRatesPool,
        private readonly CacheInterface $fluctuationDataPool,
        private readonly CacheInterface $supportedCurrenciesPool,
        private readonly CacheInterface $euVatRatesPool,
    ) {
    }

    public function getLatestRates(?LatestRatesOption $options = null): iterable
    {
        return $this->latestRatesPool->get(
            $this->createCacheName('latest_rates', ['options' => $options]),
            fn (): iterable => $this->decoratedClient->getLatestRates($options),
        );
    }

    public function convertCurrency(string $fromCurrency, string $toCurrency, int|float $amount, ?ConvertCurrencyOption $options = null): int|float
    {
        return $this->convertCurrencyPool->get(
            $this->createCacheName('convert_currency', ['fromCurrency' => $fromCurrency, 'toCurrency' => $toCurrency, 'amount' => $amount, 'options' => $options]),
            fn (): int|float => $this->decoratedClient->convertCurrency($fromCurrency, $toCurrency, $amount, $options),
        );
    }

    public function getHistoricalRates(\DateTimeImmutable $date, ?HistoricalRatesOption $options = null): iterable
    {
        return $this->historicalRatesPool->get(
            $this->createCacheName('historical_rates', ['date' => $date, 'options' => $options]),
            fn (): iterable => $this->decoratedClient->getHistoricalRates($date, $options),
        );
    }

    public function getTimeSeriesRates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, ?TimeSeriesDataOption $options = null): iterable
    {
        return $this->timeseriesRatesPool->get(
            $this->createCacheName('timeseries_rates', ['startDate' => $startDate, 'endDate' => $endDate, 'options' => $options]),
            fn (): iterable => $this->decoratedClient->getTimeSeriesRates($startDate, $endDate, $options),
        );
    }

    public function getFluctuationData(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, ?FluctuationDataOption $options = null): iterable
    {
        return $this->fluctuationDataPool->get(
            $this->createCacheName('fluctuation_data', ['startDate' => $startDate, 'endDate' => $endDate, 'options' => $options]),
            fn (): iterable => $this->decoratedClient->getFluctuationData($startDate, $endDate, $options),
        );
    }

    public function getSupportedCurrencies(?SupportedSymbolsOption $options = null): iterable
    {
        return $this->supportedCurrenciesPool->get(
            $this->createCacheName('supported_currencies', ['options' => $options]),
            fn (): iterable => $this->decoratedClient->getSupportedCurrencies($options),
        );
    }

    public function getEuVatRates(?EuVatRatesOption $options = null): iterable
    {
        return $this->euVatRatesPool->get(
            $this->createCacheName('eu_vat_rates', ['options' => $options]),
            fn (): iterable => $this->decoratedClient->getEuVatRates($options),
        );
    }

    /**
     * @param mixed[] $parameters
     */
    private function createCacheName(string $prefix, array $parameters): string
    {
        $parameters = array_map(
            static fn (mixed $parameter): mixed => match (true) {
                $parameter instanceof OptionInterface => (array) $parameter,
                $parameter instanceof \DateTimeImmutable => $parameter->format('Y-m-d'),
                $parameter instanceof \Traversable => implode('_', iterator_to_array($parameter)),
                is_array($parameter) => implode(',', $parameter),
                is_scalar($parameter) => (string) $parameter,
                default => null,
            },
            $parameters,
        );

        return implode('_', [$prefix, md5(serialize($parameters))]);
    }
}
