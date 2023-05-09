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
use Symfony\Contracts\Cache\ItemInterface;

final class CacheableExchangeRateHostClient implements ExchangeRateHostClientInterface
{
    public function __construct(
        private readonly ExchangeRateHostClientInterface $decoratedClient,
        private readonly CacheInterface $cache,
        private readonly int|string $latestRatesExpiration,
        private readonly int|string $convertCurrencyExpiration,
        private readonly int|string $historicalRatesExpiration,
        private readonly int|string $timeseriesRatesExpiration,
        private readonly int|string $fluctuationDataExpiration,
        private readonly int|string $supportedCurrenciesExpiration,
        private readonly int|string $euVatRatesExpiration,
    ) {
    }

    public function getLatestRates(?LatestRatesOption $options = null): iterable
    {
        $cacheName = $this->createCacheName('latest_rates', ['options' => $options]);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($options): iterable {
            $this->setExpiration($item, $this->latestRatesExpiration);

            return $this->decoratedClient->getLatestRates($options);
        });
    }

    public function convertCurrency(string $fromCurrency, string $toCurrency, int|float $amount, ?ConvertCurrencyOption $options = null): int|float
    {
        $cacheName = $this->createCacheName('convert_currency', ['fromCurrency' => $fromCurrency, 'toCurrency' => $toCurrency, 'amount' => $amount, 'options' => $options]);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($fromCurrency, $toCurrency, $amount, $options): float {
            $this->setExpiration($item, $this->convertCurrencyExpiration);

            return $this->decoratedClient->convertCurrency($fromCurrency, $toCurrency, $amount, $options);
        });
    }

    public function getHistoricalRates(\DateTimeImmutable $date, ?HistoricalRatesOption $options = null): iterable
    {
        $cacheName = $this->createCacheName('historical_rates', ['date' => $date, 'options' => $options]);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($date, $options): iterable {
            $this->setExpiration($item, $this->historicalRatesExpiration);

            return $this->decoratedClient->getHistoricalRates($date, $options);
        });
    }

    public function getTimeSeriesRates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, ?TimeSeriesDataOption $options = null): iterable
    {
        $cacheName = $this->createCacheName('timeseries_rates', ['startDate' => $startDate, 'endDate' => $endDate, 'options' => $options]);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($startDate, $endDate, $options): iterable {
            $this->setExpiration($item, $this->timeseriesRatesExpiration);

            return $this->decoratedClient->getTimeSeriesRates($startDate, $endDate, $options);
        });
    }

    public function getFluctuationData(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, ?FluctuationDataOption $options = null): iterable
    {
        $cacheName = $this->createCacheName('fluctuation_data', ['startDate' => $startDate, 'endDate' => $endDate, 'options' => $options]);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($startDate, $endDate, $options): iterable {
            $this->setExpiration($item, $this->fluctuationDataExpiration);

            return $this->decoratedClient->getFluctuationData($startDate, $endDate, $options);
        });
    }

    public function getSupportedCurrencies(?SupportedSymbolsOption $options = null): iterable
    {
        $cacheName = $this->createCacheName('supported_currencies', ['options' => $options]);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($options): iterable {
            $this->setExpiration($item, $this->supportedCurrenciesExpiration);

            return $this->decoratedClient->getSupportedCurrencies($options);
        });
    }

    public function getEuVatRates(?EuVatRatesOption $options = null): iterable
    {
        $cacheName = $this->createCacheName('eu_vat_rates', ['options' => $options]);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($options): iterable {
            $this->setExpiration($item, $this->euVatRatesExpiration);

            return $this->decoratedClient->getEuVatRates($options);
        });
    }

    private function setExpiration(ItemInterface $item, int|string $expiration): void
    {
        if (is_string($expiration)) {
            $item->expiresAt(new \DateTimeImmutable($expiration, new \DateTimeZone('UTC')));
        } else {
            $item->expiresAfter($expiration);
        }
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

        $strParameters = md5(serialize($parameters));

        return implode('_', [$prefix, $strParameters]);
    }
}
