<?php

namespace scenarios\Knapsack;

use Knapsack\Collection;
use PHPUnit_Framework_TestCase;

/**
 * http://martinfowler.com/articles/refactoring-pipelines.html#GroupingFlightRecords
 */
class GroupingFlightsTest extends PHPUnit_Framework_TestCase
{
    private $inputData = [
        [
            "origin" => "BOS",
            "dest" => "LAX",
            "date" => "2015-01-12",
            "number" => "25",
            "carrier" => "AA",
            "delay" => 10.0,
            "cancelled" => false
        ],
        [
            "origin" => "BOS",
            "dest" => "LAX",
            "date" => "2015-01-13",
            "number" => "25",
            "carrier" => "AA",
            "delay" => 0.0,
            "cancelled" => true
        ],
    ];

    public function testIt()
    {
        $collection = new Collection($this->inputData);

        $result = $collection
            ->groupBy(function ($v) {
                return $v['dest'];
            })
            ->map([$this, 'summarize'])
            ->map([$this, 'buildResults'])
            ->toArray();

        $expected = [
            'LAX' => [
                'meanDelay' => 10,
                'cancellationRate' => 0.5
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function summarize(array $flightsData)
    {
        $flights = new Collection($flightsData);

        $numCancellations = $flights
            ->filter(function ($f) {
                return $f['cancelled'];
            })
            ->size();

        $totalDelay = $flights
            ->reject(function ($f) {
                return $f['cancelled'];
            })
            ->reduce(
                function ($tmp, $f) {
                    return $tmp + $f['delay'];
                },
                0
            );

        return [
            'numFlights' => $flights->size(),
            'numCancellations' => $numCancellations,
            'totalDelay' => $totalDelay
        ];
    }

    public function buildResults(array $airport)
    {
        return [
            'meanDelay' => $airport['totalDelay'] / ($airport['numFlights'] - $airport['numCancellations']),
            'cancellationRate' => $airport['numCancellations'] / $airport['numFlights']
        ];
    }
}
