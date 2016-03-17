<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Tests;

use Trinity\NotificationBundle\Drivers\ApiDriver\ApiDriver;
use Trinity\NotificationBundle\Tests\Sandbox\Entity\Product;


/**
 * Class ApiDriverTest.
 */
class ApiDriverTest extends BaseTest
{
    /**
     * @test
     */
    public function testJsonEncodeObject()
    {
        $product = new Product();
        $id = $product->getId();
        $driver = $driver = $this->getDriver();

        $method = $this->getMethod($driver, 'JSONEncodeObject');
        $name = $product->getName();

        $expected = "{\"id\":$id,\"name\":\"$name\",\"";
        $result = $method->invokeArgs($driver, [$product, 'KJHGHJKKJHJKJHJH']);

        $this->assertStringStartsWith($expected, $result);

        $this->assertContains('"hash":', $result);
        $this->assertContains('"timestamp":', $result);
    }


    /**
     * @return ApiDriver
     */
    private function getDriver()
    {
        $driver = new ApiDriver(
            $this->getContainer()->get('event_dispatcher'),
            $this->getContainer()->get('trinity.notification.entity_converter'),
            $this->getContainer()->get('trinity.notification.utils')
        );

        return $driver;
    }


    /**
     * @test
     */
    public function testPrepareURLs()
    {
        $driver = $driver = $this->getDriver();

        $method = $this->getMethod($driver, 'prepareURL');

        $expected = 'http://example.com/product';
        $result = ($method->invokeArgs($driver, ['http://example.com', new Product(), 'POST']));
        $this->assertEquals($expected, $result);

        $expected = 'http://example.com/product';
        $result = ($method->invokeArgs($driver, ['http://example.com/', new Product(), 'POST']));
        $this->assertEquals($expected, $result);
    }


    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function testCreateJSONRequestError()
    {
        $driver = $this->getDriver();

        $method = $this->getMethod($driver, 'JSONEncodeObject');

        $data = $method->invokeArgs($driver, [new Product(), 'KJHGHJKKJHJKJHJH']);

        $method = $this->getMethod($driver, 'createRequest');
        $result = $method->invokeArgs($driver, [$data, 'http://example.com/product', 'POST', true]);
    }


    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function testCreateJSONRequestError2()
    {
        $driver = $this->getDriver();

        $method = $this->getMethod($driver, 'JSONEncodeObject');

        $data = $method->invokeArgs($driver, [new Product(), 'KJHGHJKKJHJKJHJH']);

        $method = $this->getMethod($driver, 'createRequest');
        $result = $method->invokeArgs($driver, [$data, 'http://example.com/product', 'POST', false]);
    }


    /**
     * @expectedException \Trinity\NotificationBundle\Exception\MethodException
     */
    public function testPrepareURLsError()
    {
        $driver = $this->getDriver();
        $method = $this->getMethod($driver, 'prepareURL');
        $method->invokeArgs($driver, ['http://example.com', new \stdClass(), 'POST']);
    }


    /**
     * @expectedException \Trinity\NotificationBundle\Exception\ClientException
     */
    public function testPrepareURLsClientError()
    {
        $driver = $this->getDriver();
        $method = $this->getMethod($driver, 'prepareURL');
        $method->invokeArgs($driver, [null, new Product(), 'POST']);
    }
}
