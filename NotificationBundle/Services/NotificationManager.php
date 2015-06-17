<?php

    namespace Trinity\NotificationBundle\Services;


    use Doctrine\ORM\PersistentCollection;
    use GuzzleHttp\Client;
    use Symfony\Component\EventDispatcher\EventDispatcher;
    use Trinity\FrameworkBundle\Entity\IEntityNotification;
    use Trinity\FrameworkBundle\Notification\Annotations\NotificationProcessor;
    use Trinity\NotificationBundle\Event\Events;
    use Trinity\NotificationBundle\Event\SendEvent;
    use Trinity\NotificationBundle\Event\StatusEvent;
    use Trinity\NotificationBundle\Exception\ClientException;
    use Trinity\NotificationBundle\Exception\MethodException;
    use Nette\Utils\Strings;
    use Symfony\Component\HttpKernel\Exception\HttpException;



    class NotificationManager
    {
        const DELETE = 'DELETE';
        const POST = 'POST';
        const PUT = 'PUT';


        /** @var  NotificationProcessor */
        protected $processor;

        /** @var  EventDispatcher */
        protected $eventDispatcher;



        function __construct(
            $eventDispatcher,
            NotificationProcessor $annotationProcessor
        )
        {
            $this->eventDispatcher = $eventDispatcher;
            $this->processor = $annotationProcessor;
        }



        /**
         * @param IEntityNotification $entity
         * @param string $method
         * @return mixed|string|void
         * @throws ClientException
         * @throws MethodException
         */
        public function send($entity, $method)
        {
            $response = "";

            // before send
            $this->eventDispatcher->dispatch(
                Events::BEFORE_NOTIFICATION_SEND,
                new SendEvent($entity)
            );

            $clients = $this->prepareClients($entity);


            if (!$clients){
                return;
            }

            foreach ($clients as $client) {

                if(!$client->isNotificationEnabled()) continue;

                $url  = $this->prepareURLs($client->getNotifyUrl(), $entity, $method);
                $json = $this->json_encode_object($entity, $client->getSecret());

                try {
                    $response = $this->processNotification($json, $url, $method, true);

                    $this->eventDispatcher->dispatch(
                        Events::SUCCESS_NOTIFICATION,
                        new StatusEvent($client, $entity, $entity->getId(), $url, $json, $method, null, null)
                    );
                } catch (\Exception $ex) {
                    $message = "$method: URL: " . $url . " returns error: " . $ex->getMessage() . ".";
                    $this->eventDispatcher->dispatch(
                        Events::ERROR_NOTIFICATION,
                        new StatusEvent($client, $entity, $entity->getId(), $url, $json, $method, $ex, $message)
                    );

                    $response = "ERROR - $message";
                }
            }

            $this->eventDispatcher->dispatch(
                Events::AFTER_NOTIFICATION_SEND,
                new SendEvent($entity)
            );

            return $response;
        }



        /**
         * @param $entity
         * @return \Necktie\AppBundle\Entity\Client[]|NULL
         */
        protected function prepareClients(IEntityNotification $entity)
        {
            $clients = $entity->getClients();

            if (!$clients){
                return null;
            }

            elseif ($clients instanceof PersistentCollection) {
                $clients = $clients->toArray();
                return $clients;
            } elseif (!is_array($clients)) {
                $cl = $clients;
                $clients = [];
                $clients[] = $cl;
                return $clients;
            }

            return $clients;
        }



        /**
         * @param string $url
         * @param $entity
         * @param $method
         * @return array
         * @throws ClientException
         * @throws MethodException
         * @internal param $client
         * @internal param $entity
         * @internal param $method
         * @internal param $url
         */
        private function prepareURLs($url, $entity, $method)
        {
            $clientMethod = "getClients";
            if (!is_callable([$entity, $clientMethod])) {
                throw new MethodException("Method '$clientMethod' not exists in entity.");
            }

            if ($url == null || empty($url)) {
                throw new ClientException("Notification error: Client has not set notification URL. Please use IEntityNotification.");
            }

            $class = $this->processor->getUrlPostfix($entity, $method);
            // add / to url
            if (!Strings::endsWith($url, "/")) {
                $url .= "/";
            }

            return $url . $class;
        }



        /**
         * Returns object encoded in json
         * Encode only first level (FK are expressed as ID strings)
         * @param $data object
         * @param $secret
         * @return string
         * @internal param string $hash
         */
        private function json_encode_object($data, $secret)
        {
            $result = $this->processor->convertJson($data);
            $result['timestamp'] = (new \DateTime())->getTimestamp();
            $result['hash']      = hash("sha256", $secret . (implode(",", $result)));

            return json_encode($result);
        }



        private function processNotification($data, $url, $method = self::POST, $is_encoded = false, $secret = null, $pass = 1)
        {
            if (!$is_encoded) {
                $data = is_object($data) ? $this->json_encode_object($data, $secret) : json_encode($data);
            }

            $client = new Client();
            $request = $client->createRequest($method, $url, [
                'headers' => ['Content-type' => 'application/json'],
                'body'    => $data,
                'future'  => true
            ]);

            /** @var \GuzzleHttp\Message\FutureResponse $response */
            $response = $client->send($request);

            if (!$response || !$response->getStatusCode() || $response->getStatusCode() != '200') {
                if ($pass < 5) {
                    sleep(rand(1, 10));
                    return $this->processNotification($data, $url, $pass++, true);
                } else {
                    throw new HttpException(
                        $response->getStatusCode(),
                        "Notification error. {$response->getBody()}"
                    );
                }
            } else {
                return $response->json();
            }
        }

    }