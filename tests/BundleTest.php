<?php

declare(strict_types=1);

namespace Tests\Benjaminmal\ExchangeRateBundle;

use Benjaminmal\ExchangeRateBundle\Client\CacheableExchangeRateClient;
use Benjaminmal\ExchangeRateBundle\Client\ExchangeRateClient;
use Benjaminmal\ExchangeRateBundle\Client\ExchangeRateClientInterface;
use Benjaminmal\ExchangeRateBundle\Client\TraceableExchangeRateClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class BundleTest extends KernelTestCase
{
    /**
     * @test
     *
     * @dataProvider serviceProvider
     */
    public function isInContainer(string $id, string $class, bool $shouldExist): void
    {
        $container = self::getContainer();
        $service = $container->get($id, ContainerInterface::NULL_ON_INVALID_REFERENCE);

        if ($shouldExist) {
            $this->assertInstanceOf($class, $service);
        } else {
            $this->assertNotInstanceOf($class, $service);
        }
    }

    /**
     * @test
     *
     * @dataProvider serviceProvider
     */
    public function isInContainerWithoutSfHttpClient(string $id, string $class, bool $shouldExist): void
    {
        static::bootKernel(['environment' => 'test_without_http_client']);

        $container = self::getContainer();
        $service = $container->get($id, ContainerInterface::NULL_ON_INVALID_REFERENCE);

        if ($shouldExist) {
            $this->assertInstanceOf($class, $service);
        } else {
            $this->assertNotInstanceOf($class, $service);
        }
    }

    /**
     * @test
     *
     * @dataProvider noCacheServiceProvider
     */
    public function isInContainerWithNoCache(string $id, string $class, bool $shouldExist): void
    {
        static::bootKernel(['environment' => 'test_with_no_cache']);

        $container = self::getContainer();
        $service = $container->get($id, ContainerInterface::NULL_ON_INVALID_REFERENCE);

        if ($shouldExist) {
            $this->assertInstanceOf($class, $service);
        } else {
            $this->assertNotInstanceOf($class, $service);
        }
    }

    public static function serviceProvider(): array
    {
        return [
            [ExchangeRateClientInterface::class, CacheableExchangeRateClient::class, true],
            ['benjaminmal.exchangerate_bundle.client', CacheableExchangeRateClient::class, true],
            ['benjaminmal.exchangerate_bundle.cacheable_client', CacheableExchangeRateClient::class, true],
            ['benjaminmal.exchangerate_bundle.traceable_client', TraceableExchangeRateClient::class, true],
            ['benjaminmal.exchangerate_bundle.base_client', ExchangeRateClient::class, true],
            ['benjaminmal.exchangerate_bundle.http_client', ClientInterface::class, true],
            ['benjaminmal.exchangerate_bundle.uri_factory', UriFactoryInterface::class, true],
            ['benjaminmal.exchangerate_bundle.request_factory', RequestFactoryInterface::class, true],
            ['benjaminmal.exchangerate_bundle.cache_pool', CacheInterface::class, true],

            // Should not exist
            [ExchangeRateClientInterface::class, TraceableExchangeRateClient::class, false],
            ['benjaminmal.exchangerate_bundle.client', TraceableExchangeRateClient::class, false],
            [ExchangeRateClientInterface::class, ExchangeRateClient::class, false],
            ['benjaminmal.exchangerate_bundle.client', ExchangeRateClient::class, false],
        ];
    }

    public static function noCacheServiceProvider(): array
    {
        return [
            [ExchangeRateClientInterface::class, TraceableExchangeRateClient::class, true],
            ['benjaminmal.exchangerate_bundle.client', TraceableExchangeRateClient::class, true],
            ['benjaminmal.exchangerate_bundle.traceable_client', TraceableExchangeRateClient::class, true],
            ['benjaminmal.exchangerate_bundle.base_client', ExchangeRateClient::class, true],
            ['benjaminmal.exchangerate_bundle.http_client', ClientInterface::class, true],
            ['benjaminmal.exchangerate_bundle.uri_factory', UriFactoryInterface::class, true],
            ['benjaminmal.exchangerate_bundle.request_factory', RequestFactoryInterface::class, true],

            // Should not exist
            [ExchangeRateClientInterface::class, CacheableExchangeRateClient::class, false],
            ['benjaminmal.exchangerate_bundle.client', CacheableExchangeRateClient::class, false],
            ['benjaminmal.exchangerate_bundle.cacheable_client', CacheableExchangeRateClient::class, false],
            ['benjaminmal.exchangerate_bundle.cache_pool', CacheInterface::class, false],
            [ExchangeRateClientInterface::class, ExchangeRateClient::class, false],
            ['benjaminmal.exchangerate_bundle.client', ExchangeRateClient::class, false],
        ];
    }
}
