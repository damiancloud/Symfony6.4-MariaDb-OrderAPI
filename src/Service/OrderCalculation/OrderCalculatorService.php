<?php

namespace App\Service\OrderCalculation;

use App\Entity\Orders;

class OrderCalculatorService
{
    private $collectors = [];

    public function __construct(iterable $collectors)
    {
        foreach ($collectors as $collector) {
            $this->addCollector($collector);
        }
    }

    public function addCollector(OrderCalculationCollectorInterface $collector): void
    {
        $this->collectors[] = $collector;
    }

    public function calculate(Orders $order): array
    {
        $result = [];
        foreach ($this->collectors as $collector) {
            $result = array_merge($result, $collector->calculate($order));
        }

        return $result;
    }
}
