<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Driver;

use GuzzleHttp\Client;
use Trinity\FrameworkBundle\Entity\IClient;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\StatusEvent;

/**
 * Class ApiDriver.
 */
class ApiDriver extends BaseDriver
{
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';

    /**
     * @param object  $entity
     * @param IClient $client
     * @param array   $params
     *
     * @return string
     */
    public function execute($entity, $client, $params = [])
    {
        $response = '';
        $HTTPMethod = self::POST;

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
                    new StatusEvent($client, $entity, $entity->getId(), $url, $json, $HTTPMethod, null, null)
                );
            } catch (\Exception $ex) {
                $message = "$HTTPMethod: URL: ".$url.' returns error: '.$ex->getMessage().'.';

                $this->eventDispatcher->dispatch(
                    Events::ERROR_NOTIFICATION,
                    new StatusEvent($client, $entity, $entity->getId(), $url, $json, $HTTPMethod, $ex, $message)
                );

                $response = "ERROR - $message";
            }
        }

        return $response;
    }

    /**
     * Send request to client.
     * Client = web application (http:example.com).
     *
     * @param object|string $data
     * @param string        $url
     * @param string        $method
     * @param bool          $isEncoded
     * @param null          $secret
     *
     * @return mixed
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

        $request = $httpClient->createRequest(
            $method,
            $url,
            [
                'headers' => ['Content-type' => 'application/json'],
                'body' => $data,
                'future' => true,
            ]
        );

        // throw ClientException
        /** @var \GuzzleHttp\Message\FutureResponse $response */
        $response = $httpClient->send($request);

        return $response->json();
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
