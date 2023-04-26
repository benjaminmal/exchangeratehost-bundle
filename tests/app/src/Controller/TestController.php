<?php

declare(strict_types=1);

namespace Tests\Benjaminmal\ExchangeRateBundle\App\Controller;

use Benjaminmal\ExchangeRateBundle\Client\ExchangeRateClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    public function __construct(
        private readonly ExchangeRateClientInterface $client,
    ) {
    }

    #[Route(path: '/', name: 'app_index')]
    public function index(): Response
    {
        return $this->json([]);
    }
}
