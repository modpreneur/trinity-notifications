<?php

namespace Trinity\NotificationBundle\AppTests;

use Trinity\NotificationBundle\Notification\AnnotationsUtils;
use Trinity\NotificationBundle\AppTests\Entity\AllSourceEntityInterface;
use Trinity\NotificationBundle\AppTests\Entity\EEntityInterface;
use Trinity\NotificationBundle\AppTests\Entity\EntityInterfaceErrorArray;
use Trinity\NotificationBundle\AppTests\Entity\EntityInterfaceMethodDate;
use Trinity\NotificationBundle\AppTests\Entity\EntityInterfaceWithoutSource;
use Trinity\NotificationBundle\AppTests\Sandbox\Entity\Product;


/**
 * Class NotificationUtilsTest.
 */
class NotificationUtilsTest extends BaseTest
{
    /**
     * @test
     */
    public function testCheckIsObjectEntity()
    {
        $utils = $this->getContainer()->get('trinity.notification.utils');

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
        $utils = $this->getContainer()->get('trinity.notification.annotations.utils');

        $this->assertNotEmpty($utils->getClassSourceAnnotation(new Product()));

        // Errors
        $this->assertNotEmpty($utils->getClassSourceAnnotation(new \stdClass()));
    }


    /**
     * @test
     */
    public function testHasSource()
    {
        $utils = $this->getContainer()->get('trinity.notification.utils');

        $this->assertTrue($utils->hasSource(new Product(), 'id'));
        $this->assertTrue($utils->hasSource(new Product(), 'name'));
        $this->assertTrue($utils->hasSource(new Product(), 'description'));

        $this->assertFalse($utils->hasSource(new Product(), 'blah'));
    }


    /**
     * @test
     */
    public function testHasDependedSource()
    {
        $utils = $this->getContainer()->get('trinity.notification.utils');

        $this->assertTrue($utils->hasDependedSource(new Product(), 'id'));
        $this->assertFalse($utils->hasDependedSource(new Product(), 'name'));

        $this->assertFalse($utils->hasDependedSource(new Product(), 'blah'));

        $this->assertFalse($utils->hasDependedSource(new EEntityInterface(), 'blah'));
    }


    /**
     * @test
     */
    public function testHTTPMethodForEntity()
    {
        $utils = $this->getContainer()->get('trinity.notification.utils');

        $this->assertTrue($utils->hasHTTPMethod(new Product(), 'PUT'));
        $this->assertTrue($utils->hasHTTPMethod(new Product(), 'DeleTE'));
        $this->assertFalse($utils->hasHTTPMethod(new Product(), 'Blah'));

        $this->assertTrue($utils->hasHTTPMethod(new \stdClass(), 'Blah'));
    }


    /**
     * @test
     */
    public function testClassAnnotations()
    {
        $utils = $this->getContainer()->get('trinity.notification.annotations.utils');

        $class = AnnotationsUtils::ANNOTATION_CLASS;
        $this->assertTrue(
            ($utils->getClassAnnotations(new Product(), AnnotationsUtils::ANNOTATION_CLASS)[0] instanceof $class)
        );
    }


    /**
     * @test
     */
    public function testURLPostfix()
    {
        $utils = $this->getContainer()->get('trinity.notification.utils');

        $this->assertEquals('std-class', $utils->getUrlPostfix(new \stdClass(), 'DELETE'));
        $this->assertEquals('product', $utils->getUrlPostfix(new Product()));

        $this->assertEquals('no-name-e-entity', $utils->getUrlPostfix(new EEntityInterface()));
        $this->assertEquals('put-e-entity', $utils->getUrlPostfix(new EEntityInterface(), 'put'));
        $this->assertEquals('delete-e-entity', $utils->getUrlPostfix(new EEntityInterface(), 'delete'));
        $this->assertEquals('post-e-entity', $utils->getUrlPostfix(new EEntityInterface(), 'post'));
    }


    /**
     * @throws \Trinity\NotificationBundle\Exception\MethodException
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     *
     * @expectedException \Trinity\NotificationBundle\Exception\SourceException
     */
    public function testEntityToArrayError()
    {
        $utils = $this->getContainer()->get('trinity.notification.entity_converter');

        // Error
        $utils->toArray(new EntityInterfaceWithoutSource());
    }


    /**
     * Notification\Source(columns="*").
     *
     * @throws \Trinity\NotificationBundle\Exception\MethodException
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     */
    public function testEntityToArrayAllSource()
    {
        $utils = $this->getContainer()->get('trinity.notification.entity_converter');

        $allSourceEntity = new AllSourceEntityInterface();
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
        $utils = $this->getContainer()->get('trinity.notification.entity_converter');
        $class = (get_class($utils));
        $method = $this->getMethod($class, 'processProperty');

        $p = new Product();
        $propertyArray = $method->invokeArgs($utils, [$p, 'id', 'getId']);

        //$this->assertEquals(['id' => 1], $propertyArray);
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
            'name' => $p->getName(),
            'description' => $p->getDescription()
        ];

        $arrayA = $utils->toArray($p);
        unset($arrayA['id']);
        $this->assertEquals($sourceArrayA, $arrayA);

        $p = new EEntityInterface();

        $sourceEE = [
            'name' => 'EE Entity',
            'description' => 'Description for entity.',
            'date' => '2010-11-12 00:00:00',
            'fullPrice' => '10$',
            'test-method' => 'test',
        ];

        $arrayEE = $utils->toArray($p);
        unset($arrayEE['id']);

        $this->assertEquals($sourceEE, $arrayEE);

        $errorEntity = new EntityInterfaceErrorArray();
        $utils->toArray($errorEntity);
    }


    /**
     * @test
     */
    public function testEntityToArrayMethodDate()
    {
        $utils = $this->getContainer()->get('trinity.notification.entity_converter');
        $class = (get_class($utils));
        $method = $this->getMethod($class, 'processMethod');

        $entity = new EntityInterfaceMethodDate();

        $array = $method->invokeArgs($utils, [$entity, 'date', 'getDate']);

        $this->assertEquals(['date' => '2010-11-12 00:00:00'], $array);
    }

}
