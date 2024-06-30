<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Repository\OrdersRepository;
use App\Entity\Product;
use App\Entity\OrdersItem;
use App\Service\OrderCalculation\OrderCalculatorService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

#[Route('/order')]
class OrderControllerApi extends AbstractController
{
    private LoggerInterface $logger;
    private OrderCalculatorService $orderCalculatorService;

    public function __construct(LoggerInterface $logger, OrderCalculatorService $orderCalculatorService)
    {
        $this->logger = $logger;
        $this->orderCalculatorService = $orderCalculatorService;
    }

    #[Route('/create', name: 'create_order', methods: ['POST'])]
    public function createOrder(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['name'], $data['items']) || !is_array($data['items'])) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['name'], $data['items']) || !is_array($data['items'])) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $order = new Orders();
        $order->setName((string) $data['name']);

        foreach ($data['items'] as $itemData) {
            if (!isset($itemData['product_id'], $itemData['quantity'])) {
                $this->logger->error('Missing product_id or quantity in item data', ['itemData' => $itemData]);
                continue;
            }

            $product = $em->getRepository(Product::class)->find($itemData['product_id']);
            if (!$product) {
                $this->logger->error('Product not found', ['product_id' => $itemData['product_id']]);
                continue;
            }

            $orderItem = new OrdersItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($itemData['quantity']);

            $order->addOrdersItem($orderItem);
            $em->persist($orderItem);
        }

        try {
            $em->persist($order);
            $em->flush();
        } catch (\Exception $e) {
            $this->logger->error('Error persisting order', ['exception' => $e->getMessage()]);
            return new JsonResponse(['error' => 'Error creating order' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'message' => 'Zamówienie zostało utworzone',
            'order' => [
                'id' => $order->getId(),
                'name' => $order->getName(),
                'date' => $order->getDate() ? $order->getDate()->format('Y-m-d H:i:s') : null,
                'items' => $this->serializeItems($order->getOrdersItems()),
            ],
        ]);
    }

    #[Route('/{id}', name:'orders_show', methods: ['GET'])]
    public function show(int $id, OrdersRepository $ordersRepository): JsonResponse
    {
        $order = $ordersRepository->find($id);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $calculatedValues = $this->orderCalculatorService->calculate($order);

        $data = [
            'id' => $order->getId(),
            'name' => $order->getName(),
            'date' => $order->getDate()->format('Y-m-d H:i:s'),
            'items' => $this->serializeItems($order->getOrdersItems()),
            'calculation' => $calculatedValues
        ];

        return $this->json($data);
    }

    /**
     * @param iterable<OrdersItem> $items
     * @return array<mixed>
     */
    private function serializeItems(iterable $items): array
    {
        $serialized = [];
        foreach ($items as $item) {
            $serialized[] = [
                'id' => $item->getProduct()->getId(),
                'name' => $item->getProduct()->getTitle(),
                'price' => $item->getProduct()->getPrice(),
                'quantity' => $item->getQuantity(),
            ];
        }

        return $serialized;
    }
}
