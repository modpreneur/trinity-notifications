<?php

namespace Trinity\NotificationBundle\Tests;

use Closure;
use Doctrine\Common\Collections\Collection;
use Traversable;
use Trinity\NotificationBundle\Tests\Entity\Client;
use Trinity\NotificationBundle\Tests\Entity\EntityDisableClient;
use Trinity\NotificationBundle\Tests\Entity\EntityWithoutClient;
use Trinity\NotificationBundle\Tests\Entity\Product;

/**
 * Class NotificationManagerTest.
 */
class NotificationManagerTest extends BaseTest
{
    /**
     * @test
     */
    public function testClientToArray()
    {
        $manager = $this->container->get('trinity.notification.client_sender');
        $method = $this->getMethod($manager, 'clientsToArray');

        $p = new Product();
        $clients = $method->invokeArgs(
            $manager,
            [$p->getClients()]
        );

        $this->assertNotEmpty($clients);

        foreach ($clients as $client) {
            $this->assertTrue($client instanceof Client);
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
     * @expectedException \Trinity\NotificationBundle\Exception\MethodException
     */
    public function testPrepareURLsError()
    {
        $manager = $this->container->get('trinity.notification.client_sender');
        $method = $this->getMethod($manager, 'prepareURL');

        $method->invokeArgs($manager, ['http://example.com', new \stdClass(), 'POST']);
    }

    /**
     * @expectedException \Trinity\NotificationBundle\Exception\ClientException
     */
    public function testPrepareURLsClientError()
    {
        $manager = $this->container->get('trinity.notification.client_sender');
        $method = $this->getMethod($manager, 'prepareURL');

        $method->invokeArgs($manager, [null, new Product(), 'POST']);
    }

    /**
     * @test
     */
    public function testPrepareURLs()
    {
        $manager = $this->container->get('trinity.notification.client_sender');
        $method = $this->getMethod($manager, 'prepareURL');

        $expected = 'http://example.com/product';
        $result = ($method->invokeArgs($manager, ['http://example.com', new Product(), 'POST']));
        $this->assertEquals($expected, $result);

        $expected = 'http://example.com/product';
        $result = ($method->invokeArgs($manager, ['http://example.com/', new Product(), 'POST']));
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function testJsonEncodeObject()
    {
        $manager = $this->container->get('trinity.notification.client_sender');
        $method = $this->getMethod($manager, 'JSONEncodeObject');

        $expected = "{\"id\":1,\"name\":\"Someone's name\",\"description\":\"Lorem impsu\"";
        $result = $method->invokeArgs($manager, [new Product(), 'KJHGHJKKJHJKJHJH']);

        $this->assertStringStartsWith($expected, $result);

        $this->assertContains('"hash":', $result);
        $this->assertContains('"timestamp":', $result);
    }

    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     *
     * @expectedExceptionMessage Client error response [url] http://example.com/product [status code] 404 [reason phrase] Not Found
     */
    public function testCreateJSONRequestError()
    {
        $manager = $this->container->get('trinity.notification.client_sender');
        $method = $this->getMethod($manager, 'JSONEncodeObject');

        $data = $method->invokeArgs($manager, [new Product(), 'KJHGHJKKJHJKJHJH']);

        $method = $this->getMethod($manager, 'createRequest');
        $result = $method->invokeArgs($manager, [$data, 'http://example.com/product', 'POST', true]);
    }

    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     *
     * @expectedExceptionMessage Client error response [url] http://example.com/product [status code] 404 [reason phrase] Not Found
     */
    public function testCreateJSONRequestError_()
    {
        $manager = $this->container->get('trinity.notification.client_sender');
        $method = $this->getMethod($manager, 'JSONEncodeObject');

        $data = $method->invokeArgs($manager, [new Product(), 'KJHGHJKKJHJKJHJH']);

        $method = $this->getMethod($manager, 'createRequest');
        $result = $method->invokeArgs($manager, [$data, 'http://example.com/product', 'POST', false]);
    }

    /**
     * @test
     */
    public function testSendWithoutClient()
    {
        $manager = $this->container->get('trinity.notification.client_sender');

        $entity = new EntityWithoutClient();

        $result = $manager->send($entity);
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function testSendWithDisableClient()
    {
        $manager = $this->container->get('trinity.notification.client_sender');
        $entity = new EntityDisableClient();
        $result = $manager->send($entity);

        $this->assertEmpty($result);
    }
}
