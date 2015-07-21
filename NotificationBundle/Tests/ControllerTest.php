<?php

    namespace Trinity\NotificationBundle\Tests;


    class ControllerTest extends BaseTest
    {

        public function testController(){
            $processor = $this->container->get("trinity.notification.processor");
            $controller = new Controller\DisableController();

            $this->assertTrue($processor->isControllerOrActionDisabled($controller));


            $controller = new Controller\ActiveController();

            $this->assertTrue($processor->isControllerOrActionDisabled($controller, "disableNotificationAction"));
            $this->assertFalse($processor->isControllerOrActionDisabled($controller, "activeNotificationAction"));
        }

    }