<?php

declare(strict_types=1);

namespace Benjaminmal\ExchangeRateHostBundle\Model\Option;

class ConvertCurrencyOption implements OptionInterface
{
    public function __construct(
        /**
         * [optional] It is also possible to convert currencies using historical exchange rate data.
         * To do this, please also use the API's date parameter and set it to your preferred date.
         */
        public ?\DateTimeImmutable $date = null,

        /**
         * [optional] Changing base currency. Enter the three-letter currency code
         * of your preferred base currency. Example: USD
         */
        public ?string $base = null,

        /**
         * [optional] Enter a list currency codes to limit output currencies.
         * Example ['USD','EUR','CZK']
         *
         * @var null|string|iterable<string>
         */
        public null|string|iterable $symbols = null,

        /**
         * [optional] Round numbers to decimal place. Example: 2
         */
        public ?int $places = null,

        /**
         * [optional] You can switch source data between (default) forex, bank view or crypto currencies.
         * - Example of European Central Bank source {@link https://api.exchangerate.host/sources}: ecb
         * - Example of Crypto currencies source {@link https://api.exchangerate.host/cryptocurrencies}: crypto
         */
        public ?string $source = null,

        /**
         * [optional] API comes with support for JSONP Callbacks. This feature enables you to specify a
         * function name, pass it into the API's callback GET parameter and cause the API to return your
         * requested API response wrapped inside that function. Example: functionName
         */
        public ?string $callback = null,
    ) {
    }
}
