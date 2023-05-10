<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateHostBundle;

use Benjaminmal\ExchangeRateHostBundle\Client\CacheableExchangeRateHostClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class ExchangeRateHostBundle extends AbstractBundle implements CompilerPassInterface
{
    protected string $extensionAlias = 'exchangerate_host';

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        // Cache
        if (! $config['cache']['enabled']) {
            return;
        }

        if (isset($config['cache']['pools'])) {
            $pools = $config['cache']['pools'];

            if (isset($pools['latest_rates']) && $pools['latest_rates']) {
                $builder->setAlias('benjaminmal.exchangerate_host_bundle.cache_pool.latest_rates', $pools['latest_rates']);
            }

            if (isset($pools['convert_currency']) && $pools['convert_currency']) {
                $builder->setAlias('benjaminmal.exchangerate_host_bundle.cache_pool.convert_currency', $pools['convert_currency']);
            }

            if (isset($pools['historical_rates']) && $pools['historical_rates']) {
                $builder->setAlias('benjaminmal.exchangerate_host_bundle.cache_pool.historical_rates', $pools['historical_rates']);
            }

            if (isset($pools['timeseries_rates']) && $pools['timeseries_rates']) {
                $builder->setAlias('benjaminmal.exchangerate_host_bundle.cache_pool.timeseries_rates', $pools['timeseries_rates']);
            }

            if (isset($pools['fluctuation_data']) && $pools['fluctuation_data']) {
                $builder->setAlias('benjaminmal.exchangerate_host_bundle.cache_pool.fluctuation_data', $pools['fluctuation_data']);
            }

            if (isset($pools['supported_currencies']) && $pools['supported_currencies']) {
                $builder->setAlias('benjaminmal.exchangerate_host_bundle.cache_pool.supported_currencies', $pools['supported_currencies']);
            }

            if (isset($pools['eu_vat_rates']) && $pools['eu_vat_rates']) {
                $builder->setAlias('benjaminmal.exchangerate_host_bundle.cache_pool.eu_vat_rates', $pools['eu_vat_rates']);
            }

            $services = $container->services();
            $services
                ->set('benjaminmal.exchangerate_host_bundle.cacheable_client')
                ->class(CacheableExchangeRateHostClient::class)
                ->decorate('benjaminmal.exchangerate_host_bundle.client', priority: 128)
                ->args([
                    service('.inner'),
                    service('benjaminmal.exchangerate_host_bundle.cache_pool.latest_rates'),
                    service('benjaminmal.exchangerate_host_bundle.cache_pool.convert_currency'),
                    service('benjaminmal.exchangerate_host_bundle.cache_pool.historical_rates'),
                    service('benjaminmal.exchangerate_host_bundle.cache_pool.timeseries_rates'),
                    service('benjaminmal.exchangerate_host_bundle.cache_pool.fluctuation_data'),
                    service('benjaminmal.exchangerate_host_bundle.cache_pool.supported_currencies'),
                    service('benjaminmal.exchangerate_host_bundle.cache_pool.eu_vat_rates'),
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
        if (! $container->has('benjaminmal.exchangerate_host_bundle.request_factory')
            && $container->has(RequestFactoryInterface::class)
        ) {
            $container->setAlias('benjaminmal.exchangerate_host_bundle.request_factory', RequestFactoryInterface::class);
        }

        if (! $container->has('benjaminmal.exchangerate_host_bundle.uri_factory')
            && $container->has(UriFactoryInterface::class)
        ) {
            $container->setAlias('benjaminmal.exchangerate_host_bundle.uri_factory', UriFactoryInterface::class);
        }

        if (! $container->has('benjaminmal.exchangerate_host_bundle.http_client')
            && $container->has('psr18.http_client')
        ) {
            $container->setAlias('benjaminmal.exchangerate_host_bundle.http_client', 'psr18.http_client');
        } elseif ($container->has(ClientInterface::class)) {
            $container->setAlias('benjaminmal.exchangerate_host_bundle.http_client', ClientInterface::class);
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('framework', [
            'cache' => [
                'pools' => [
                    'exchangeratehost.cache' => [
                        'adapter' => 'cache.app',
                        'default_lifetime' => 60 * 60 * 24, # 1 day
                    ],
                ],
            ],
        ]);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('cache')
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Enable or disable the cache. Default to true.')
                            ->defaultTrue()
                        ->end()
                        ->arrayNode('pools')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('latest_rates')
                                    ->info('The latest rates cache pool. False to deactivate it.')
                                    ->defaultValue('exchangeratehost.cache')
                                ->end()
                                ->scalarNode('convert_currency')
                                    ->info('The convert currency pool. False to deactivate it.')
                                    ->defaultValue('exchangeratehost.cache')
                                ->end()
                                ->scalarNode('historical_rates')
                                    ->info('The historical rates pool. False to deactivate it.')
                                    ->defaultValue('exchangeratehost.cache')
                                ->end()
                                ->scalarNode('timeseries_rates')
                                    ->info('The timeseries rates pool. False to deactivate it.')
                                    ->defaultValue('exchangeratehost.cache')
                                ->end()
                                ->scalarNode('fluctuation_data')
                                    ->info('The fluctuation data pool. False to deactivate it.')
                                    ->defaultValue('exchangeratehost.cache')
                                ->end()
                                ->scalarNode('supported_currencies')
                                    ->info('The supported currencies pool. False to deactivate it.')
                                    ->defaultValue('exchangeratehost.cache')
                                ->end()
                                ->scalarNode('eu_vat_rates')
                                    ->info('The eu vat rates pool. False to deactivate it.')
                                    ->defaultValue('exchangeratehost.cache')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
