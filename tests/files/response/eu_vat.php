<?php

declare(strict_types=1);

use Benjaminmal\ExchangeRateHostBundle\Model\Output\VatRates;

return [
    'AT' => new VatRates(
        countryName: 'Austria',
        standardRate: 20,
        reducedRates: [
            10,
            13,
        ],
        superReducedRates: [],
        parkingRates: [
            13,
        ],
    ),
    'BE' => new VatRates(
        countryName: 'Belgium',
        standardRate: 21,
        reducedRates: [
            6,
            12,
        ],
        superReducedRates: [],
        parkingRates: [
            12,
        ],
    ),
    'BG' => new VatRates(
        countryName: 'Bulgaria',
        standardRate: 20,
        reducedRates: [
            9,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'CY' => new VatRates(
        countryName: 'Cyprus',
        standardRate: 19,
        reducedRates: [
            5,
            9,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'CZ' => new VatRates(
        countryName: 'Czechia',
        standardRate: 21,
        reducedRates: [
            10,
            15,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'DE' => new VatRates(
        countryName: 'Germany',
        standardRate: 19,
        reducedRates: [
            7,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'DK' => new VatRates(
        countryName: 'Denmark',
        standardRate: 25,
        reducedRates: [],
        superReducedRates: [],
        parkingRates: [],
    ),
    'EE' => new VatRates(
        countryName: 'Estonia',
        standardRate: 20,
        reducedRates: [
            9,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'EL' => new VatRates(
        countryName: 'Greece',
        standardRate: 24,
        reducedRates: [
            6,
            13,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'ES' => new VatRates(
        countryName: 'Spain',
        standardRate: 21,
        reducedRates: [
            10,
        ],
        superReducedRates: [
            4,
        ],
        parkingRates: [],
    ),
    'FI' => new VatRates(
        countryName: 'Finland',
        standardRate: 24,
        reducedRates: [
            10,
            14,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'FR' => new VatRates(
        countryName: 'France',
        standardRate: 20,
        reducedRates: [
            5.5,
            10,
        ],
        superReducedRates: [
            2.1,
        ],
        parkingRates: [],
    ),
    'HR' => new VatRates(
        countryName: 'Croatia',
        standardRate: 25,
        reducedRates: [
            5,
            13,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'HU' => new VatRates(
        countryName: 'Hungary',
        standardRate: 27,
        reducedRates: [
            5,
            18,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'IE' => new VatRates(
        countryName: 'Ireland',
        standardRate: 23,
        reducedRates: [
            9,
            13.5,
        ],
        superReducedRates: [
            4.8,
        ],
        parkingRates: [
            13.5,
        ],
    ),
    'IT' => new VatRates(
        countryName: 'Italy',
        standardRate: 22,
        reducedRates: [
            5,
            10,
        ],
        superReducedRates: [
            4,
        ],
        parkingRates: [],
    ),
    'LT' => new VatRates(
        countryName: 'Lithuania',
        standardRate: 21,
        reducedRates: [
            5,
            9,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'LU' => new VatRates(
        countryName: 'Luxembourg',
        standardRate: 17,
        reducedRates: [
            8,
        ],
        superReducedRates: [
            3,
        ],
        parkingRates: [
            14,
        ],
    ),
    'LV' => new VatRates(
        countryName: 'Latvia',
        standardRate: 21,
        reducedRates: [
            12,
            5,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'MT' => new VatRates(
        countryName: 'Malta',
        standardRate: 18,
        reducedRates: [
            5,
            7,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'NL' => new VatRates(
        countryName: 'Netherlands',
        standardRate: 21,
        reducedRates: [
            9,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'PL' => new VatRates(
        countryName: 'Poland',
        standardRate: 23,
        reducedRates: [
            5,
            8,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'PT' => new VatRates(
        countryName: 'Portugal',
        standardRate: 23,
        reducedRates: [
            6,
            13,
        ],
        superReducedRates: [],
        parkingRates: [
            13,
        ],
    ),
    'RO' => new VatRates(
        countryName: 'Romania',
        standardRate: 19,
        reducedRates: [
            5,
            9,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'SE' => new VatRates(
        countryName: 'Sweden',
        standardRate: 25,
        reducedRates: [
            6,
            12,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'SI' => new VatRates(
        countryName: 'Slovenia',
        standardRate: 22,
        reducedRates: [
            5,
            9.5,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
    'SK' => new VatRates(
        countryName: 'Slovakia',
        standardRate: 20,
        reducedRates: [
            10,
        ],
        superReducedRates: [],
        parkingRates: [],
    ),
];
