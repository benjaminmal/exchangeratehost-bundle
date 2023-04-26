<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateBundle;

use Benjaminmal\ExchangeRateBundle\Client\CacheableExchangeRateClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class ExchangeRateBundle extends AbstractBundle implements CompilerPassInterface
{
    protected string $extensionAlias = 'exchangerate_host';

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        // Cache
        if (isset($config['cache']['pool']) && $config['cache']['pool']) {
            // Expiration
            if (isset($config['cache']['latest_rates_expiration'])) {
                $this->validateExpiration($config['cache']['latest_rates_expiration']);
                $builder->setParameter('benjaminmal.exchangerate_bundle.cache.latest_rates_expiration', $config['cache']['latest_rates_expiration']);
            }

            if (isset($config['cache']['convert_currency_expiration'])) {
                $this->validateExpiration($config['cache']['convert_currency_expiration']);
                $builder->setParameter('benjaminmal.exchangerate_bundle.cache.convert_currency_expiration', $config['cache']['convert_currency_expiration']);
            }

            if (isset($config['cache']['historical_rates_expiration'])) {
                $this->validateExpiration($config['cache']['historical_rates_expiration']);
                $builder->setParameter('benjaminmal.exchangerate_bundle.cache.historical_rates_expiration', $config['cache']['historical_rates_expiration']);
            }

            if (isset($config['cache']['timeseries_rates_expiration'])) {
                $this->validateExpiration($config['cache']['timeseries_rates_expiration']);
                $builder->setParameter('benjaminmal.exchangerate_bundle.cache.timeseries_rates_expiration', $config['cache']['timeseries_rates_expiration']);
            }

            if (isset($config['cache']['fluctuation_data_expiration'])) {
                $this->validateExpiration($config['cache']['fluctuation_data_expiration']);
                $builder->setParameter('benjaminmal.exchangerate_bundle.cache.fluctuation_data_expiration', $config['cache']['fluctuation_data_expiration']);
            }

            if (isset($config['cache']['supported_currencies_expiration'])) {
                $this->validateExpiration($config['cache']['supported_currencies_expiration']);
                $builder->setParameter('benjaminmal.exchangerate_bundle.cache.supported_currencies_expiration', $config['cache']['supported_currencies_expiration']);
            }

            if (isset($config['cache']['eu_vat_rates_expiration'])) {
                $this->validateExpiration($config['cache']['eu_vat_rates_expiration']);
                $builder->setParameter('benjaminmal.exchangerate_bundle.cache.eu_vat_rates_expiration', $config['cache']['eu_vat_rates_expiration']);
            }

            $builder->setAlias('benjaminmal.exchangerate_bundle.cache_pool', $config['cache']['pool']);
            $services = $container->services();
            $services
                ->set('benjaminmal.exchangerate_bundle.cacheable_client')
                ->class(CacheableExchangeRateClient::class)
                ->decorate('benjaminmal.exchangerate_bundle.client')
                ->args([
                    service('.inner'),
                    service('benjaminmal.exchangerate_bundle.cache_pool'),
                    param('benjaminmal.exchangerate_bundle.cache.latest_rates_expiration'),
                    param('benjaminmal.exchangerate_bundle.cache.convert_currency_expiration'),
                    param('benjaminmal.exchangerate_bundle.cache.historical_rates_expiration'),
                    param('benjaminmal.exchangerate_bundle.cache.timeseries_rates_expiration'),
                    param('benjaminmal.exchangerate_bundle.cache.fluctuation_data_expiration'),
                    param('benjaminmal.exchangerate_bundle.cache.supported_currencies_expiration'),
                    param('benjaminmal.exchangerate_bundle.cache.eu_vat_rates_expiration'),
                ])
            ;
        }
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this);
    }

    public function process(ContainerBuilder $container): void
    {
        // Alias PSRs if interfaces are already set in container
        if (! $container->has('benjaminmal.exchangerate_bundle.request_factory')
            && $container->has(RequestFactoryInterface::class)
        ) {
            $container->setAlias('benjaminmal.exchangerate_bundle.request_factory', RequestFactoryInterface::class);
        }

        if (! $container->has('benjaminmal.exchangerate_bundle.uri_factory')
            && $container->has(UriFactoryInterface::class)
        ) {
            $container->setAlias('benjaminmal.exchangerate_bundle.uri_factory', UriFactoryInterface::class);
        }

        if (! $container->has('benjaminmal.exchangerate_bundle.http_client')
            && $container->has('psr18.http_client')
        ) {
            $container->setAlias('benjaminmal.exchangerate_bundle.http_client', 'psr18.http_client');
        } elseif ($container->has(ClientInterface::class)) {
            $container->setAlias('benjaminmal.exchangerate_bundle.http_client', ClientInterface::class);
        }
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $root = $definition->rootNode();
        $root
            ->children()
                ->arrayNode('cache')
                    ->children()
                        ->scalarNode('pool')
                            ->info('The cache pool to use.')
                            ->defaultValue('cache.app')
                        ->end()
                        ->scalarNode('latest_rates_expiration')
                            ->info('The latest rates cache expiration. Integer for seconds, string for a valid date')
                            ->defaultValue('tomorrow 6am')
                        ->end()
                        ->scalarNode('convert_currency_expiration')
                            ->info('The convert currency cache expiration. Integer for seconds, string for a valid date')
                            ->defaultValue('tomorrow 6am')
                        ->end()
                        ->scalarNode('historical_rates_expiration')
                            ->info('The historical rates cache expiration. Integer for seconds, string for a valid date')
                            ->defaultValue('tomorrow 6am')
                        ->end()
                        ->scalarNode('timeseries_rates_expiration')
                            ->info('The timeseries rates cache expiration. Integer for seconds, string for a valid date')
                            ->defaultValue('tomorrow 6am')
                        ->end()
                        ->scalarNode('fluctuation_data_expiration')
                            ->info('The fluctuation data cache expiration. Integer for seconds, string for a valid date')
                            ->defaultValue('tomorrow 6am')
                        ->end()
                        ->scalarNode('supported_currencies_expiration')
                            ->info('The supported currencies cache expiration. Integer for seconds, string for a valid date')
                            ->defaultValue('tomorrow 6am')
                        ->end()
                        ->scalarNode('eu_vat_rates_expiration')
                            ->info('The eu vat rates cache expiration. Integer for seconds, string for a valid date')
                            ->defaultValue('tomorrow 6am')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function validateExpiration(mixed $expiration): void
    {
        if (! is_int($expiration) && ! is_string($expiration)) {
            throw new \RuntimeException('Expiration must be a date (string) or a second (integer).');
        }

        if (is_string($expiration) && ! strtotime($expiration)) {
            throw new \RuntimeException('The expiration date (string) is not valid.');
        }
    }
}
