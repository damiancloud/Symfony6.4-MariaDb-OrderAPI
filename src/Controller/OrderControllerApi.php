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

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[Route('/orders')]
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
    #[OA\Post(
        path: "/orders/create",
        summary: "Create a new order",
        tags: ["Order"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Order 1"),
                    new OA\Property(
                        property: "items",
                        type: "array",
                        items: new OA\Items(
                            type: "object",
                            properties: [
                                new OA\Property(property: "product_id", type: "integer", example: 1),
                                new OA\Property(property: "quantity", type: "integer", example: 2)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Order created",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Order was created"),
                        new OA\Property(property: "order", type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Order 1"),
                                new OA\Property(property: "date", type: "string", format: "date-time", example: "2024-07-01 12:34:56"),
                                new OA\Property(
                                    property: "items",
                                    type: "array",
                                    items: new OA\Items(
                                        type: "object",
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "name", type: "string", example: "Product Name"),
                                            new OA\Property(property: "price", type: "number", format: "float", example: 19.99),
                                            new OA\Property(property: "quantity", type: "integer", example: 2)
                                        ]
                                    )
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid JSON data",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Invalid JSON data")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Error creating order",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Error creating order")
                    ]
                )
            )
        ]
    )]
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
            'message' => 'Order was created',
            'order' => [
                'id' => $order->getId(),
                'name' => $order->getName(),
                'date' => $order->getDate() ? $order->getDate()->format('Y-m-d H:i:s') : null,
                'items' => $this->serializeItems($order->getOrdersItems()),
            ],
        ]);
    }

    #[Route('/{id}', name: 'orders_show', methods: ['GET'])]
    #[OA\Get(
        path: "/orders/{id}",
        summary: "Show order details",
        tags: ["Order"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer"),
                description: "Order ID"
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Order details",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "name", type: "string", example: "Order 1"),
                        new OA\Property(property: "date", type: "string", format: "date-time", example: "2024-07-01 12:34:56"),
                        new OA\Property(
                            property: "items",
                            type: "array",
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "Product 1"),
                                    new OA\Property(property: "price", type: "number", format: "float", example: 10),
                                    new OA\Property(property: "quantity", type: "integer", example: 3)
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "calculation", 
                            type: "array", 
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "grand_total", type: "number", format: "float", example: 36.9),
                                    new OA\Property(property: "total_price", type: "number", format: "float", example: 30),
                                    new OA\Property(property: "total_vat", type: "number", format: "float", example: 6.9),
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Order not found",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Order not found")
                    ]
                )
            )
        ]
    )]
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
