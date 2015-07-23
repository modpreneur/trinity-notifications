<?php

namespace Trinity\NotificationBundle\Tests;

use Trinity\NotificationBundle\Notification\AnnotationsUtils;
use Trinity\NotificationBundle\Tests\Entity\AllSourceEntity;
use Trinity\NotificationBundle\Tests\Entity\EEntity;
use Trinity\NotificationBundle\Tests\Entity\EntityErrorArray;
use Trinity\NotificationBundle\Tests\Entity\EntityMethodDate;
use Trinity\NotificationBundle\Tests\Entity\EntityWithoutSource;
use Trinity\NotificationBundle\Tests\Entity\Product;



class NotificationUtilsTest extends BaseTest
{


    public function testCheckIsObjectEntity()
    {
        $utils = $this->container->get("trinity.notification.utils");

        $this->assertTrue($utils->isNotificationEntity(new Product()));
        $this->assertFalse($utils->isNotificationEntity(new \stdClass()));
    }



    /**
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     *
     * @expectedException \Trinity\NotificationBundle\Exception\SourceException
     */
    public function testClassAnnotationSource()
    {
        $utils = $this->container->get("trinity.notification.annotations.utils");

        $this->assertNotEmpty($utils->getClassSourceAnnotation(new Product()));

        // Errors
        $this->assertNotEmpty($utils->getClassSourceAnnotation(new \stdClass()));
    }



    public function testHasSource()
    {
        $utils = $this->container->get("trinity.notification.utils");

        $this->assertTrue($utils->hasSource(new Product(), 'id'));
        $this->assertTrue($utils->hasSource(new Product(), 'name'));
        $this->assertTrue($utils->hasSource(new Product(), 'description'));

        $this->assertFalse($utils->hasSource(new Product(), 'blah'));
    }



    public function testHTTPMethodForEntity()
    {
        $utils = $this->container->get("trinity.notification.utils");

        $this->assertTrue($utils->hasHTTPMethod(new Product(), "PUT"));
        $this->assertTrue($utils->hasHTTPMethod(new Product(), "DeleTE"));
        $this->assertFalse($utils->hasHTTPMethod(new Product(), "Blah"));

        $this->assertTrue($utils->hasHTTPMethod(new \stdClass(), "Blah"));
    }



    public function testClassAnnotations()
    {
        $utils = $this->container->get("trinity.notification.annotations.utils");

        $class = AnnotationsUtils::ANNOTATION_CLASS;
        $this->assertTrue(
            ($utils->getClassAnnotations(new Product(), AnnotationsUtils::ANNOTATION_CLASS)[0] instanceof $class)
        );
    }



    public function testURLPostfix()
    {
        $utils = $this->container->get("trinity.notification.utils");

        $this->assertEquals('std-class', $utils->getUrlPostfix(new \stdClass(), 'DELETE'));
        $this->assertEquals('product', $utils->getUrlPostfix(new Product()));

        $this->assertEquals('no-name-e-entity', $utils->getUrlPostfix(new EEntity()));
        $this->assertEquals('put-e-entity', $utils->getUrlPostfix(new EEntity(), 'put'));
        $this->assertEquals('delete-e-entity', $utils->getUrlPostfix(new EEntity(), 'delete'));
        $this->assertEquals('post-e-entity', $utils->getUrlPostfix(new EEntity(), 'post'));
    }



    /**
     * @throws \Trinity\NotificationBundle\Exception\MethodException
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     *
     * @expectedException \Trinity\NotificationBundle\Exception\SourceException
     */
    public function testEntityToArrayError()
    {
        $utils = $this->container->get("trinity.notification.entityConverter");

        // Error
        $utils->toArray(new EntityWithoutSource());
    }



    /**
     *
     * Notification\Source(columns="*")
     *
     * @throws \Trinity\NotificationBundle\Exception\MethodException
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     *
     */
    public function testEntityToArrayAllSource()
    {
        $utils = $this->container->get("trinity.notification.entityConverter");

        $allSourceEntity = new AllSourceEntity();
        $allSourceArrayExpected = [
            'id' => 1,
            'name' => 'All source',
            'description' => 'Description text.',
            'price' => '10$',
        ];

        $allSourceArrayResult = $utils->toArray($allSourceEntity);
        $this->assertEquals($allSourceArrayExpected, $allSourceArrayResult);
    }



    /**
     * @throws \Exception
     *
     * @expectedException \Exception
     *
     * @expectedExceptionMessage No method or property error
     */
    public function testEntityToArray()
    {
        $utils = $this->container->get("trinity.notification.entityConverter");
        $class = (get_class($utils));
        $method = $this->getMethod($class, "processProperty");

        $p = new Product();
        $propertyArray = $method->invokeArgs($utils, [$p, 'id', 'getId']);

        $this->assertEquals(['id' => 1], $propertyArray);
        $propertyArray = $method->invokeArgs(
            $utils,
            [
                $p,
                'id',
                'getNoExistsProperty',
            ]
        );

        $this->assertEquals(['id' => null], $propertyArray);

        $p = new Product();

        $sourceArrayA = [
            'id' => 1,
            'name' => "Someone's name",
            'description' => 'Lorem impsu',
        ];

        $arrayA = $utils->toArray($p);
        $this->assertEquals($sourceArrayA, $arrayA);

        $p = new EEntity();

        $sourceEE = [
            "id" => 1,
            "name" => "EE Entity",
            "description" => "Description for entity.",
            'date' => "2010-11-12 00:00:00",
            'fullPrice' => '10$',
            'test-method' => 'test'
        ];

        $arrayEE = $utils->toArray($p);
        $this->assertEquals($sourceEE, $arrayEE);


        $errorEntity = new EntityErrorArray();
        $utils->toArray($errorEntity);
    }


    public function testEntityToArrayMethodDate(){
        $utils = $this->container->get("trinity.notification.entityConverter");
        $class = (get_class($utils));
        $method = $this->getMethod($class, "processMethod");

        $entity = new EntityMethodDate();

        $array = $method->invokeArgs( $utils, [$entity, 'date', 'getDate'] );

        $this->assertEquals([ 'date' => '2010-11-12 00:00:00'], $array );
    }

    /*
    public function testEntityToArrayMethodDateError(){
        $utils = $this->container->get("trinity.notification.entityConverter");
        $class = (get_class($utils));
        $method = $this->getMethod($class, "processMethod");

        $entity = new EntityErrorArray();

        $array = $method->invokeArgs( $utils, [$entity, 'date', 'getDate'] );

        $this->assertEquals([ 'date' => '2010-11-12 00:00:00'], $array );
    }
    */
}