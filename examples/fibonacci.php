<?php

use Knapsack\Collection;

include_once "../vendor/autoload.php";

$collection = new Collection([[1, 1]]);
$result = $collection
    ->iterate(function ($v) {
        return [$v[1], $v[0] + $v[1]];
    })
    ->map(function ($v) {
        return $v[0];
    })
    ->take(5)
    ->resetKeys()
    ->toArray();

assert($result == [1, 1, 2, 3, 5]);
