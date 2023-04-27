<?php

declare(strict_types=1);

use Benjaminmal\ExchangeRateHostBundle\Client\ExchangeRateHostClient;
use Benjaminmal\ExchangeRateHostBundle\Client\ExchangeRateHostClientInterface;
use Benjaminmal\ExchangeRateHostBundle\Client\TraceableExchangeRateHostClient;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $env = $containerConfigurator->env();

    $services->alias(ExchangeRateHostClientInterface::class, 'benjaminmal.exchangerate_host_bundle.client');
    $services->alias('benjaminmal.exchangerate_host_bundle.client', 'benjaminmal.exchangerate_host_bundle.base_client');
    $services
        ->set('benjaminmal.exchangerate_host_bundle.base_client')
        ->class(ExchangeRateHostClient::class)
        ->args([
            service('benjaminmal.exchangerate_host_bundle.http_client'),
            service('benjaminmal.exchangerate_host_bundle.uri_factory'),
            service('benjaminmal.exchangerate_host_bundle.request_factory'),
        ])
    ;

    if ($env !== 'prod') {
        $services
            ->set('benjaminmal.exchangerate_host_bundle.traceable_client')
            ->class(TraceableExchangeRateHostClient::class)
            ->decorate('benjaminmal.exchangerate_host_bundle.client')
            ->args([
                service('.inner'),
                service('debug.stopwatch'),
            ])
        ;
    }
};
