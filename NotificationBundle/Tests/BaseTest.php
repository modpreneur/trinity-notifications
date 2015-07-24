<?php
namespace Trinity\NotificationBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;



abstract class BaseTest extends WebTestCase
{

    /** @var  Container */
    protected $container;

    /** @var  KernelInterface */
    protected $kernelObject;

    /** @var  Client */
    protected $clientObject;



    /**
     * Create kernel
     */
    public function setUp()
    {
        $this->kernelObject = self::createKernel();
        $this->kernelObject->boot();
        $this->container = $this->getContainer();

        $this->clientObject = self::createClient();
    }



    /**
     * shutdown kernel
     */
    public function tearDown()
    {
        $this->kernelObject->shutdown();
    }



    /**
     * @param string|object $class
     * @param string $name
     * @return \ReflectionMethod
     */
    protected static function getMethod($class, $name)
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}