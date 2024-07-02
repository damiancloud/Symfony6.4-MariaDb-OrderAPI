<?php

namespace App\Tests\Imtegraton;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Orders;
use App\Entity\OrdersItem;

use Doctrine\ORM\EntityManagerInterface;

class OrderControllerApiTest extends WebTestCase
{
    private HttpClientInterface $client;
    private ?int $lastOrderId = null;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = HttpClient::create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
  
    public function testShowOrder()
    {
        $orderId = 1;
        $response = $this->client->request('GET', 'http://nginx/orders/' . $orderId);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = $response->toArray();
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('date', $responseData);
        $this->assertArrayHasKey('items', $responseData);
        $this->assertArrayHasKey('calculation', $responseData);

        $this->assertEquals($orderId, $responseData['id']);
    }

    public function testShowOrderNotFound()
    {
        $orderId = 9999;
        $response = $this->client->request('GET', 'http://nginx/orders/' . $orderId);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testCreateOrder()
    {
        $data = [
            'name' => 'Order test',
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2
                ],
                [
                    'product_id' => 2,
                    'quantity' => 3
                ]
            ]
        ];

        $response = $this->client->request('POST', 'http://nginx/orders/create', [
            'json' => $data
        ]);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = $response->toArray();
        $this->lastOrderId = $responseData['order']['id'];

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Order was created', $responseData['message']);

        $this->assertArrayHasKey('order', $responseData);
        $order = $responseData['order'];

        $this->assertArrayHasKey('name', $order);
        $this->assertEquals('Order test', $order['name']);
        $this->assertArrayHasKey('date', $order);
        $this->assertArrayHasKey('items', $order);
        $this->assertCount(2, $order['items']);

        $item1 = $order['items'][0];
        $this->assertArrayHasKey('id', $item1);
        $this->assertArrayHasKey('name', $item1);
        $this->assertArrayHasKey('price', $item1);
        $this->assertArrayHasKey('quantity', $item1);
        $this->assertEquals(2, $item1['quantity']);

        $item2 = $order['items'][1];
        $this->assertArrayHasKey('id', $item2);
        $this->assertArrayHasKey('name', $item2);
        $this->assertArrayHasKey('price', $item2);
        $this->assertArrayHasKey('quantity', $item2);
        $this->assertEquals(3, $item2['quantity']);
    }
}
