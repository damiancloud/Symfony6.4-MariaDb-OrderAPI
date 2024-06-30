<?php

namespace App\Service\OrderCalculation;

use App\Entity\Orders;

interface OrderCalculationCollectorInterface
{
    public function calculate(Orders $order): array;
}
