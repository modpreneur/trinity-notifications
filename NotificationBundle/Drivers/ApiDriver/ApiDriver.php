<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Drivers\ApiDriver;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Nette\Utils\Strings;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Driver\BaseDriverInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\StatusEvent;
use Trinity\NotificationBundle\Exception\NotificationDriverException;


/**
 * Class ApiDriver.
 */
class ApiDriver extends BaseDriverInterface
{
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';


    /**
     * @param object $entity
     * @param $client
     * @param array $params
     *
     * @return string
     */
    public function execute($entity, ClientInterface $client, $params = [])
    {
        if(!($entity instanceof NotificationEntityInterface)){
            return;
        }

        $response = '';
        $HTTPMethod = self::POST;
        $user = null;

        if ($this->tokenStorage) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        if ($client->isNotificationEnabled()) {
            if (array_key_exists('HTTPMethod', $params)) {
                $HTTPMethod = $params['HTTPMethod'];
            }

            $url = $this->prepareURL($client->getNotificationUri(), $entity, $HTTPMethod);
            $json = $this->JSONEncodeObject($entity, $client->getSecret());
            try {
                $response = $this->createRequest($json, $url, $HTTPMethod, true);
                $this->eventDispatcher->dispatch(
                    Events::SUCCESS_NOTIFICATION,
                    new StatusEvent($client, $entity, $entity->getId(), $url, $json, $HTTPMethod, null, null, $user)
                );
                $entity->setSyncStatus($client, 'ok');
            } catch (\Exception $ex) {

                $message = "$HTTPMethod: URL: ".$url.' returns error: '.$ex->getMessage().'.';
                $this->eventDispatcher->dispatch(
                    Events::ERROR_NOTIFICATION,
                    new StatusEvent($client, $entity, $entity->getId(), $url, $json, $HTTPMethod, $ex, $message, $user)
                );

                $entity->setSyncStatus($client, 'error');

                $response = "ERROR - $message";
            }
        }

        return $response;
    }


    /**
     * Send request to client.
     * TestClient = web application (http:example.com).
     *
     * @param object|string $data
     * @param string $url
     * @param string $method
     * @param bool $isEncoded
     * @param null $secret
     *
     * @return mixed
     * @throws NotificationDriverException
     */
    private function createRequest(
        $data,
        $url,
        $method = self::POST,
        $isEncoded = false,
        $secret = null
    ) {
        if (!$isEncoded) {
            $data = is_object($data) ? $this->JSONEncodeObject($data, $secret) : json_encode($data);
        }

        $httpClient = new Client();

        //new interface(v6.0)
        $request = new Request($method, $url, ['content-type' => 'application/json'], $data);
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
        ['error' => "Web resultj: " . $body];
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
