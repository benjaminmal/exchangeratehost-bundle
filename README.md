[![Continuous integration](https://github.com/benjaminmal/exchangeratehost-bundle/actions/workflows/ci.yaml/badge.svg)](https://github.com/benjaminmal/exchangeratehost-bundle/actions/workflows/ci.yaml)
# exchangerate.host bundle
This bundle allows you to query the great (and free!) [exchangerate.host](https://exchangerate.host) API in a [Symfony](https://symfony.com/) app with ease. It supports [PSR-7](https://www.php-fig.org/psr/psr-7/), [PSR-17](https://www.php-fig.org/psr/psr-17/), [PSR-18](https://www.php-fig.org/psr/psr-18/) and uses the [Symfony Cache](https://symfony.com/doc/current/cache.html) which supports [PSR-6](https://www.php-fig.org/psr/psr-6/) and [PSR-16](https://www.php-fig.org/psr/psr-16/) so you have full control of your dependencies!

⚠️ This bundle is unofficial. I'm not related to [exchangerate.host](https://exchangerate.host).

## Summary
- [Requirements](#requirements)
- [Installation](#installation)
- [Getting started](#getting-started)
    - [Use the API client](#use-the-api-client)
- [What's more](#whats-more)

## Requirements
- PHP ^8.1
- Symfony ^6.2

## Installation
### Composer
```console  
$ composer require benjaminmal/exchangeratehost-bundle
```

### PSRs
In order to use this bundle, you need to set [PSR-17](https://www.php-fig.org/psr/psr-17/) message factories and a [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client.

If you already have PSR-17 factories and PSR-18 HTTP client in your services you're done! Otherwise, you can use these great libraries:
```console
$ composer require nyholm/psr7 symfony/http-client
```

If you're using [Symfony Flex](https://symfony.com/doc/current/quick_tour/flex_recipes.html) and the recommended librairies, you're all set! 

Otherwise, enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Benjaminmal\ExchangeRateHostBundle\ExchangeRateHostBundle::class => ['all' => true],
];
```

Then add your implementation of [PSR-17](https://www.php-fig.org/psr/psr-17/) and [PSR-18](https://www.php-fig.org/psr/psr-18/) services if they don't exist yet:
```yaml
# services.yaml
services:
    Psr\Http\Message\RequestFactoryInterface: '@your_custom_psr17_request_factory'
    Psr\Http\Message\UriFactoryInterface: '@your_custom_psr17_uri_factory'
    Psr\Http\Client\ClientInterface: '@your_custom_psr18_http_client'
```

## Getting started
### Config
Here are the default values:
```yaml
# exchangerate_host.yaml
exchangerate_host:
    cache:
        # Set the cache pool. Optional. Set it to false if you don't want to use cache (not recommended).
        pool: 'cache.app'
        
        # Data cache expiration. Optional. Could be an integer (seconds) or a string (date used 
        # in DateTime::__construct(), e.g '+3days', UTC timezone)
        latest_rates_expiration: 'tomorrow 6am'
        convert_currency_expiration: 'tomorrow 6am'
        historical_rates_expiration: 'tomorrow 6am'
        timeseries_rates_expiration: 'tomorrow 6am'
        fluctuation_data_expiration: 'tomorrow 6am'
        supported_currencies_expiration: 'tomorrow 6am'
        eu_vat_rates_expiration: 'tomorrow 6am'
```
### Use the API client
The API client is available through autowiring via `ExchangeRateHostClientInterface` or via `benjaminmal.exchangerate_host_bundle.client` service id:

```php
namespace App\Service;

use Benjaminmal\ExchangeRateHostBundle\Client\ExchangeRateHostClientInterface;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\ConvertCurrencyOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\EuVatRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\FluctuationDataOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\HistoricalRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\LatestRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\SupportedSymbolsOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\TimeSeriesDataOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Output\FluctuationData;
use Benjaminmal\ExchangeRateHostBundle\Model\Output\SymbolData;
use Benjaminmal\ExchangeRateHostBundle\Model\Output\VatRates;

class MyService
{
    public function __construct(private readonly ExchangeRateHostClientInterface $client)
    {
    }

    public function getLatestRates()
    {
        // Get the latest rates
        $rates = $this->client->getLatestRates(
            options: new LatestRatesOption( // Optional
                base: 'EUR',
                symbols: ['USD', 'CZK'],
                amount: 1200,
                places: 2,
                source: 'ecb',
                callback: 'functionName',
            ),
        );

        /**
         * @var string $currency
         * @var float $rate
         */
        foreach ($rates as $currency => $rate) {
            // ...
        }

        // ...
    }
    
    public function convertCurrency(): int
    {
        // Convert price
        /** @var int $newAmount */
        $newAmount = $this->client->convertCurrency(
            fromCurrency: 'EUR', // Required
            toCurrency: 'USD', // Required
            amount: 1300, // Required
            options: new ConvertCurrencyOption(), // Optional
        );
        
        // ...
    }
    
    public function getHistoricalRates()
    {
        // Get rates from a specific day
        $rates = $this->client->getHistoricalRates(
            date: new \DateTimeImmutable('-10days'), // Required, will be converted in the url with the format 'Y-m-d'
            options: new HistoricalRatesOption(), // Optional
        );
        
        /**
         * @var string $currency
         * @var float $rate
         */
        foreach ($rates as $currency => $rate) {
            // ...
        }
        
        // ...
    }
    
    public function getTimeSeriesRates()
    {
        // Get the rates between 2 dates
        $rates = $this->client->getTimeSeriesRates(
            startDate: new \DateTimeImmutable('-89days'), // Required
            endDate: new \DateTimeImmutable('-10days'), // Required
            options: new TimeSeriesDataOption(), // Optional
        );

        /**
         * @var $date string formatted as 'Y-m-d'
         * @var $datum iterable<string, float> 
         */
        foreach ($rates as $date => $datum) {
            foreach ($datum as $currency => $rate) {
                // ...
            }
        }

        // ...
    }
    
    public function getFluctuationData()
    {
        // Get the fluctuation data between 2 dates
        $data = $this->client->getFluctuationData(
            startDate: new \DateTimeImmutable('-89days'), // Required
            endDate: new \DateTimeImmutable('-10days'), // Required
            options: new FluctuationDataOption(), // Optional
        );
        
        /**
         * @var FluctuationData $fluctuationData 
         */
        foreach ($data as $currency => $fluctuationData) {
            echo $fluctuationData->startRate;
            echo $fluctuationData->endRate;
            echo $fluctuationData->change;
            echo $fluctuationData->changePct;
        }
        
        // ...
    }
    
    public function getCurrencies()
    {
        // Get the supported currencies
        $supportedCurrencies = $this->client->getSupportedCurrencies(
            options: new SupportedSymbolsOption(), // Optional
        );
        
        /**
         * @var SymbolData $supportedCurrency 
         */
        foreach ($supportedCurrencies as $currency => $supportedCurrency) {
            echo $supportedCurrency->code;
            echo $supportedCurrency->description;
        }
        
        // ...
    }
    
    public function getEurVatRates()
    {
        // Get EU VAT rates
        $rates = $this->client->getEuVatRates(
            options: new EuVatRatesOption(), // Optional
        );
        
        /**
         * @var VatRates $vatRate 
         */
        foreach ($rates as $countryCode => $vatRate) {
            echo $vatRate->countryName;
            echo $vatRate->standardRate;
            echo implode(', ', $vatRate->parkingRates);
            echo implode(', ', $vatRate->reducedRates);
            echo implode(', ', $vatRate->superReducedRates);
        }
    }
}
```

## What's more?
- [exchangerate.host](https://exchangerate.host/#/#docs) for full documentations about the exchangerate.host API.
- [nyholm/psr7](https://github.com/Nyholm/psr7) for a PSR-7 and PSR-17 implementations.
- [symfony/http-client](https://symfony.com/doc/current/http_client.html) for a PSR-18 implementation and a great integration with Symfony.
