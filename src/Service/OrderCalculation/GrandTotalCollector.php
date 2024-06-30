<?php

namespace App\Service\OrderCalculation;

use App\Entity\Orders;
use App\Service\Configuration\VatConfig;

class GrandTotalCollector implements OrderCalculationCollectorInterface
{
    private $vatConfig;

    public function __construct(VatConfig $vatConfig)
    {
        $this->vatConfig = $vatConfig;
    }

    public function calculate(Orders $order): array
    {
        $total = 0;
        foreach ($order->getOrdersItems() as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }
        $vat = $total * $this->vatConfig->getVatRate();
        $grandTotal = $total + $vat;
        return ['grand_total' => round($grandTotal, 3)];
    }
}
