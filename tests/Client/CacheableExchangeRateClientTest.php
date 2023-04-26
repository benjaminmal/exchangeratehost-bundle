<?php

declare(strict_types=1);

namespace Tests\Benjaminmal\ExchangeRateBundle\Client;

use Benjaminmal\ExchangeRateBundle\Client\CacheableExchangeRateClient;
use Benjaminmal\ExchangeRateBundle\Exception\ClientException;
use Benjaminmal\ExchangeRateBundle\Model\Option\OptionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\CacheInterface;

class CacheableExchangeRateClientTest extends TestCase
{
    use TestClientHelperTrait;

    /**
     * @test
     *
     * @dataProvider dataProvider
     */
    public function caching(string $file, string $method, array $args, ?OptionInterface $option, string $expectedUrl, mixed $expectedResult, string $expectedCacheName): void
    {
        $cachePool = new ArrayAdapter(storeSerialized: false);

        $response = $this->createResponse($file);
        $client = $this->createCacheableClient([$response], $cachePool);
        $args = $this->convertArguments($args);
        $result = $client->$method(...$args);

        $this->assertSame('GET', $response->getRequestMethod());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedUrl, $response->getRequestUrl());

        // Get php expected result
        $expectedResult ??= new \ArrayObject($this->getExpectedResponse($file));
        $this->assertEquals($expectedResult, $result);

        // Cache
        $this->assertTrue($cachePool->hasItem($expectedCacheName));
        $cacheResult = $cachePool->getItem($expectedCacheName);
        $this->assertTrue($cacheResult->isHit());
        $this->assertEquals($expectedResult, $cacheResult->get());
    }

    /**
     * @test
     *
     * @dataProvider dataProvider
     */
    public function noCachingShouldError(string $file, string $method, array $args, ?OptionInterface $option, string $expectedUrl, mixed $expectedResult, string $expectedCacheName, array $alternateArgs): void
    {
        $cachePool = new ArrayAdapter(storeSerialized: false);

        $response = $this->createResponse($file);
        $client = $this->createCacheableClient([$response], $cachePool);
        $args = $this->convertArguments($args);
        $result = $client->$method(...$args);

        $this->assertSame('GET', $response->getRequestMethod());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedUrl, $response->getRequestUrl());

        // Get php expected result
        $expectedResult ??= new \ArrayObject($this->getExpectedResponse($file));
        $this->assertEquals($expectedResult, $result);

        // Try a 2nd time with caching with error response (should not get error since its cached)
        $client = $this->createCacheableClient([new MockResponse('', ['http_code' => 404])], $cachePool);
        $result = $client->$method(...$args);
        $this->assertEquals($expectedResult, $result);

        // Try a 3rd time with other params so should not hit cache

        // Cannot test with same request
        if ([] === $args) {
            return;
        }

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Not Found');

        // Should error
        $client = $this->createCacheableClient([new MockResponse('', ['http_code' => 404])], $cachePool);
        $client->$method(...$this->convertArguments($alternateArgs));
    }

    private function createCacheableClient(iterable $responses, CacheInterface $cache): CacheableExchangeRateClient
    {
        return new CacheableExchangeRateClient(
            $this->createClient($responses),
            $cache,
            'tomorrow 9am',
            86400,
            'tomorrow 9am',
            'tomorrow 9am',
            3600,
            'tomorrow 9am',
            'tomorrow 9am',
        );
    }
}
