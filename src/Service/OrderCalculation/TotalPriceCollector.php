<?php

namespace App\Service\OrderCalculation;

use App\Entity\Orders;

class TotalPriceCollector implements OrderCalculationCollectorInterface
{
    public function calculate(Orders $order): array
    {
        $total = 0;
        foreach ($order->getOrdersItems() as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }

        return ['total_price' => $total];
    }
}
