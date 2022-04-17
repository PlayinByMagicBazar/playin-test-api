<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Order;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class OrderValidatorTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testValidation(): void
    {
        $em = static::$manager;
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $orders = $em->getRepository(Order::class)->findAll();
        foreach($orders as $order){
            $client->request('GET', '/api/orders/'.$order->getId().'/validation');
        }
        self::assertResponseIsSuccessful();
    }
} 