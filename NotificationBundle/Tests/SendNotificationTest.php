<?php
    namespace Trinity\NotificationBundle\Tests;

    use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
    use ReflectionClass;
    use Trinity\NotificationBundle\Tests\Entity\Product;



    /**
     * Class SendNotificationTest
     * @package Trinity\NotificationBundle\Tests
     *
     *
     * For (http://api.dev.clickandcoach.com/)
     *
     */
    class SendNotificationTest extends BaseTest
    {
        public function testEntityToArray()
        {
            $service = $this->container->get('trinity.notification.client_sender');
            $class = (get_class($service));
            $method = self::getMethod($class, "clientsToArray");
            $e = new Product();

            $array = $method->invokeArgs($service, [$e]);

            $this->assertTrue(is_array($array));
        }



        /**
         * @expectedException \Trinity\NotificationBundle\Exception\ClientException
         */
        public function testEntityToArrayError()
        {
            $service = $this->container->get('trinity.notification.client_sender');
            $class = (get_class($service));
            $method = self::getMethod($class, "clientsToArray");
            $e = new \stdClass();

            $arrayStdClass = $method->invokeArgs($service, [$e]);
        }



        public function testUrl()
        {
            $service = $this->container->get('trinity.notification.client_sender');
            $class = (get_class($service));
            $e = new Product();

            $urlWithEndSlash = "http://seznam.cz/";
            $urlWithoutEndSlash = "http://seznam.cz";

            $method = self::getMethod($class, "prepareURLs");
            $this->assertSame(
                "http://seznam.cz/product",
                $method->invokeArgs($service, [$urlWithEndSlash, $e, "POST"])
            );
            $this->assertSame(
                "http://seznam.cz/product",
                $method->invokeArgs($service, [$urlWithoutEndSlash, $e, "POST"])
            );
        }



        /**
         * @expectedException \Trinity\NotificationBundle\Exception\ClientException
         */
        public function testURLException()
        {

            $service = $this->container->get('trinity.notification.client_sender');
            $class = (get_class($service));
            $e = new Product();

            $method = self::getMethod($class, "prepareURLs");
            $method->invokeArgs($service, [null, $e, "NoExistMethod"]);
        }



        /**
         * @expectedException \Trinity\NotificationBundle\Exception\ClientException
         */
        public function testSendEntity()
        {
            $service = $this->container->get('trinity.notification.client_sender');
            $entity = new Product();
            $res = $service->send($entity, "PUT");
            $this->assertTrue(is_array($res));

            $res = $service->send($entity, "GET");
            $this->assertTrue(is_string($res));


            // Error
            $service->send(new \stdClass(), "Put");
        }



        protected static function getMethod($class, $name)
        {
            $class = new ReflectionClass($class);
            $method = $class->getMethod($name);
            $method->setAccessible(true);

            return $method;
        }
    }