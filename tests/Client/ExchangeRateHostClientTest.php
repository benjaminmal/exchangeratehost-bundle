<?php

declare(strict_types=1);

namespace Tests\Benjaminmal\ExchangeRateHostBundle\Client;

use Benjaminmal\ExchangeRateHostBundle\Model\Option\OptionInterface;
use PHPUnit\Framework\TestCase;

class ExchangeRateHostClientTest extends TestCase
{
    use TestClientHelperTrait;

    /**
     * @test
     *
     * @dataProvider dataProvider
     */
    public function methods(string $file, string $method, array $args, ?OptionInterface $option, string $expectedUrl, mixed $expectedResult): void
    {
        $response = $this->createResponse($file);
        $client = $this->createClient([$response]);
        $result = $client->$method(...$this->convertArguments($args));

        $this->assertSame('GET', $response->getRequestMethod());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedUrl, $response->getRequestUrl());

        if ($result instanceof \ArrayObject) {
            $result = $result->getArrayCopy();
        }

        // Get php expected result
        $expectedResult ??= $this->getExpectedResponse($file);
        $this->assertEquals($expectedResult, $result);
    }
}
