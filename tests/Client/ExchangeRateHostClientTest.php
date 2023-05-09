<?php

declare(strict_types=1);

namespace Tests\Benjaminmal\ExchangeRateHostBundle\Client;

use Benjaminmal\ExchangeRateHostBundle\Exception\UnexpectedValueException;
use Benjaminmal\ExchangeRateHostBundle\Exception\UnsuccessfulResponseException;
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
        $result = $client->$method(...[...$this->convertArguments($args), $option]);

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

    /**
     * @test
     * @dataProvider basicResponseProvider
     */
    public function unexpectedResults(string $file, string $method, array $args): void
    {
        $this->expectException(UnexpectedValueException::class);

        $response = $this->createResponse('unexpected_result_response/' . $file);
        $client = $this->createClient([$response]);
        $client->$method(...$this->convertArguments($args));
    }

    /**
     * @test
     * @dataProvider basicResponseProvider
     */
    public function unsuccessfulResults(string $file, string $method, array $args): void
    {
        $this->expectException(UnsuccessfulResponseException::class);

        $response = $this->createResponse('unsuccessful_response/' . $file);
        $client = $this->createClient([$response]);
        $client->$method(...$this->convertArguments($args));
    }

    public static function basicResponseProvider(): array
    {
        return [
            ['convert', 'convertCurrency', ['USD', 'EUR', 1200]],
            ['eu_vat', 'getEuVatRates', []],
            ['fluctuation', 'getFluctuationData', ['2020-01-01', '2020-01-04']],
            ['historical', 'getHistoricalRates', ['2020-04-04']],
            ['latest', 'getLatestRates', []],
            ['symbols', 'getSupportedCurrencies', []],
            ['timeseries', 'getTimeSeriesRates', ['2020-01-01', '2020-01-04']],
        ];
    }
}
