<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Tests;

use Trinity\NotificationBundle\Driver\ApiDriver;
use Trinity\NotificationBundle\Tests\Entity\Product;

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
        $driver = $driver = $this->getDriver();

        $method = $this->getMethod($driver, 'JSONEncodeObject');

        $expected = "{\"id\":1,\"name\":\"Someone's name\",\"description\":\"Lorem impsu\"";
        $result = $method->invokeArgs($driver, [new Product(), 'KJHGHJKKJHJKJHJH']);

        //dump($result);

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
            $this->getContainer()->get('trinity.notification.entityConverter'),
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
     *
//     * guzzleHttp exception v6.0
     * @expectedExceptionMessage Client error: 404
     *
//     * guzzleHttp exception v5.3
//     * @expectedExceptionMessage Client error response [url] http://example.com/product [status code] 404 [reason phrase] Not Found
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
     *
//     * guzzleHttp exception v6.0
     * @expectedExceptionMessage Client error: 404
     *
//     * guzzleHttp exception v5.3
//     * @expectedExceptionMessage Client error response [url] http://example.com/product [status code] 404 [reason phrase] Not Found
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
