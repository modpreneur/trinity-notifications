<?php

namespace Trinity\NotificationBundle\Tests;

/**
 * Class ControllerTest.
 */
class ControllerTest extends BaseTest
{
    /**
     *  Controller.
     */
    public function testController()
    {
        $processor = $this->container->get('trinity.notification.utils');
        $controller = new Controllers\DisableController();

        $this->assertTrue($processor->isControllerOrActionDisabled($controller));
        $controller = new Controllers\ActiveController();

        $this->assertFalse($processor->isControllerOrActionDisabled($controller, 'disableNotificationAction'));
        $this->assertTrue($processor->isControllerOrActionDisabled($controller, 'activeNotificationAction'));
    }
}
