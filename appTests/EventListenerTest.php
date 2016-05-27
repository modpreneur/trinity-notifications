<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\AppTests;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\Tests\Common\Persistence\Mapping\TestClassMetadataFactory;
use Symfony\Component\HttpFoundation\Request;
use Trinity\NotificationBundle\AppTests\Entity\EntityInterfaceDisableClient;
use Trinity\NotificationBundle\AppTests\Sandbox\Entity\Product;


/**
 * Class EventListenerTest.
 */
class EventListenerTest extends BaseTest
{
    /**
     * @test
     */
    public function testPostUpdate()
    {
        $em = $this->getEM(true);
        $ev = $this->getContainer()->get('trinity.notification.entity_listener');

        $object = new Product();
        $args = new LifecycleEventArgs(
            $object, $em
        );

        $this->assertSame(
            [
                [
                    'code' => 200,
                    'statusCode' => 200,
                    'message' => 'OK',
                ],
            ],
            $ev->postUpdate($args)
        );

        $object = new EntityInterfaceDisableClient();
        $args = new LifecycleEventArgs(
            $object, $em
        );

        $this->assertArrayHasKey('error', $ev->postUpdate($args)[0]);

        $request = new Request();
        $request->query->add(
            ['_controller' => '\\Trinity\\NotificationBundle\\Tests\\Controllers\\ActiveController::disableNotification']
        );
        $ev->setRequest($request);
        $this->assertArrayHasKey('error', $ev->postUpdate($args)[0]);

        $this->setPropertyValue($ev, 'defaultValueForEnabledController', false);
        $this->assertArrayHasKey('error', $ev->postUpdate($args)[0]);
    }


    /**
     * @test
     */
    public function testPreRemove()
    {
        $em = $this->getEM(true);
        $ev = $this->getContainer()->get('trinity.notification.entity_listener');

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
        $em = $this->getEM(true);
        $ev = $this->getContainer()->get('trinity.notification.entity_listener');

        $object = new Product();
        $args = new LifecycleEventArgs(
            $object, $em
        );

        $object->setName('Name_' .rand(1, 9999));
        $result = $ev->postPersist($args);

        $this->assertEquals(
            [
                'code' => 200,
                'statusCode' => 200,
                'message' => 'OK',
            ],
            reset($result)
        );
    }


    /**
     * @test
     */
    public function testPreFlush()
    {
        $em = $this->getEM(true);
        $ev = $this->getContainer()->get('trinity.notification.entity_listener');

        $object = new Product();
        $refProp = new \ReflectionProperty($ev, 'entity');
        $refProp->setAccessible(true);
        $refProp->setValue($ev, $object);
        $args = new PreFlushEventArgs($em);

        $array = ($ev->preFlush($args)[0]);
        $refProp->setValue($ev, null);

        $this->assertArrayHasKey(
            'error',
            $array
        );
    }


    /**
     * @test
     */
    public function testSendNotification()
    {
        $em = $this->getEM(true);
        $ev = $this->getContainer()->get('trinity.notification.entity_listener');

        $sendNotification = $this->getMethod($ev, 'sendNotification');

        $entity = new Product();

        $this->assertEquals(false, $sendNotification->invokeArgs($ev, [$em, new \stdClass(), 'no-method']));
        $this->assertEquals(false, $sendNotification->invokeArgs($ev, [$em, new Product(), 'no-method']));

        $refPropConf = new \ReflectionProperty('\\Doctrine\\ORM\\EntityManager', 'config');
        $refPropConf->setAccessible(true);
        $refPropConf->setValue($em, new Configuration());

        $refPropConf = new \ReflectionProperty('\\Doctrine\\ORM\\EntityManager', 'metadataFactory');
        $refPropConf->setAccessible(true);

        $driver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver');
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $refPropConf->setValue($em, new  TestClassMetadataFactory($driver, $metadata));

        $refPropUnitOfWork = new \ReflectionProperty('\\Doctrine\\ORM\\EntityManager', 'unitOfWork');
        $refPropUnitOfWork->setAccessible(true);

        $unitOfWork = $this->getMock(
            '\\Doctrine\\ORM\\UnitOfWork',
            [],
            [$em]
        );

        $unitOfWork->expects($this->any())->method('getEntityChangeSet')->willReturn(
            ['name' => 'New Name', 'description' => 'New description.']
        );

        $refPropUnitOfWork->setValue($em, $unitOfWork);

        $this->assertEquals(
            [
                [
                    'code' => 200,
                    'statusCode' => 200,
                    'message' => 'OK',
                ],
            ],
            $sendNotification->invokeArgs($ev, [$em, $entity, 'POST'])
        );
    }
}
