<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateBundle\Client;

use Benjaminmal\ExchangeRateBundle\Model\Option\ConvertCurrencyOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\EuVatRatesOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\FluctuationDataOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\HistoricalRatesOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\LatestRatesOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\SupportedSymbolsOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\TimeSeriesDataOption;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceableExchangeRateClient implements ExchangeRateClientInterface
{
    private const STOPWATCH_CATEGORY = 'exchangerate-client';

    public function __construct(
        private readonly ExchangeRateClientInterface $decoratedClient,
        private readonly Stopwatch $stopwatch,
    ) {
    }

    public function getLatestRates(LatestRatesOption $options = new LatestRatesOption()): iterable
    {
        $this->stopwatch->start('getLatestRates', self::STOPWATCH_CATEGORY);
        $data = $this->decoratedClient->getLatestRates($options);
        $this->stopwatch->stop('getLatestRates');

        return $data;
    }

    public function convertCurrency(string $fromCurrency, string $toCurrency, int $amount, ConvertCurrencyOption $options = new ConvertCurrencyOption()): float
    {
        $this->stopwatch->start('convertCurrency', self::STOPWATCH_CATEGORY);
        $data = $this->decoratedClient->convertCurrency($fromCurrency, $toCurrency, $amount, $options);
        $this->stopwatch->stop('convertCurrency');

        return $data;
    }

    public function getHistoricalRates(\DateTimeImmutable $date, HistoricalRatesOption $options = new HistoricalRatesOption()): iterable
    {
        $this->stopwatch->start('getHistoricalRates', self::STOPWATCH_CATEGORY);
        $data = $this->decoratedClient->getHistoricalRates($date, $options);
        $this->stopwatch->stop('getHistoricalRates');

        return $data;
    }

    public function getTimeSeriesRates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, TimeSeriesDataOption $options = new TimeSeriesDataOption()): iterable
    {
        $this->stopwatch->start('getTimeSeriesRates', self::STOPWATCH_CATEGORY);
        $data = $this->decoratedClient->getTimeSeriesRates($startDate, $endDate, $options);
        $this->stopwatch->stop('getTimeSeriesRates');

        return $data;
    }

    public function getFluctuationData(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, FluctuationDataOption $options = new FluctuationDataOption()): iterable
    {
        $this->stopwatch->start('getFluctuationData', self::STOPWATCH_CATEGORY);
        $data = $this->decoratedClient->getFluctuationData($startDate, $endDate, $options);
        $this->stopwatch->stop('getFluctuationData');

        return $data;
    }

    public function getSupportedCurrencies(SupportedSymbolsOption $options = new SupportedSymbolsOption()): iterable
    {
        $this->stopwatch->start('getSupportedCurrencies', self::STOPWATCH_CATEGORY);
        $data = $this->decoratedClient->getSupportedCurrencies($options);
        $this->stopwatch->stop('getSupportedCurrencies');

        return $data;
    }

    public function getEuVatRates(EuVatRatesOption $options = new EuVatRatesOption()): iterable
    {
        $this->stopwatch->start('getEuVatRates', self::STOPWATCH_CATEGORY);
        $data = $this->decoratedClient->getEuVatRates($options);
        $this->stopwatch->stop('getEuVatRates');

        return $data;
    }
}
