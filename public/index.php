<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Orbit\Finance\Support\ApiKernel;

$kernel = new ApiKernel();
$kernel->handle();
