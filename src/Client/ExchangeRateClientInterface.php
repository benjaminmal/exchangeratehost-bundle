<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateBundle\Client;

use Benjaminmal\ExchangeRateBundle\Exception\ClientException;
use Benjaminmal\ExchangeRateBundle\Model\Option\ConvertCurrencyOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\EuVatRatesOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\FluctuationDataOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\HistoricalRatesOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\LatestRatesOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\SupportedSymbolsOption;
use Benjaminmal\ExchangeRateBundle\Model\Option\TimeSeriesDataOption;
use Benjaminmal\ExchangeRateBundle\Model\Output\FluctuationData;
use Benjaminmal\ExchangeRateBundle\Model\Output\SymbolData;
use Benjaminmal\ExchangeRateBundle\Model\Output\VatRates;

interface ExchangeRateClientInterface
{
    /**
     * Get the latest foreign exchange reference rates. Latest endpoint will return
     * exchange rate data updated on daily basis.
     *
     * @throws ClientException when the server responds with an unexpected response
     *
     * @return iterable<string, float> iterable<currency, rates>
     */
    public function getLatestRates(LatestRatesOption $options = new LatestRatesOption()): iterable;

    /**
     * Currency conversion endpoint, can be used to convert any amount from one currency to another.
     * In order to convert currencies, please use the API's convert endpoint, append the from and to
     * parameters and set them to your preferred base and target currency codes.
     *
     * @param string $fromCurrency [required] The three-letter currency code of the currency you would like to convert from
     * @param string $toCurrency [required] The three-letter currency code of the currency you would like to convert to
     * @param int $amount [required] The amount to be converted
     *
     * @throws ClientException when the server responds with an unexpected response
     */
    public function convertCurrency(string $fromCurrency, string $toCurrency, int $amount, ConvertCurrencyOption $options = new ConvertCurrencyOption()): float;

    /**
     * Historical rates are available for most currencies all the way back to the year of 1999.
     *
     * @param \DateTimeImmutable $date the date to query
     *
     * @throws ClientException when the server responds with an unexpected response
     *
     * @return iterable<string, float> iterable<currency, rates>
     */
    public function getHistoricalRates(\DateTimeImmutable $date, HistoricalRatesOption $options = new HistoricalRatesOption()): iterable;

    /**
     * Timeseries endpoint are for daily historical rates between two dates of your choice,
     * with a maximum time frame of 366 days.
     *
     * @param \DateTimeImmutable $startDate [required] The start date of your preferred timeframe
     * @param \DateTimeImmutable $endDate [required] The end date of your preferred timeframe
     *
     * @throws ClientException when the server responds with an unexpected response
     *
     * @return iterable<string, iterable<string, float>> iterable<date, iterable<currency, rate>>
     */
    public function getTimeSeriesRates(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, TimeSeriesDataOption $options = new TimeSeriesDataOption()): iterable;

    /**
     * Using the fluctuation endpoint you will be able to retrieve information about how currencies
     * fluctuate on a day-to-day basis. To use this feature, simply append a start_date and end_date
     * and choose which currencies (symbols) you would like to query the API for. Please note that
     * the maximum allowed timeframe is 366 days.
     *
     * @param \DateTimeImmutable $startDate [required] The start date of your preferred fluctuation timeframe
     * @param \DateTimeImmutable $endDate [required] The end date of your preferred fluctuation timeframe
     *
     * @throws ClientException when the server responds with an unexpected response
     *
     * @return iterable<string, FluctuationData> iterable<currency, FluctuationData>
     */
    public function getFluctuationData(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, FluctuationDataOption $options = new FluctuationDataOption()): iterable;

    /**
     * API comes with a constantly updated endpoint returning all available currencies. To access this list,
     * make a request to the API's symbols endpoint.
     *
     * @throws ClientException when the server responds with an unexpected response
     *
     * @return iterable<string, SymbolData> iterable<currency, SymbolData>
     */
    public function getSupportedCurrencies(SupportedSymbolsOption $options = new SupportedSymbolsOption()): iterable;

    /**
     * Our accurate EU VAT information API simplifies in and around the European Union.
     * Request a single by country code or entire set EU VAT rates.
     *
     * @throws ClientException when the server responds with an unexpected response
     *
     * @return iterable<string, VatRates> iterable<country_code, VatRates>
     */
    public function getEuVatRates(EuVatRatesOption $options = new EuVatRatesOption()): iterable;
}
