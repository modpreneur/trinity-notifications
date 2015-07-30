<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;



/**
 * Class BaseTest.
 */
abstract class BaseTest extends WebTestCase
{
    /** @var  Container */
    protected $container;

    /** @var  KernelInterface */
    protected $kernelObject;

    /** @var  Client */
    protected $clientObject;



    /**
     * Create kernel.
     */
    public function setUp()
    {
        $this->kernelObject = self::createKernel();
        $this->kernelObject->boot();
        $this->container = $this->getContainer();

        $this->clientObject = self::createClient();
    }



    /**
     * Shutdown kernel.
     */
    public function tearDown()
    {
        $this->kernelObject->shutdown();
    }



    /**
     * @param string|object $class
     * @param string $name
     *
     * @return \ReflectionMethod
     */
    protected static function getMethod($class, $name)
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }



    /**
     * @param object $class
     * @param string $property
     * @param $value
     */
    protected function setPropertyValue($class, $property, $value)
    {
        $property = new \ReflectionProperty($class, $property);
        $property->setAccessible(true);
        $property->setValue($class, $value);
    }



    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEM()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->setMethods(
            ['getRepository']
        )->disableOriginalConstructor()->getMock();

        return $em;
    }
}
