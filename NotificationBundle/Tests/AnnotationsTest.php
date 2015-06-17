<?php

namespace Trinity\NotificationBundle\Tests\Controller;

use Doctrine\Common\Annotations\AnnotationReader;
use Trinity\FrameworkBundle\Notification\Annotations\NotificationProcessor;
use Trinity\FrameworkBundle\Notification\Annotations\Url;
use Trinity\NotificationBundle\Entity\AllAndMethodsProduct;
use Trinity\NotificationBundle\Entity\AllSourceOfProduct;
use Trinity\NotificationBundle\Entity\SpecificColumnOfProduct;
use Trinity\NotificationBundle\Entity\WithoutSourceProduct;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;



require __DIR__ . DIRECTORY_SEPARATOR . "Entity/AllSourceOfProduct.php";
require __DIR__ . DIRECTORY_SEPARATOR . "Entity/SpecificColumnOfProduct.php";
require __DIR__ . DIRECTORY_SEPARATOR . "Entity/AllAndMethodsProduct.php";
require __DIR__ . DIRECTORY_SEPARATOR . "Entity/WithoutSourceProduct.php";

/**
 * Class AnnotationsTest
 * @author Tomáš Jančar
 * @package Trinity\NotificationBundle\Tests\Controller
 */
class AnnotationsTest extends WebTestCase{


    const ANNOTATION_CLASS = "\\Necktie\\NotificationBundle\\Annotations\\Source";

    const ANNOTATION_METHODS_CLASS = "\\Necktie\\NotificationBundle\\Annotations\\Methods";

    const ANNOTATION_URL_CLASS = "\\Necktie\\NotificationBundle\\Annotations\\URL";

    /** @var  AnnotationReader */
    protected $reader;

    protected $processor;


    public function testUrl(){
        $entity    = new AllAndMethodsProduct();

        /** @var Url $events */
        $urlPostfix       = $this->processor->getUrlPostfix($entity, "post");
        $this->assertEquals("productPost", $urlPostfix);

        /** @var Url $events */
        $urlPostfix       = $this->processor->getUrlPostfix($entity, "put");
        $this->assertEquals("productPut", $urlPostfix);

        /** @var Url $events */
        $urlPostfix       = $this->processor->getUrlPostfix($entity, "delete");
        $this->assertEquals("productDelete", $urlPostfix);


        $urlPostfix       = $this->processor->getUrlPostfix($entity, "dafaq");
        $this->assertEquals("all-and-methods-product", $urlPostfix);

        $entity    = new WithoutSourceProduct();

        /** @var Url $events */
        $urlPostfix       = $this->processor->getUrlPostfix($entity);
        $this->assertEquals("without-source-product", $urlPostfix);


        $entity    = new AllSourceOfProduct();

        /** @var Url $events */
        $urlPostfix       = $this->processor->getUrlPostfix($entity);
        $this->assertEquals("all-source-url", $urlPostfix);

        $entity = new SpecificColumnOfProduct();
        $urlPostfix       = $this->processor->getUrlPostfix($entity, "post");
        $this->assertEquals("post-put", $urlPostfix);

        $urlPostfix       = $this->processor->getUrlPostfix($entity, "put");
        $this->assertEquals("post-put", $urlPostfix);

        $urlPostfix       = $this->processor->getUrlPostfix($entity, "delete");
        $this->assertEquals("delete", $urlPostfix);
    }



    public function testEventsTypes()
    {
        $entity    = new AllAndMethodsProduct();
        $processor = new NotificationProcessor(new AnnotationReader());
        $events = $processor->getEntityAnnotation($entity, self::ANNOTATION_METHODS_CLASS);
        $types     = ["post", "put"];

        $this->assertEquals($types, $events->getTypes());

        $this->assertTrue($events->hasType("put"));
        $this->assertFalse($events->hasType("delete"));
        $this->assertTrue($events->hasType("POST"));

        $this->assertTrue($processor->isMethodEnabled($entity, "put"));

        // without events definitions
        $entity    = new WithoutSourceProduct();
        $processor = new NotificationProcessor(new AnnotationReader());
        $events = $processor->getEntityAnnotation($entity, self::ANNOTATION_METHODS_CLASS);
        $this->assertNull($events);

        $this->assertTrue($processor->isMethodEnabled($entity, "put"));
    }



    public function testConvertObject()
    {
        //  @DisableNotification\Source(columns="*")
        $entity  = new AllSourceOfProduct();
        $entity->setName("Mimon");
        $entity->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit.");
        $entity->setType("video");


        $premiseArray = [];
        $premiseArray["ID"]           = NULL;
        $premiseArray["name"]         = "Mimon";
        $premiseArray["description"]  = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.";
        $premiseArray["type"]         = "video";
        $premiseArray["client"]       = NULL;
        $premiseArray["billingPlan"]  = NULL;


        $reader  = new AnnotationReader();
        $convert = new NotificationProcessor($reader);
        $array   = $convert->convertJson($entity);
        $this->assertEquals($premiseArray, $array);
    }



    public function testConvertObjectWithSpecificColumn(){
        // @DisableNotification\Source(columns="id, name, description")
        $entity = new SpecificColumnOfProduct();

        $entity->setName("Mimon");
        $entity->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit.");
        $entity->setType("video");


        $premiseArray = [];
        $premiseArray["id"]           = NULL;
        $premiseArray["name"]         = "Mimon";

        // method
        $premiseArray["price"]        = 100;

        $premiseArray["description"]  = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.";

        $reader  = new AnnotationReader();
        $convert = new NotificationProcessor($reader);

        $array = $convert->convertJson($entity);
        $this->assertEquals($premiseArray, $array);
    }



    public function testAllAndMethods(){
        // @DisableNotification\Source(columns="*, price")
        $entity = new AllAndMethodsProduct();

        $entity->setName("Mimon");
        $entity->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit.");
        $entity->setType("video");


        $premiseArray = [];
        $premiseArray["id"]           = NULL;
        $premiseArray["name"]         = "Mimon";
        $premiseArray["description"]  = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.";
        $premiseArray["type"]         = "video";
        $premiseArray["client"]       = NULL;
        $premiseArray["billingPlan"]  = NULL;

        // method
        $premiseArray["productPrice"]  = 100;

        $reader  = new AnnotationReader();
        $convert = new NotificationProcessor($reader);

        $array = $convert->convertJson($entity);
        $this->assertEquals($premiseArray, $array);
    }



    /**
     * @expectedException \Trinity\NotificationBundle\Exception\SourceException
     */
    public function testWithoutSource()
    {
        $entity = new WithoutSourceProduct();
        $reader  = new AnnotationReader();
        $convert = new NotificationProcessor($reader);

        $array = $convert->convertJson($entity);
    }



    /**
     * DisableNotification\Source(columns="*, price")
     * isAllColumnsSelected()
     */
    public function testAsteriskInSourceReturnTrue(){
        $entity = new AllAndMethodsProduct();
        $class = get_class($entity);
        $annotationReader = new AnnotationReader();
        $reflectionObject = new \ReflectionClass($class);
        $classSourceAnnotation  = $annotationReader->getClassAnnotation($reflectionObject, self::ANNOTATION_CLASS);

        $this->assertTrue($classSourceAnnotation->isAllColumnsSelected());
    }



    protected function setUp()
    {
        $this->reader    = new AnnotationReader();
        $this->processor = new NotificationProcessor($this->reader);
    }


}