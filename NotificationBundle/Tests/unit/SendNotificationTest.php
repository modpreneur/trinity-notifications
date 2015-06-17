<?php

namespace Trinity\NotificationBundle\Tests\unit;

use Trinity\NotificationBundle\Services\NotificationManager;
use Trinity\NotificationBundle\Tests\entity\TestEntity;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;



class SendNotificationTest extends WebTestCase{

    /** @var  NotificationManager */
    protected $sender;



    /**
     * @test
     */
    public function testSendNotification(){
        $client = static::createClient();
        $sender = $client->getContainer()->get("necktie_notification.client_sender");
        $entity = new TestEntity();
        $this->assertStringStartsWith("ERROR", $sender->send($entity, "GET"));
    }

}