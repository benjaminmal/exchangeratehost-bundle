<?php

declare(strict_types=1);

use Benjaminmal\ExchangeRateBundle\Client\ExchangeRateClient;
use Benjaminmal\ExchangeRateBundle\Client\ExchangeRateClientInterface;
use Benjaminmal\ExchangeRateBundle\Client\TraceableExchangeRateClient;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $env = $containerConfigurator->env();

    $services->alias(ExchangeRateClientInterface::class, 'benjaminmal.exchangerate_bundle.client');
    $services->alias('benjaminmal.exchangerate_bundle.client', 'benjaminmal.exchangerate_bundle.base_client');
    $services
        ->set('benjaminmal.exchangerate_bundle.base_client')
        ->class(ExchangeRateClient::class)
        ->args([
            service('benjaminmal.exchangerate_bundle.http_client'),
            service('benjaminmal.exchangerate_bundle.uri_factory'),
            service('benjaminmal.exchangerate_bundle.request_factory'),
        ])
    ;

    if ($env !== 'prod') {
        $services
            ->set('benjaminmal.exchangerate_bundle.traceable_client')
            ->class(TraceableExchangeRateClient::class)
            ->decorate('benjaminmal.exchangerate_bundle.client')
            ->args([
                service('.inner'),
                service('debug.stopwatch'),
            ])
        ;
    }
};
