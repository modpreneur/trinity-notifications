<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Drivers\ApiDriver;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Nette\Utils\Strings;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Driver\BaseDriver;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\StatusEvent;
use Trinity\NotificationBundle\Exception\NotificationDriverException;


/**
 * Class ApiDriver.
 */
class ApiDriver extends BaseDriver
{
    const DELETE = 'DELETE';
    const POST   = 'POST';
    const PUT    = 'PUT';

    private $entityQueue = [];

    /**
     * @param NotificationEntityInterface $entity
     * @param $client
     * @param array $params
     *
     * @return string
     */
    public function execute(NotificationEntityInterface $entity, ClientInterface $client, $params = [])
    {
        $id = $entity->getId();
        $class = get_class($entity);

        if(!$id) {
            return [];
        }

        if(array_key_exists($class, $this->entityQueue) && in_array($id, $this->entityQueue[$class])) {
            return [];
        }

        $this->entityQueue[$class][] = $id;

        $response = [];
        $HTTPMethod = self::POST;
        $user = null;

        if ($this->tokenStorage) {
            $token = $this
                ->tokenStorage
                ->getToken();

            if($token){
                $user = $token->getUser();
            }
        }

        $entityArray = $this->entityConverter->toArray($entity);

        foreach( $entity->getClients() as $client ){

            if ($client->isNotificationEnabled()) {
                if (array_key_exists('HTTPMethod', $params)) {
                    $HTTPMethod = $params['HTTPMethod'];
                }

                $url = $this->prepareURL($client->getNotificationUri(), $entity, $HTTPMethod);
                $json = $this->JSONEncodeObject($entityArray, $client->getSecret());

                try {
                    $response[] = $this->createRequest($json, $url, $HTTPMethod, true, null,  $params);
                    $this->eventDispatcher->dispatch(
                        Events::SUCCESS_NOTIFICATION,
                        new StatusEvent($client, $entity, $id, $url, $json, $HTTPMethod, null, null, $user)
                    );
                    $entity->setNotificationStatus($client, 'ok');
                } catch (\Exception $ex) {

                    $message = "$HTTPMethod: URL: ".$url.' returns error: '.$ex->getMessage().'.';
                    $this->eventDispatcher->dispatch(
                        Events::ERROR_NOTIFICATION,
                        new StatusEvent($client, $entity, $id, $url, $json, $HTTPMethod, $ex, $message, $user)
                    );

                    $entity->setNotificationStatus($client, 'error');
                    $response[] = "ERROR - $message";
                }
            }
        }

        return $response;
    }


    /**
     * Send request to client.
     * TestClient = web application (http:example.com).
     *
     * @param object|string $data
     * @param string $uri
     * @param string $method
     * @param bool $isEncoded
     * @param null $secret
     *
     * @param array $params
     * @return mixed
     * @throws NotificationDriverException
     */
    private function createRequest(
        $data,
        $uri,
        $method = self::POST,
        $isEncoded = false,
        $secret = null,
        array $params = []
    ) {
        if (!$isEncoded) {
            $data = is_object($data) ? $this->JSONEncodeObject($data, $secret) : json_encode($data);
        }

        $httpClient = new Client();

        $request = new Request($method, $uri, ['content-type' => 'application/json'], $data);

        /** @var Client $response */
        $response = $httpClient->send($request);

        if (Strings::contains((string)$response->getBody(), '"code":404')) {
            throw new NotificationDriverException((string)$response->getBody());
        }

        if (Strings::contains((string)$response->getBody(), '"code":500')) {
            throw new NotificationDriverException((string)$response->getBody());
        }

        $body = (string)$response->getBody();

        return json_decode($body, true)
               ??
               ['error' => "Client result: " . $body];
    }


    /**
     * Return name of driver.
     *
     * @return string
     */
    public function getName()
    {
        return 'api_driver';
    }

}
