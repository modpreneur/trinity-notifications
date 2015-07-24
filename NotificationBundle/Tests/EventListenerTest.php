<?php

namespace Trinity\NotificationBundle\Tests;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\UnitOfWork;
use Trinity\NotificationBundle\Tests\Entity\EntityDisableClient;
use Trinity\NotificationBundle\Tests\Entity\EntityWithoutClient;
use Trinity\NotificationBundle\Tests\Entity\Product;



/**
 * Class EventListenerTest
 * @package Trinity\NotificationBundle\Tests
 */
class EventListenerTest extends BaseTest
{

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEM(){
        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('getRepository'))
            ->disableOriginalConstructor()
            ->getMock();

        return $em;
    }


    /**
     * @test
     */
    public function testPostUpdate(){

        $em = $this->getEM();
        $ev = $this->container->get("trinity.notification.entity_listener");

        $object = new Product();
        $args = new LifecycleEventArgs(
            $object,
            $em
        );

        $this->assertSame(
            [
                "code" => 200,
                "statusCode" => 200,
                "message" => "OK"
            ],
            $ev->postUpdate($args)
        );

        $object = new EntityDisableClient();
        $args = new LifecycleEventArgs(
            $object,
            $em
        );

        $this->assertEmpty($ev->postUpdate($args));
    }



    /**
     * @test
     */
    public function testPreRemove(){
        $em = $this->getEM();
        $ev = $this->container->get("trinity.notification.entity_listener");

        $object = new Product();
        $args = new LifecycleEventArgs(
            $object,
            $em
        );

        $ev->preRemove($args);
    }


    /**
     * @test
     */
    public function testPostPersist(){
        $em = $this->getEM();
        $ev = $this->container->get("trinity.notification.entity_listener");

        $object = new Product();
        $args = new LifecycleEventArgs(
            $object,
            $em
        );

        $result = $ev->postPersist($args);
        $this->assertContains("ERROR", $result);
    }

}


