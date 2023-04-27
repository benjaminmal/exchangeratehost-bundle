<?php

namespace Tests\Benjaminmal\ExchangeRateHostBundle\App\Controller;

use Benjaminmal\ExchangeRateHostBundle\Client\ExchangeRateHostClientInterface;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\ConvertCurrencyOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\EuVatRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\FluctuationDataOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\HistoricalRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\LatestRatesOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\SupportedSymbolsOption;
use Benjaminmal\ExchangeRateHostBundle\Model\Option\TimeSeriesDataOption;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class MyController extends AbstractController
{
    public function __construct(
        private readonly ExchangeRateHostClientInterface $client,
    ) {
    }

    #[Route(path: '/latest', name: 'app_latest')]
    public function getLatestRates(Request $request): JsonResponse
    {
        $options = $request->query->all();
        $results = $this->client->getLatestRates(new LatestRatesOption(...$options));

        return $this->json($results);
    }

    #[Route(path: '/convert/{fromCurrency}&{toCurrency}&{amount}', name: 'app_convert')]
    public function convertCurrency(Request $request, string $fromCurrency, string $toCurrency, int $amount): JsonResponse
    {
        $options = $request->query->all();
        $results = $this->client->convertCurrency($fromCurrency, $toCurrency, $amount, new ConvertCurrencyOption(...$options));

        return $this->json(['result' => $results]);
    }

    #[Route(path: '/historical/{date}', name: 'app_historical')]
    public function getHistoricalRates(Request $request, \DateTimeImmutable $date): JsonResponse
    {
        $options = $request->query->all();
        $results = $this->client->getHistoricalRates($date, new HistoricalRatesOption(...$options));

        return $this->json($results);
    }

    #[Route(path: '/timeseries/{startDate}&{endDate}', name: 'app_timeseries')]
    public function getTimeSeriesRates(Request $request, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): JsonResponse
    {
        $options = $request->query->all();
        $results = $this->client->getTimeSeriesRates($startDate, $endDate, new TimeSeriesDataOption(...$options));

        return $this->json($results);
    }

    #[Route(path: '/fluctuation/{startDate}&{endDate}', name: 'app_fluctuation')]
    public function getFluctuationData(Request $request, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): JsonResponse
    {
        $options = $request->query->all();
        $results = $this->client->getFluctuationData($startDate, $endDate, new FluctuationDataOption(...$options));

        return $this->json($results);
    }

    #[Route(path: '/supported_currencies', name: 'app_supported')]
    public function getSupportedCurrencies(Request $request): JsonResponse
    {
        $options = $request->query->all();
        $results = $this->client->getSupportedCurrencies(new SupportedSymbolsOption(...$options));

        return $this->json($results);
    }

    #[Route(path: '/eu_vat', name: 'app_eu_vat')]
    public function getEuVatRates(Request $request): JsonResponse
    {
        $options = $request->query->all();
        $results = $this->client->getEuVatRates(new EuVatRatesOption(...$options));

        return $this->json($results);
    }
}
