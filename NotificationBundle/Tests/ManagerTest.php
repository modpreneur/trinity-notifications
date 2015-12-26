<?php

namespace Trinity\NotificationBundle\Tests;

use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Tests\Entity\EntityInterfaceDisableClient;
use Trinity\NotificationBundle\Tests\Entity\EntityInterfaceWithoutClient;
use Trinity\NotificationBundle\Tests\Entity\Product;
use Trinity\NotificationBundle\Tests\Sandbox\Entity\Client;


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
        $manager = $this->getContainer()->get('trinity.notification.manager');

        $entity = new EntityInterfaceWithoutClient();

        $result = $manager->send($entity);
        $this->assertEmpty($result);
    }


    /**
     * @test
     */
    public function testClientToArray()
    {
        $manager = $this->getContainer()->get('trinity.notification.manager');
        $method = $this->getMethod($manager, 'clientsToArray');

        $p = new Product();
        $clients = $method->invokeArgs(
            $manager,
            [$p->getClients()]
        );

        // NULL
        $this->assertEquals([], $method->invokeArgs($manager, [null]));

        $this->assertNotEmpty($clients);

        foreach ($clients as $client) {
            $this->assertTrue($client instanceof ClientInterface);

            // ClientInterface
            $client = new Client();
            $this->assertEquals([$client], $method->invokeArgs($manager, [$client]));

            // Collection
            $this->assertEquals([$client], $method->invokeArgs($manager, [new TestCollection()]));

            // Array
            $this->assertEquals([$client], $method->invokeArgs($manager, [[$client]]));

        }
    }


    /**
     * @test
     */
    public function testSendWithDisableClient()
    {
        $manager = $this->getContainer()->get('trinity.notification.manager');
        $entity = new EntityInterfaceDisableClient();
        $result = $manager->send($entity);

        $this->assertEmpty($result);
    }

}
