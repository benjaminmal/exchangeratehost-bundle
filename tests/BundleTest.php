<?php

declare(strict_types=1);

namespace Tests\Benjaminmal\ExchangeRateHostBundle;

use Benjaminmal\ExchangeRateHostBundle\Client\CacheableExchangeRateHostClient;
use Benjaminmal\ExchangeRateHostBundle\Client\ExchangeRateHostClient;
use Benjaminmal\ExchangeRateHostBundle\Client\ExchangeRateHostClientInterface;
use Benjaminmal\ExchangeRateHostBundle\Client\TraceableExchangeRateHostClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
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

    /**
     * @test
     */
    public function bootWithWrongCachePool(): void
    {
        $this->expectException(ServiceNotFoundException::class);

        static::bootKernel(['environment' => 'test_with_invalid_cache']);
        self::getContainer()->get('benjaminmal.exchangerate_host_bundle.cache_pool');
    }

    /**
     * @test
     */
    public function correctCachePoolInContainer(): void
    {
        $container = self::getContainer();
        $cachePool = $container->get('exchangerate_host.cache', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        $cachePool2 = $container->get('benjaminmal.exchangerate_host_bundle.cache_pool', ContainerInterface::NULL_ON_INVALID_REFERENCE);

        $this->assertSame($cachePool, $cachePool2);
    }

    /**
     * @test
     */
    public function bootDev(): void
    {
        static::bootKernel(['environment' => 'dev']);
        $container = self::getContainer();

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * @test
     */
    public function bootProd(): void
    {
        static::bootKernel(['environment' => 'prod']);
        $container = self::getContainer();

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    public static function serviceProvider(): array
    {
        return [
            [ExchangeRateHostClientInterface::class, TraceableExchangeRateHostClient::class, true],
            ['benjaminmal.exchangerate_host_bundle.client', TraceableExchangeRateHostClient::class, true],
            ['benjaminmal.exchangerate_host_bundle.traceable_client', TraceableExchangeRateHostClient::class, true],
            ['benjaminmal.exchangerate_host_bundle.cacheable_client', CacheableExchangeRateHostClient::class, true],
            ['benjaminmal.exchangerate_host_bundle.base_client', ExchangeRateHostClient::class, true],
            ['benjaminmal.exchangerate_host_bundle.http_client', ClientInterface::class, true],
            ['benjaminmal.exchangerate_host_bundle.uri_factory', UriFactoryInterface::class, true],
            ['benjaminmal.exchangerate_host_bundle.request_factory', RequestFactoryInterface::class, true],
            ['benjaminmal.exchangerate_host_bundle.cache_pool', CacheInterface::class, true],

            // Should not exist
            [ExchangeRateHostClientInterface::class, CacheableExchangeRateHostClient::class, false],
            ['benjaminmal.exchangerate_host_bundle.client', CacheableExchangeRateHostClient::class, false],
            [ExchangeRateHostClientInterface::class, ExchangeRateHostClient::class, false],
            ['benjaminmal.exchangerate_host_bundle.client', ExchangeRateHostClient::class, false],
        ];
    }

    public static function noCacheServiceProvider(): array
    {
        return [
            [ExchangeRateHostClientInterface::class, TraceableExchangeRateHostClient::class, true],
            ['benjaminmal.exchangerate_host_bundle.client', TraceableExchangeRateHostClient::class, true],
            ['benjaminmal.exchangerate_host_bundle.traceable_client', TraceableExchangeRateHostClient::class, true],
            ['benjaminmal.exchangerate_host_bundle.base_client', ExchangeRateHostClient::class, true],
            ['benjaminmal.exchangerate_host_bundle.http_client', ClientInterface::class, true],
            ['benjaminmal.exchangerate_host_bundle.uri_factory', UriFactoryInterface::class, true],
            ['benjaminmal.exchangerate_host_bundle.request_factory', RequestFactoryInterface::class, true],

            // Should not exist
            [ExchangeRateHostClientInterface::class, CacheableExchangeRateHostClient::class, false],
            ['benjaminmal.exchangerate_host_bundle.client', CacheableExchangeRateHostClient::class, false],
            ['benjaminmal.exchangerate_host_bundle.cacheable_client', CacheableExchangeRateHostClient::class, false],
            ['benjaminmal.exchangerate_host_bundle.cache_pool', CacheInterface::class, false],
            [ExchangeRateHostClientInterface::class, ExchangeRateHostClient::class, false],
            ['benjaminmal.exchangerate_host_bundle.client', ExchangeRateHostClient::class, false],
        ];
    }
}
