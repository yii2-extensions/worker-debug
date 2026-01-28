<?php

declare(strict_types=1);

/**
 * Overrides internal-mocker stubs for specific functions.
 */
$stubs = require __DIR__ . '/../../vendor/xepozz/internal-mocker/src/stubs.php';

if (is_array($stubs) === false) {
    $stubs = [];
}

$stubs['microtime'] = [
    'signatureArguments' => 'bool $as_float = false',
    'arguments' => '$as_float',
];

return $stubs;
