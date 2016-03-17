<?php

namespace Trinity\NotificationBundle\Tests;

use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Tests\Entity\EntityInterfaceDisableClient;
use Trinity\NotificationBundle\Tests\Entity\EntityInterfaceWithoutClient;
use Trinity\NotificationBundle\Tests\Sandbox\Entity\Client;
use Trinity\NotificationBundle\Tests\Sandbox\Entity\Product;


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

            // TestClient
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

        $this->assertArrayHasKey('error', $result[0]);
    }


    /**
     * @test
     */
    public function testSyncEntity(){
        $manager = $this->getContainer()->get('trinity.notification.manager');
        $entity = new Product();
        $client = $entity->getClients()[0];

        $r = $manager->syncEntity($entity, $client);

        $this->assertEquals([
            'code'       => 200,
            'statusCode' => 200,
            'message'    => 'OK'
        ], $r[0]);
    }

}
