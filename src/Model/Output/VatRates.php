<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateBundle\Model\Output;

class VatRates
{
    public function __construct(
        public ?string $countryName = null,
        public ?int $standardRate = null,

        /**
         * @var float[]
         */
        public ?array $reducedRates = null,

        /**
         * @var float[]
         */
        public ?array $superReducedRates = null,

        /**
         * @var float[]
         */
        public ?array $parkingRates = null,
    ) {
    }
}
