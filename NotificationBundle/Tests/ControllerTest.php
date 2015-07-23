<?php

    namespace Trinity\NotificationBundle\Tests;


    use ReflectionClass;



    class ControllerTest extends BaseTest
    {

        public function testController(){
            $processor = $this->container->get("trinity.notification.utils");
            $controller = new Controllers\DisableController();

            $this->assertTrue($processor->isControllerOrActionDisabled($controller));
            $controller = new Controllers\ActiveController();

            $this->assertTrue($processor->isControllerOrActionDisabled($controller, "disableNotificationAction"));
            $this->assertFalse($processor->isControllerOrActionDisabled($controller, "activeNotificationAction"));
        }
    }