<?php

namespace DusanKasan\Knapsack\Tests\Helpers;

class Car extends Machine
{
    /**
     * @var int
     */
    private $numberOfSeats;

    /**
     * @param string $name
     * @param int $numberOfSeats
     */
    public function __construct($name, $numberOfSeats)
    {
        parent::__construct($name);
        $this->numberOfSeats = $numberOfSeats;
    }
}
