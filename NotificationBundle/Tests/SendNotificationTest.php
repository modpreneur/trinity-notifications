<?php
namespace Trinity\NotificationBundle\Tests;

use Symfony\Component\DependencyInjection\Container;
use Braincrafted\Bundle\TestingBundle\Test\WebTestCase;

/**
 * Class SendNotificationTest
 * @package Trinity\NotificationBundle\Tests
 *
 *
 * For (http://api.dev.clickandcoach.com/)
 *
 */
class SendNotificationTest extends WebTestCase{

	/** @var  Container */
	private $container;


	public function setUp()
	{
		$this->setUpKernel();
        $this->container = $this->getContainer();
	}


	public function testUrl(){

    }

	public function testServiceIsDefinedInContainer()
	{
		$service = $this->container->get('trinity.notification.client_sender');
		$entity = new Product();
		$res = $service->send($entity, "PUT");
        $this->assertTrue(is_array($res));

        $res = $service->send($entity, "GET");
        $this->assertTrue(is_string($res));
	}

    public function tearDown()
    {
        $this->tearDownKernel();
    }

}