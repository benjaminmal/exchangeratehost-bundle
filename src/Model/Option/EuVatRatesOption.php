<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateBundle\Model\Option;

class EuVatRatesOption implements OptionInterface
{
    public function __construct(
        /**
         * [optional] Enter a list country codes to limit output countries.
         * Example ['BE','FR','IT']
         *
         * @var null|string|iterable<string>
         */
        public string|iterable|null $symbols = null,

        /**
         * [optional] API comes with support for JSONP Callbacks. This feature enables you to specify a
         * function name, pass it into the API's callback GET parameter and cause the API to return your
         * requested API response wrapped inside that function. Example: functionName
         */
        public ?string $callback = null,
    ) {
    }
}
