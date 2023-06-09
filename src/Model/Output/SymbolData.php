<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateHostBundle\Model\Output;

class SymbolData
{
    public function __construct(
        public ?string $description = null,
        public ?string $code = null,
    ) {
    }
}
