<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateHostBundle\Model\Output;

class FluctuationData
{
    public function __construct(
        public ?float $startRate = null,
        public ?float $endRate = null,
        public ?float $change = null,
        public ?float $changePct = null,
    ) {
    }
}
