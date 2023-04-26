<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {
    $config->import('vendor/e-lodgy/coding-standard/ecs.php');

    $config->paths(['config/', 'src/', 'tests/', 'ecs.php']);
    $config->skip(['tests/app/var/']);
};
