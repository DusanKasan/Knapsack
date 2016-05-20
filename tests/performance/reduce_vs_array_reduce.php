<?php

use DusanKasan\Knapsack\Collection;
use Symfony\Component\Console\Helper\Table;

include_once __DIR__ . "/../../vendor/autoload.php";

const NUMBER_OF_ITEMS = 10000;
const REPEAT_COUNT = 10;

function getIntegerReport()
{
    $arrayMapDeltas = 0.0;
    $collectionMapDeltas = 0.0;
    $fixtureProvider = function () {
        $array = [];
        for ($i = 0; $i < NUMBER_OF_ITEMS; $i++) {
            $array[] = $i;
        }

        return $array;
    };
    $mapper = function ($temp, $item) {
        return $item + $temp;
    };

    for ($j = 0; $j < REPEAT_COUNT; $j++) {
        $array = $fixtureProvider();


        $arrayMapStart = microtime(true);
        array_reduce($array, $mapper, 0);
        $arrayMapDeltas += microtime(true) - $arrayMapStart;

        $collection = new Collection($array);
        $collectionMapStart = microtime(true);
        $collection->reduce($mapper, 0);
        $collectionMapDeltas += microtime(true) - $collectionMapStart;
    }

    return [
        'name' => 'array_reduce vs Collection::reduce on ' . NUMBER_OF_ITEMS . ' integers (addition)',
        'native' => (float) $arrayMapDeltas / REPEAT_COUNT,
        'collection' => (float) $collectionMapDeltas / REPEAT_COUNT
    ];
}

function getStringReport()
{
    $arrayMapDeltas = 0.0;
    $collectionMapDeltas = 0.0;
    $fixtureProvider = function () {
        $array = [];
        for ($i = 0; $i < NUMBER_OF_ITEMS; $i++) {
            $array[] = $i . 'asd';
        }

        return $array;
    };
    $mapper = function ($temp, $item) {
        return $temp . $item;
    };

    for ($j = 0; $j < REPEAT_COUNT; $j++) {
        $array = $fixtureProvider();
        $arrayMapStart = microtime(true);
        array_reduce($array, $mapper, '');
        $arrayMapDeltas += microtime(true) - $arrayMapStart;

        $array = $fixtureProvider();
        $collection = new Collection($array);
        $collectionMapStart = microtime(true);
        $collection->reduce($mapper, '');
        $collectionMapDeltas += microtime(true) - $collectionMapStart;
    }

    return [
        'name' => 'array_reduce vs Collection::reduce on ' . NUMBER_OF_ITEMS . ' strings (concatenation)',
        'native' => (float) $arrayMapDeltas / REPEAT_COUNT,
        'collection' => (float) $collectionMapDeltas / REPEAT_COUNT
    ];
}

function getObjectReport()
{
    $arrayMapDeltas = 0.0;
    $collectionMapDeltas = 0.0;
    $fixtureProvider = function () {
        $array = [];
        for ($i = 0; $i < NUMBER_OF_ITEMS; $i++) {
            $c = new stdClass();
            $c->asd = 1;
            $array[] = $c;
        }

        return $array;
    };
    $mapper = function ($temp, $item) {
        return $temp + $item->asd;
    };

    for ($j = 0; $j < REPEAT_COUNT; $j++) {
        $array = $fixtureProvider();
        $arrayMapStart = microtime(true);
        array_reduce($array, $mapper, 0);
        $arrayMapDeltas += microtime(true) - $arrayMapStart;

        $array = $fixtureProvider();
        $collection = new Collection($array);
        $collectionMapStart = microtime(true);
        $collection->reduce($mapper, 0);
        $collectionMapDeltas += microtime(true) - $collectionMapStart;
    }

    return [
        'name' => 'array_reduce vs Collection::reduce on ' . NUMBER_OF_ITEMS . ' object (object to field value)',
        'native' => (float) $arrayMapDeltas / REPEAT_COUNT,
        'collection' => (float) $collectionMapDeltas / REPEAT_COUNT
    ];
}

function getComplexOperationReport()
{
    $arrayMapDeltas = 0.0;
    $collectionMapDeltas = 0.0;
    $fixtureProvider = function () {
        $array = [];
        for ($i = 0; $i < NUMBER_OF_ITEMS; $i++) {
            $array[] = $i;
        }

        return $array;
    };
    $mapper = function ($temp, $item) {
        $result = 0;
        for (; $item > 0; $item--) {
            $result += $item;
        }

        return $temp + $result;
    };

    for ($j = 0; $j < REPEAT_COUNT; $j++) {
        $array = $fixtureProvider();
        $arrayMapStart = microtime(true);
        array_reduce($array, $mapper, 0);
        $arrayMapDeltas += microtime(true) - $arrayMapStart;

        $array = $fixtureProvider();
        $collection = new Collection($array);
        $collectionMapStart = microtime(true);
        $collection->reduce($mapper, 0);
        $collectionMapDeltas += microtime(true) - $collectionMapStart;
    }

    return [
        'name' => 'array_reduce vs Collection::reduce for ' . NUMBER_OF_ITEMS . ' integers n, counting sum(0, n) the naive way',
        'native' => (float) $arrayMapDeltas / REPEAT_COUNT,
        'collection' => (float) $collectionMapDeltas / REPEAT_COUNT
    ];
}

function getHashReport()
{
    $arrayMapDeltas = 0.0;
    $collectionMapDeltas = 0.0;
    $fixtureProvider = function () {
        $array = [];
        for ($i = 0; $i < NUMBER_OF_ITEMS; $i++) {
            $array[] = $i . 'asdf';
        }

        return $array;
    };
    $mapper = function ($temp, $item) {
        return md5($temp . $item);
    };

    for ($j = 0; $j < REPEAT_COUNT; $j++) {
        $array = $fixtureProvider();
        $arrayMapStart = microtime(true);
        array_reduce($array, $mapper, '');
        $arrayMapDeltas += microtime(true) - $arrayMapStart;

        $array = $fixtureProvider();
        $collection = new Collection($array);
        $collectionMapStart = microtime(true);
        $collection->reduce($mapper, '');
        $collectionMapDeltas += microtime(true) - $collectionMapStart;
    }

    return [
        'name' => 'array_reduce vs Collection::reduce on ' . NUMBER_OF_ITEMS . ' md5 invocations',
        'native' => (float) $arrayMapDeltas / REPEAT_COUNT,
        'collection' => (float) $collectionMapDeltas / REPEAT_COUNT
    ];
}

function addReportToTable(Table $table, $reportData)
{
    $row = [
        $reportData['name'],
        $reportData['native'] . 's',
        $reportData['collection'] . 's',
        ((int) (($reportData['collection'] / $reportData['native']) * 100)) . '%',
    ];

    $table->addRow($row);
}

$table = new Table(new Symfony\Component\Console\Output\ConsoleOutput());
$table->setHeaders(['operation details', 'native execution time', 'collection execution time', 'difference (percent)']);

addReportToTable($table, getIntegerReport());
addReportToTable($table, getStringReport());
addReportToTable($table, getObjectReport());
addReportToTable($table, getHashReport());
addReportToTable($table, getComplexOperationReport());

$table->render();
