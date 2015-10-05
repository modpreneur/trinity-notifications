<?php

namespace Trinity\NotificationBundle\Tests;

use Trinity\FrameworkBundle\Entity\IClient;
use Trinity\NotificationBundle\Tests\Entity\Client;
use Trinity\NotificationBundle\Tests\Entity\EntityDisableClient;
use Trinity\NotificationBundle\Tests\Entity\EntityWithoutClient;
use Trinity\NotificationBundle\Tests\Entity\Product;


/**
 * Class ManagerTest.
 */
class ManagerTest extends BaseTest
{
    /**
     * @test
     */
    public function testSendWithoutClient()
    {
        $manager = $this->container->get('trinity.notification.manager');

        $entity = new EntityWithoutClient();

        $result = $manager->send($entity);
        $this->assertEmpty($result);
    }


    /**
     * @test
     */
    public function testClientToArray()
    {
        $manager = $this->container->get('trinity.notification.manager');
        $method = $this->getMethod($manager, 'clientsToArray');

        $p = new Product();
        $clients = $method->invokeArgs(
            $manager,
            [$p->getClients()]
        );

        $this->assertNotEmpty($clients);

        foreach ($clients as $client) {
            $this->assertTrue($client instanceof IClient);
        }

        // NULL
        $this->assertEquals([], $method->invokeArgs($manager, [null]));

        // Client
        $client = new Client();
        $this->assertEquals([$client], $method->invokeArgs($manager, [$client]));

        // Collection
        $this->assertEquals([$client], $method->invokeArgs($manager, [new TestCollection()]));

        // Array
        $this->assertEquals([$client], $method->invokeArgs($manager, [[$client]]));
    }


    /**
     * @test
     */
    public function testSendWithDisableClient()
    {
        $manager = $this->container->get('trinity.notification.manager');
        $entity = new EntityDisableClient();
        $result = $manager->send($entity);

        $this->assertEmpty($result);
    }

}
