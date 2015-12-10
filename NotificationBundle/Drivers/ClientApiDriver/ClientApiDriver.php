<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Drivers\ClientApiDriver;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Nette\Utils\Strings;
use Trinity\FrameworkBundle\Entity\IClient;
use Trinity\NotificationBundle\Driver\BaseDriver;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\StatusEvent;
use Trinity\NotificationBundle\Exception\ClientException;
use Trinity\NotificationBundle\Notification\Annotations\NotificationUtils;
use Trinity\NotificationBundle\Notification\EntityConverter;

class ClientApiDriver extends BaseDriver
{
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';

    protected $oauthUrl;
    protected $masterClientSecret;
    protected $masterClientId;

    public function __construct($eventDispatcher, EntityConverter $entityConverter, NotificationUtils $notificationUtils, $oauthUrl, $masterClientSecret, $masterClientId)
    {
        parent::__construct($eventDispatcher, $entityConverter, $notificationUtils);

        $this->oauthUrl = $oauthUrl;
        $this->masterClientSecret = $masterClientSecret;
        $this->masterClientId = $masterClientId;
    }


    /**
     * @param object $entity
     * @param IClient $master
     * @param array $params
     *
     * @return mixed
     */
    public function execute($entity, $master, $params = [])
    {
        $HTTPMethod = self::POST;

        if (array_key_exists('HTTPMethod', $params)) {
            $HTTPMethod = $params['HTTPMethod'];
        }

        // Try to get access token to the master
        try {
            $oauthAccessToken = $this->getAccessToken($this->oauthUrl, $this->masterClientSecret, $this->masterClientId);
        } catch (\Exception $ex) {
            $message = "$HTTPMethod: request to OAuth URL: " . $this->oauthUrl . ' returns error: ' . $ex->getMessage() . '.';

            $this->eventDispatcher->dispatch(
                Events::ERROR_NOTIFICATION,
                new StatusEvent($master, $entity, $entity->getId(), $this->oauthUrl, null, $HTTPMethod, $ex, $message)
            );

            $response = "ERROR - $message";

            return $response;
        }

        $url = $this->prepareURL($master->getNotificationUri(), $entity, $HTTPMethod);
        $json = $this->JSONEncodeObject(
            $entity,
            $this->masterClientSecret,
            ["notification_oauth_client_id" => $this->masterClientId]
        );

        // Try to send notification data
        try {
            $response = $this->createRequest(
                $json,
                $url,
                $HTTPMethod,
                true,
                $this->masterClientSecret,
                $this->masterClientId,
                $oauthAccessToken
            );

            $this->eventDispatcher->dispatch(
                Events::SUCCESS_NOTIFICATION,
                new StatusEvent($master, $entity, $entity->getId(), $url, $json, $HTTPMethod, null, null)
            );
        } catch (\Exception $ex) {
            $message = "$HTTPMethod: URL: " . $url . ' returns error: ' . $ex->getMessage() . '.';

            $this->eventDispatcher->dispatch(
                Events::ERROR_NOTIFICATION,
                new StatusEvent($master, $entity, $entity->getId(), $url, $json, $HTTPMethod, $ex, $message)
            );
            $response = "ERROR - $message";
        }

        return $response;
    }


    /**
     * Send request to web application (http:example.com).
     *
     * @param object|string $data
     * @param string $url
     * @param string $method
     * @param bool   $isEncoded
     * @param string $clientSecret
     * @param string $clientId
     * @param string $accessToken
     *
     * @return mixed
     */
    protected function createRequest(
        $data,
        $url,
        $method = self::POST,
        $isEncoded = false,
        $clientSecret = null,
        $clientId = null,
        $accessToken = null
    )
    {
        if (!$isEncoded) {
            if (is_object($data)) {
                $data = $this->JSONEncodeObject($data, $clientSecret, ["notification_oauth_client_id" => $clientId]);
            } else {
                $data["notification_oauth_client_id"] = $clientId;
                $data = json_encode($data);
            }
        }

        $httpClient = new Client();

        //new interface(v6.0)
        $request = new Request($method, $url);

        $response = $httpClient->send(
            $request,
            [
                'headers' => ['Content-type' => 'application/json', "Authorization" => "Bearer $accessToken"],
                'body' => $data,
                'future' => true,
            ]
        );

        return json_decode(
            (string)$response->getBody(),
            true,
            512,
            0
        );

    }


    /**
     * Join client URL with entity url.
     *
     * Example: Client URL => "http://client.com"
     *          Entity(Product) URL => "product" -> addicted to annotations (method and prefix)
     *          result: http://client.com/product
     *
     * @param string $url
     * @param object $entity
     * @param string $HTTPMethod
     *
     * @return array
     *
     * @throws ClientException
     */
    protected function prepareURL($url, $entity, $HTTPMethod)
    {
        if ($url === null || empty($url)) {
            throw new ClientException('Notification: NULL client URL.');
        }

        $class = $this->notificationUtils->getUrlPostfix($entity, $HTTPMethod);

        // add / to url
        if (!Strings::endsWith($url, '/')) {
            $url .= '/';
        }

        return $url . $class;
    }


    /**
     * @param $oauthUrl
     * @param $clientSecret
     * @param $clientId
     *
     * @return Request
     */
    protected function getAccessToken($oauthUrl, $clientSecret, $clientId)
    {
        $httpClient = new Client();
        $oauthRequest = new Request(self::POST, $oauthUrl);

        $oauthResponse = $httpClient->send(
            $oauthRequest,
            [
                'headers' => ['Content-type' => 'application/json'],
                'body' => json_encode(
                    [
                        'grant_type' => 'client_credentials',
                        'client_secret' => $clientSecret,
                        'client_id' => $clientId
                    ]
                ),
            ]
        );

        $oauthResponse = json_decode(
            (string)$oauthResponse->getBody(),
            true,
            512,
            0
        );

        return $oauthResponse['access_token'];
    }


    /**
     * Return name of driver.
     *
     * @return string
     */
    public function getName()
    {
        return 'client_api_driver';
    }
}