<?php

use Knapsack\Collection;
use Symfony\Component\Console\Helper\Table;

include_once "../../vendor/autoload.php";

function getIntegerReport()
{
    $arrayMapDeltas = 0.0;
    $collectionMapDeltas = 0.0;
    $fixtureProvider = function () {
        $array = [];
        for ($i = 0; $i < 1000; $i++) {
            $array[] = $i;
        }

        return $array;
    };
    $mapper = function ($item) {
        return $item + 1;
    };

    for ($j = 0; $j < 10; $j++) {
        $array = $fixtureProvider();


        $arrayMapStart = microtime(true);
        $mappedArray = array_map($mapper, $array);
        foreach ($mappedArray as $item) {
        }
        $arrayMapDeltas += microtime(true) - $arrayMapStart;

        $collection = new Collection($array);
        $collectionMapStart = microtime(true);
        $mappedCollection = $collection->map($mapper);
        foreach ($mappedCollection as $item) {
        }
        $collectionMapDeltas += microtime(true) - $collectionMapStart;
    }

    return [
        'name' => 'array_map vs Collection::map on 1000 integers (addition)',
        'native' => $arrayMapDeltas / 10.0,
        'collection' => $collectionMapDeltas / 10.0
    ];
}

function getStringReport()
{
    $arrayMapDeltas = 0.0;
    $collectionMapDeltas = 0.0;
    $fixtureProvider = function () {
        $array = [];
        for ($i = 0; $i < 1000; $i++) {
            $array[] = $i . 'asd';
        }

        return $array;
    };
    $mapper = function ($item) {
        return $item . 'qwe';
    };

    for ($j = 0; $j < 10; $j++) {
        $array = $fixtureProvider();
        $arrayMapStart = microtime(true);
        $mappedArray = array_map($mapper, $array);
        foreach ($mappedArray as $item) {
        }
        $arrayMapDeltas += microtime(true) - $arrayMapStart;

        $array = $fixtureProvider();
        $collection = new Collection($array);
        $collectionMapStart = microtime(true);
        $mappedCollection = $collection->map($mapper);
        foreach ($mappedCollection as $item) {
        }
        $collectionMapDeltas += microtime(true) - $collectionMapStart;
    }

    return [
        'name' => 'array_map vs Collection::map on 1000 strings (concatenation)',
        'native' => $arrayMapDeltas / 10.0,
        'collection' => $collectionMapDeltas / 10.0
    ];
}

function getObjectReport()
{
    $arrayMapDeltas = 0.0;
    $collectionMapDeltas = 0.0;
    $fixtureProvider = function () {
        $array = [];
        for ($i = 0; $i < 1000; $i++) {
            $c = new stdClass();
            $c->asd = 1;
            $array[] = $c;
        }

        return $array;
    };
    $mapper = function ($item) {
        return $item->asd;
    };

    for ($j = 0; $j < 10; $j++) {
        $array = $fixtureProvider();
        $arrayMapStart = microtime(true);
        $mappedArray = array_map($mapper, $array);
        foreach ($mappedArray as $item) {
        }
        $arrayMapDeltas += microtime(true) - $arrayMapStart;

        $array = $fixtureProvider();
        $collection = new Collection($array);
        $collectionMapStart = microtime(true);
        $mappedCollection = $collection->map($mapper);
        foreach ($mappedCollection as $item) {
        }
        $collectionMapDeltas += microtime(true) - $collectionMapStart;
    }

    return [
        'name' => 'array_map vs Collection::map on 1000 object (object to field value)',
        'native' => $arrayMapDeltas / 10.0,
        'collection' => $collectionMapDeltas / 10.0
    ];
}

function getComplexOperationReport()
{
    $arrayMapDeltas = 0.0;
    $collectionMapDeltas = 0.0;
    $fixtureProvider = function () {
        $array = [];
        for ($i = 0; $i < 1000; $i++) {
            $array[] = $i;
        }

        return $array;
    };
    $mapper = function ($item) {
        $result = 0;
        for (; $item > 0; $item--) {
            $result += $item;
        }

        return $result;
    };

    for ($j = 0; $j < 10; $j++) {
        $array = $fixtureProvider();
        $arrayMapStart = microtime(true);
        $mappedArray = array_map($mapper, $array);
        foreach ($mappedArray as $item) {
        }
        $arrayMapDeltas += microtime(true) - $arrayMapStart;

        $array = $fixtureProvider();
        $collection = new Collection($array);
        $collectionMapStart = microtime(true);
        $mappedCollection = $collection->map($mapper);
        foreach ($mappedCollection as $item) {
        }
        $collectionMapDeltas += microtime(true) - $collectionMapStart;
    }

    return [
        'name' => 'array_map vs Collection::map for 1000 integers n, counting sum(0, n) the naive way',
        'native' => $arrayMapDeltas / 10.0,
        'collection' => $collectionMapDeltas / 10.0
    ];
}

function getHashReport()
{
    $arrayMapDeltas = 0.0;
    $collectionMapDeltas = 0.0;
    $fixtureProvider = function () {
        $array = [];
        for ($i = 0; $i < 1000; $i++) {
            $array[] = $i . 'asdf';
        }

        return $array;
    };
    $mapper = function ($item) {
        return md5($item);
    };

    for ($j = 0; $j < 10; $j++) {
        $array = $fixtureProvider();
        $arrayMapStart = microtime(true);
        $mappedArray = array_map($mapper, $array);
        foreach ($mappedArray as $item) {
        }
        $arrayMapDeltas += microtime(true) - $arrayMapStart;

        $array = $fixtureProvider();
        $collection = new Collection($array);
        $collectionMapStart = microtime(true);
        $mappedCollection = $collection->map($mapper);
        foreach ($mappedCollection as $item) {
        }
        $collectionMapDeltas += microtime(true) - $collectionMapStart;
    }

    return [
        'name' => 'array_map vs Collection::map on 1000 md5 invocations',
        'native' => $arrayMapDeltas / 10.0,
        'collection' => $collectionMapDeltas / 10.0
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
