<?php

namespace Trinity\NotificationBundle\Tests;

use Trinity\NotificationBundle\Event\SendEvent;
use Trinity\NotificationBundle\Event\StatusEvent;
use Trinity\NotificationBundle\Tests\Entity\Client;
use Trinity\NotificationBundle\Tests\Entity\Product;



/**
 * Class EventTest
 * @package Trinity\NotificationBundle\Tests
 */
class EventTest extends BaseTest
{

    /**
     * @test
     */
    public function testCreateStatusEvent(){

        $client = new Client();
        $client->setEnableNotification(true);

        $exception  = new \Exception("Test Exception");
        $method     = "method";
        $url        = "http://example.com/method";
        $json       = "{json}";
        $entityName = "Entity";
        $entityId   = 1;

        $event = new StatusEvent(
            $client,
            $entityName,
            $entityId,
            $url,
            $json,
            $method,
            $exception,
            NULL
        );

        $this->assertEquals("Test Exception", $event->getMessage());

        $event = new StatusEvent(
            $client,
            $entityName,
            $entityId,
            $url,
            $json,
            $method,
            $exception,
            "Error Message"
        );

        $this->assertEquals("Error Message", $event->getMessage());
        $this->assertEquals($entityName, $event->getEntityName());
        $this->assertEquals($url, $event->getUrl());
        $this->assertEquals($client, $event->getClient());
        $this->assertEquals($json, $event->getJson());
        $this->assertEquals($method, $event->getMethod());
        $this->assertEquals($exception, $event->getException());

        $event->setEntityId(1);
        $this->assertEquals(1, $event->getEntityId());
        $this->assertTrue( $event->hasError() );

        $event = new StatusEvent(
            $client,
            $entityName,
            $entityId,
            $url,
            $json,
            $method,
            NULL,
            NULL
        );

        $this->assertFalse($event->hasError());
        $this->assertNull($event->getMessage());
    }



    /**
     * @test
     */
    public function testCreateSendEvent(){
        $entity = new Product();
        $event = new SendEvent($entity);
        $this->assertEquals($entity, $event->getEntity());
    }

}