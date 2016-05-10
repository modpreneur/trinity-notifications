<?php

namespace Trinity\NotificationBundle\AppTests;

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
        $processor = $this->getContainer()->get('trinity.notification.utils');
        $controller = new Controllers\DisableController();

        $this->assertTrue($processor->isControllerOrActionDisabled($controller));
        $controller = new Controllers\ActiveController();

        $this->assertTrue($processor->isControllerOrActionDisabled($controller, 'disableNotificationAction'));
        $this->assertFalse($processor->isControllerOrActionDisabled($controller, 'activeNotificationAction'));
    }
}
