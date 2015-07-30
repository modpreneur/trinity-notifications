<?php

namespace Trinity\NotificationBundle\Tests;

use Trinity\NotificationBundle\Tests\Entity\EntityDisableClient;
use Trinity\NotificationBundle\Tests\Entity\EntityWithoutClient;



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
        $manager = $this->container->get('trinity.notification.manager');

        $entity = new EntityWithoutClient();

        $result = $manager->send($entity);
        $this->assertEmpty($result);
    }



    /**
     * @test
     */
    public function testSendWithDisableClient()
    {
        $manager = $this->container->get('trinity.notification.manager');
        $entity = new EntityDisableClient();
        $result = $manager->send($entity);

        $this->assertEmpty($result);
    }



    /**
     * @test
     */
    public function testSetDriver()
    {
        $manager = $this->container->get('trinity.notification.manager');
        $method = $this->getMethod($manager, "setDriver");

        $result = $method->invokeArgs($manager, []);

        $result = $method->invokeArgs($manager, [$this->container->get("trinity.notification.driver.api")]);
        $this->assertInstanceOf("\\Trinity\\NotificationBundle\\Driver\\ApiDriver", $manager->getDriver());

    }



    /**
     * @test
     * @expectedException \Trinity\NotificationBundle\Exception\NotificationDriverException
     * @expectedExceptionMessage Driver no_exists_driver not found.
     */
    public function testSetDriverWrongDriver()
    {
        $manager = $this->container->get('trinity.notification.manager');
        $method = $this->getMethod($manager, "setDriver");

        $this->setPropertyValue($manager, "driver", null);
        $this->setPropertyValue($manager, "driverName", "no_exists_driver");

        $result = $method->invokeArgs($manager, []);
    }



    /**
     * @test
     * @expectedException \Trinity\NotificationBundle\Exception\NotificationDriverException
     * @expectedExceptionMessage Notification driver is probably not set.
     */
    public function testSetDriverNoDriver()
    {
        $manager = $this->container->get('trinity.notification.manager');
        $method = $this->getMethod($manager, "setDriver");

        $this->setPropertyValue($manager, "driver", null);
        $this->setPropertyValue($manager, "drivers", []);
        $this->setPropertyValue($manager, "driverName", null);

        $result = $method->invokeArgs($manager, []);
    }

}
