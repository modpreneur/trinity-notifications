<?php

/**
 *  This file is part of the Trinity project.
 */
namespace Trinity\NotificationBundle\Tests;

use Trinity\NotificationBundle\Tests\Entity\Product;

/**
 * Class SendNotificationTest.
 *
 *
 *
 * For (http://api.dev.clickandcoach.com/)
 */
class SendNotificationTest extends BaseTest
{
    /**
     * @test
     */
    public function testEntityToArray()
    {
        $service = $this->container->get('trinity.notification.client_sender');
        $class = (get_class($service));
        $method = self::getMethod($class, 'clientsToArray');
        $e = new Product();

        $array = $method->invokeArgs($service, [$e]);

        $this->assertTrue(is_array($array));
    }

    /**
     * @test
     */
    public function testUrl()
    {
        $service = $this->container->get('trinity.notification.client_sender');
        $class = (get_class($service));
        $e = new Product();

        $urlWithEndSlash = 'http://seznam.cz/';
        $urlWithoutEndSlash = 'http://seznam.cz';

        $method = self::getMethod($class, 'prepareURL');
        $this->assertSame(
            'http://seznam.cz/product',
            $method->invokeArgs($service, [$urlWithEndSlash, $e, 'POST'])
        );
        $this->assertSame(
            'http://seznam.cz/product',
            $method->invokeArgs($service, [$urlWithoutEndSlash, $e, 'POST'])
        );
    }

    /**
     * @expectedException \Trinity\NotificationBundle\Exception\ClientException
     */
    public function testURLException()
    {
        $service = $this->container->get('trinity.notification.client_sender');
        $class = (get_class($service));
        $e = new Product();

        $method = self::getMethod($class, 'prepareURL');
        $method->invokeArgs($service, [null, $e, 'NoExistMethod']);
    }

    /**
     * @test
     */
    public function testSendEntity()
    {
        $service = $this->container->get('trinity.notification.client_sender');
        $entity = new Product();
        $res = $service->send($entity, 'PUT');

        $this->assertTrue(is_array($res));

        $res = $service->send($entity, 'GET');
        $this->assertTrue(is_string($res));
    }
}
