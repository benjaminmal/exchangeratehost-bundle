[![Continuous integration](https://github.com/benjaminmal/exchangeratehost-bundle/actions/workflows/ci.yaml/badge.svg)](https://github.com/benjaminmal/exchangeratehost-bundle/actions/workflows/ci.yaml)
# exchangerate.host bundle
This bundle allows you to query the great (and free!) [exchangerate.host](https://exchangerate.host) API in a [Symfony](https://symfony.com/) app with ease. It supports [PSR-7](https://www.php-fig.org/psr/psr-7/), [PSR-17](https://www.php-fig.org/psr/psr-17/), [PSR-18](https://www.php-fig.org/psr/psr-18/) and uses the [Symfony Cache](https://symfony.com/doc/current/cache.html) which supports [PSR-6](https://www.php-fig.org/psr/psr-6/) and [PSR-16](https://www.php-fig.org/psr/psr-16/) so you have full control of your dependencies!

⚠️ This bundle is unofficial. I'm not related to [exchangerate.host](https://exchangerate.host).

## Summary
- [Requirements](#requirements)
- [Installation](#installation)
- [Getting started](#getting-started)
    - [Config](#config)
    - [Use the API client](#use-the-api-client)
- [Cache](#cache) 
    - [Customizing the cache](#customizing-the-cache)
    - [Clearing the cache](#clearing-the-cache)
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
The following file is optional but here are the default config values:
```yaml
# exchangerate_host.yaml
exchangerate_host:
    cache:
        # Enabled / disable caching. Optional. Disabling it is not recommended 
        # (HTTP request can be long, rate limit could be hit).
        enabled: true
        
        # Set the cache pool. Optional. Set it to false if you don't want to use 
        # this specific cache pool. Default to "exchangeratehost.cache", which extends the default "app.cache".
        pools:
            latest_rates: 'exchangeratehost.cache'
            convert_currency: 'exchangeratehost.cache'
            historical_rates: 'exchangeratehost.cache'
            timeseries_rates: 'exchangeratehost.cache'
            fluctuation_data: 'exchangeratehost.cache'
            supported_currencies: 'exchangeratehost.cache'
            eu_vat_rates: 'exchangeratehost.cache'
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

## Cache
### Customizing the cache
You want to change the default cache behavior? Let's do that:
```yaml
# exchangerate_host.yaml
exchangerate_host:
    cache:
        pools:
            latest_rates: 'my_new_pool.cache'
```
```yaml
# cache.yaml
framework:
    cache:
        pools:
            my_new_pool.cache:
                adapter: exchangeratehost.cache # extending the default bundle cache
                defaultLifetime: 3600 # 1 hour
                # ... your custom config
```

### Clearing the cache
If you are using the cache (which is highly recommended) you may want to clear the cache at each new entry of the exchangerate.host API. So you need to set a cron job on your server just after 00:05am GMT everyday (found in the [FAQ](https://exchangerate.host/#/faq)).

The cron command:
```
6 0 * * *
```
⚠️ Cron generally works on local time! Adapt it to the timezone of your servers.

The command:
```console
php path/to/my_project/bin/console cache:pool:clear exchangeratehost.cache
```
If you changed the default cache pool, use them instead of `exchangeratehost.cache`!

## What's more?
- [exchangerate.host](https://exchangerate.host/#/#docs) for full documentations about the exchangerate.host API.
- [nyholm/psr7](https://github.com/Nyholm/psr7) for a PSR-7 and PSR-17 implementations.
- [symfony/http-client](https://symfony.com/doc/current/http_client.html) for a PSR-18 implementation and a great integration with Symfony.
