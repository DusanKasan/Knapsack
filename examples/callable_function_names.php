<?php

use Knapsack\Collection;

include_once "../vendor/autoload.php";

$collection = new Collection([true, 1, new stdClass(), 2]);
$result = $collection
    ->reject('is_bool')
    ->reject('is_object')
    ->concat([3, 4])
    ->resetKeys()
    ->splitAt(2)
    ->toArray();

assert($result == [[1, 2], [2 => 3, 3 => 4]]);
