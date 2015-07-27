<?php

namespace Trinity\NotificationBundle\Tests;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Symfony\Component\HttpFoundation\Request;
use Trinity\NotificationBundle\Tests\Entity\EntityDisableClient;
use Trinity\NotificationBundle\Tests\Entity\Product;



/**
 * Class EventListenerTest
 * @package Trinity\NotificationBundle\Tests
 */
class EventListenerTest extends BaseTest
{
    /**
     * @test
     */
    public function testPostUpdate()
    {

        $em = $this->getEM();
        $ev = $this->container->get("trinity.notification.entity_listener");

        $object = new Product();
        $args = new LifecycleEventArgs(
            $object, $em
        );

        $this->assertSame(
            [
                "code" => 200,
                "statusCode" => 200,
                "message" => "OK",
            ],
            $ev->postUpdate($args)
        );


        $object = new EntityDisableClient();
        $args = new LifecycleEventArgs(
            $object, $em
        );

        $this->assertEmpty($ev->postUpdate($args));

        $request = new Request();
        $request->query->add(
            ["_controller" => "\\Trinity\\NotificationBundle\\Tests\\Controllers\\ActiveController::disableNotification"]
        );
        $ev->setRequest($request);

        $this->assertEmpty($ev->postUpdate($args));
    }



    /**
     * @test
     */
    public function testPreRemove()
    {
        $em = $this->getEM();
        $ev = $this->container->get("trinity.notification.entity_listener");

        $object = new Product();
        $args = new LifecycleEventArgs(
            $object, $em
        );

        $ev->preRemove($args);
    }



    /**
     * @test
     */
    public function testPostPersist()
    {
        $em = $this->getEM();
        $ev = $this->container->get("trinity.notification.entity_listener");

        $object = new Product();
        $args = new LifecycleEventArgs(
            $object, $em
        );

        $result = $ev->postPersist($args);
        $this->assertContains("ERROR", $result);
    }



    /**
     * @test
     */
    public function testPreFlush()
    {
        $em = $this->getEM();
        $ev = $this->container->get("trinity.notification.entity_listener");

        $object = new Product();

        $refProp = new \ReflectionProperty($ev, 'entity');
        $refProp->setAccessible(true);
        $refProp->setValue($ev, $object);

        $args = new PreFlushEventArgs(
            $em
        );

        $this->assertSame(
            [
                "code" => 200,
                "statusCode" => 200,
                "message" => "OK",
            ],
            $ev->preFlush($args)
        );

        $refProp->setValue($ev, null);

        $this->assertSame(
            null,
            $ev->preFlush($args)
        );
    }



    /**
     * @test
     */
    public function testSendNotification()
    {
        $em = $this->getEM();
        $ev = $this->container->get("trinity.notification.entity_listener");

        $sendNotification = $this->getMethod($ev, "sendNotification");

        $entity = new Product();

        $this->assertEquals(false, $sendNotification->invokeArgs($ev, [$em, new \stdClass(), "no-method"]));
        $this->assertEquals(false, $sendNotification->invokeArgs($ev, [$em, new Product(), "no-method"]));


        $refPropConf = new \ReflectionProperty("\\Doctrine\\ORM\\EntityManager", "config");
        $refPropConf->setAccessible(true);
        $refPropConf->setValue($em, new Configuration());

        $refPropUnitOfWork = new \ReflectionProperty("\\Doctrine\\ORM\\EntityManager", "unitOfWork");
        $refPropUnitOfWork->setAccessible(true);

        $unitOfWork = $this->getMock(
            "\\Doctrine\\ORM\\UnitOfWork",
            [],
            [$em]
        );

        $unitOfWork->expects($this->any())->method('getEntityChangeSet')->willReturn(
                ['name' => 'New Name', 'description' => 'New description.']
            );

        $refPropUnitOfWork->setValue($em, $unitOfWork);
        $this->assertEquals(
            [
                "code" => 200,
                "statusCode" => 200,
                "message" => "OK",

            ],
            $sendNotification->invokeArgs($ev, [$em, $entity, "POST"])
        );
    }
}


