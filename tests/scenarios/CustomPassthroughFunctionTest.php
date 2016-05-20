<?php

namespace DusanKasan\Knapsack\Tests\Scenarios;

use DusanKasan\Knapsack\Collection;
use PHPUnit_Framework_TestCase;

class CustomPassthroughFunctionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Example of implementing a transpose function and how to apply it over a collection.
     *
     * For more on how this can be useful: http://adamwathan.me/2016/04/06/cleaning-up-form-input-with-transpose/
     */
    public function testIt()
    {
        $formData = [
            'names' => [
                'Jane',
                'Bob',
                'Mary',
            ],
            'emails' => [
                'jane@example.com',
                'bob@example.com',
                'mary@example.com',
            ],
            'occupations' => [
                'Doctor',
                'Plumber',
                'Dentist',
            ],
        ];

        //Must take and return a Collection
        $transpose = function (Collection $collections) {
            $transposed = array_map(
                function (...$items) {
                    return $items;
                },
                ...$collections->values()->toArray()
            );

            return Collection::from($transposed);
        };

        $result = Collection::from($formData)
            ->transform($transpose)
            ->toArray();

        $expected = [
            [
                'Jane',
                'jane@example.com',
                'Doctor'
            ],
            [
                'Bob',
                'bob@example.com',
                'Plumber'
            ],
            [
                'Mary',
                'mary@example.com',
                'Dentist'
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}
