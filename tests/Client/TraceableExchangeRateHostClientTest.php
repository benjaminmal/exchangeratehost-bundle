<?php

declare(strict_types=1);

namespace Tests\Benjaminmal\ExchangeRateHostBundle\Client;

use Benjaminmal\ExchangeRateHostBundle\Client\TraceableExchangeRateHostClient;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\OptionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;

class TraceableExchangeRateHostClientTest extends TestCase
{
    use TestClientHelperTrait;

    /**
     * @test
     *
     * @dataProvider dataProvider
     */
    public function methods(string $file, string $method, array $args, ?OptionInterface $option, string $expectedUrl, mixed $expectedResult): void
    {
        $stopWatch = $this->createMock(Stopwatch::class);
        $stopWatch
            ->expects($this->once())
            ->method('start')
            ->with($method)
        ;

        $stopWatch
            ->expects($this->once())
            ->method('stop')
            ->with($method)
        ;

        $response = $this->createResponse($file);

        $client = $this->createTraceableClient($stopWatch, [$response]);
        $result = $client->$method(...[...$this->convertArguments($args), $option]);

        $this->assertSame('GET', $response->getRequestMethod());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedUrl, $response->getRequestUrl());

        // Get php expected result
        $expectedResult ??= new \ArrayObject($this->getExpectedResponse($file));
        $this->assertEquals($expectedResult, $result);
    }

    private function createTraceableClient(Stopwatch $stopwatch, array $responses): TraceableExchangeRateHostClient
    {
        return new TraceableExchangeRateHostClient($this->createClient($responses), $stopwatch);
    }
}
