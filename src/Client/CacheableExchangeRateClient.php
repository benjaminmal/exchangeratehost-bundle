<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateBundle\Client;

use Benjaminmal\ExchangeRateBundle\Model\Option\ConvertCurrencyOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\EuVatRatesOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\FluctuationDataOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\HistoricalRatesOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\LatestRatesOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\OptionInterface;
use Benjaminmal\ExchangeRateBundle\Model\Option\SupportedSymbolsOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\TimeSeriesDataOption;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CacheableExchangeRateClient implements ExchangeRateClientInterface
{
    public function __construct(
        private readonly ExchangeRateClientInterface $decoratedClient,
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

    public function getLatestRates(LatestRatesOption $options = new LatestRatesOption()): iterable
    {
        $cacheName = $this->createCacheName('latest_rates', $options);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($options): iterable {
            $this->setExpiration($item, $this->latestRatesExpiration);

            return $this->decoratedClient->getLatestRates($options);
        });
    }

    public function convertCurrency(string $fromCurrency, string $toCurrency, int $amount, ConvertCurrencyOption $options = new ConvertCurrencyOption()): float
    {
        $cacheName = $this->createCacheName('convert_currency', $fromCurrency, $toCurrency, $amount, $options);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($fromCurrency, $toCurrency, $amount, $options): float {
            $this->setExpiration($item, $this->convertCurrencyExpiration);

            return $this->decoratedClient->convertCurrency($fromCurrency, $toCurrency, $amount, $options);
        });
    }

    public function getHistoricalRates(\DateTimeImmutable $date, HistoricalRatesOption $options = new HistoricalRatesOption()): iterable
    {
        $cacheName = $this->createCacheName('historical_rates', $date, $options);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($date, $options): iterable {
            $this->setExpiration($item, $this->historicalRatesExpiration);

            return $this->decoratedClient->getHistoricalRates($date, $options);
        });
    }

    public function getTimeSeriesRates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, TimeSeriesDataOption $options = new TimeSeriesDataOption()): iterable
    {
        $cacheName = $this->createCacheName('timeseries_rates', $startDate, $endDate, $options);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($startDate, $endDate, $options): iterable {
            $this->setExpiration($item, $this->timeseriesRatesExpiration);

            return $this->decoratedClient->getTimeSeriesRates($startDate, $endDate, $options);
        });
    }

    public function getFluctuationData(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, FluctuationDataOption $options = new FluctuationDataOption()): iterable
    {
        $cacheName = $this->createCacheName('fluctuation_data', $startDate, $endDate, $options);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($startDate, $endDate, $options): iterable {
            $this->setExpiration($item, $this->fluctuationDataExpiration);

            return $this->decoratedClient->getFluctuationData($startDate, $endDate, $options);
        });
    }

    public function getSupportedCurrencies(SupportedSymbolsOption $options = new SupportedSymbolsOption()): iterable
    {
        $cacheName = $this->createCacheName('supported_currencies', $options);

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($options): iterable {
            $this->setExpiration($item, $this->supportedCurrenciesExpiration);

            return $this->decoratedClient->getSupportedCurrencies($options);
        });
    }

    public function getEuVatRates(EuVatRatesOption $options = new EuVatRatesOption()): iterable
    {
        $cacheName = $this->createCacheName('eu_vat_rates', $options);

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

    private function createCacheName(string $prefix, mixed ...$args): string
    {
        $parameters = [];
        foreach ($args as $parameter) {
            $parameters[] = match (true) {
                $parameter instanceof OptionInterface => $this->createCacheName('', ...(array) $parameter),
                $parameter instanceof \DateTimeImmutable => $parameter->format('Y-m-d'),
                $parameter instanceof \Traversable => implode('_', iterator_to_array($parameter)),
                is_array($parameter) => implode(',', $parameter),
                is_scalar($parameter) => (string) $parameter,
                default => null,
            };
        }

        $stringParams = implode('_', array_filter($parameters, static fn (mixed $value): bool => null !== $value && '' !== $value));

        if ('' === $prefix || '' === $stringParams) {
            return $prefix . $stringParams;
        }

        return $prefix . '_' . $stringParams;
    }
}
