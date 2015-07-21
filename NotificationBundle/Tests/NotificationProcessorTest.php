<?php

    namespace Trinity\NotificationBundle\Tests;


    use Trinity\NotificationBundle\Notification\Annotations\NotificationProcessor;
    use Trinity\NotificationBundle\Tests\Entity\EEntity;
    use Trinity\NotificationBundle\Tests\Entity\Product;



    class NotificationProcessorTest extends BaseTest{


        public function testCheckIsObjectEntity()
        {
            $processor = $this->container->get("trinity.notification.processor");

            $this->assertTrue($processor->isNotificationEntity(new Product()));
            $this->assertFalse($processor->isNotificationEntity(new \stdClass()));
        }



        /**
         * @throws \Trinity\NotificationBundle\Exception\SourceException
         *
         * @expectedException \Trinity\NotificationBundle\Exception\SourceException
         */
        public function testClassAnnotationSource(){
            $processor = $this->container->get("trinity.notification.processor");

            $this->assertNotEmpty($processor->getClassSourceAnnotation(new Product()));

            // Errors
            $this->assertNotEmpty($processor->getClassSourceAnnotation(new \stdClass()));
        }


        public function testHasSource(){
            $processor = $this->container->get("trinity.notification.processor");

            $this->assertTrue($processor->hasSource(new Product(), 'id'));
            $this->assertTrue($processor->hasSource(new Product(), 'name'));
            $this->assertTrue($processor->hasSource(new Product(), 'description'));

            $this->assertFalse($processor->hasSource(new Product(), 'blah'));
        }


        public function testHTTPMethodForEntity(){
            $processor = $this->container->get("trinity.notification.processor");

            $this->assertTrue($processor->hasHTTPMethod(new Product(), "PUT"));
            $this->assertTrue($processor->hasHTTPMethod(new Product(), "DeleTE"));
            $this->assertFalse($processor->hasHTTPMethod(new Product(), "Blah"));

            $this->assertTrue($processor->hasHTTPMethod(new \stdClass(), "Blah"));
        }


        public function testClassAnnotations(){
            $processor = $this->container->get("trinity.notification.processor");

            $class = NotificationProcessor::ANNOTATION_CLASS;
            $this->assertTrue( ($processor->getClassAnnotations(new Product(), NotificationProcessor::ANNOTATION_CLASS)[0] instanceof $class ) );
        }


        public function testURLPostfix(){
            $processor = $this->container->get("trinity.notification.processor");

            $this->assertEquals('std-class', $processor->getUrlPostfix(new \stdClass(), 'DELETE'));
            $this->assertEquals('product', $processor->getUrlPostfix(new Product()));

            $this->assertEquals( 'no-name-e-entity', $processor->getUrlPostfix(new EEntity()) );
            $this->assertEquals( 'put-e-entity', $processor->getUrlPostfix(new EEntity(), 'put') );
            $this->assertEquals( 'delete-e-entity', $processor->getUrlPostfix(new EEntity(), 'delete') );
            $this->assertEquals( 'post-e-entity', $processor->getUrlPostfix(new EEntity(), 'post') );
        }
    }