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
    protected $necktieClientSecret;
    protected $necktieClientId;

    public function __construct($eventDispatcher, EntityConverter $entityConverter, NotificationUtils $notificationUtils, $oauthUrl, $necktieClientSecret, $necktieClientId)
    {
        parent::__construct($eventDispatcher, $entityConverter, $notificationUtils);

        $this->oauthUrl = $oauthUrl;
        $this->necktieClientSecret = $necktieClientSecret;
        $this->necktieClientId = $necktieClientId;
    }


    /**
     * @param object  $entity
     * @param IClient $necktie
     * @param array   $params
     *
     * @return mixed
     */
    public function execute($entity, $necktie, $params = [])
    {
        $HTTPMethod = self::POST;

        if (array_key_exists('HTTPMethod', $params)) {
            $HTTPMethod = $params['HTTPMethod'];
        }
        $url = $this->prepareURL($necktie->getNotificationUri(), $entity, $HTTPMethod);
        $json = $this->JSONEncodeObject($entity, $this->necktieClientSecret);

        // Try to get access token to the necktie
        try {
            $oauthAccessToken = $this->getAccessToken($this->oauthUrl, $this->necktieClientSecret, $this->necktieClientId);
        } catch (\Exception $ex) {
            $message = "$HTTPMethod: URL: ".$this->oauthUrl.' returns error: '.$ex->getMessage().'.';

            $this->eventDispatcher->dispatch(
                Events::ERROR_NOTIFICATION,
                new StatusEvent($necktie, $entity, $entity->getNecktieId(), $url, $json, $HTTPMethod, $ex, $message)
            );

            $response = "ERROR - $message";

            return $response;
        }

        // Try to send notification data
        try {
            $response = $this->createRequest($json, $url, $HTTPMethod, true, $this->necktieClientSecret, $oauthAccessToken);
            $this->eventDispatcher->dispatch(
                Events::SUCCESS_NOTIFICATION,
                new StatusEvent($necktie, $entity, $entity->getNecktieId(), $url, $json, $HTTPMethod, null, null)
            );
        } catch (\Exception $ex) {
            $message = "$HTTPMethod: URL: ".$url.' returns error: '.$ex->getMessage().'.';

            //todo:events
            $this->eventDispatcher->dispatch(
                Events::ERROR_NOTIFICATION,
                new StatusEvent($necktie, $entity, $entity->getNecktieId(), $url, $json, $HTTPMethod, $ex, $message)
            );
            $response = "ERROR - $message";
        }

        return $response;
    }


    /**
     * Send request to web application (http:example.com).
     *
     * @param object|string $data
     * @param string        $url
     * @param string        $method
     * @param bool          $isEncoded
     * @param string        $clientSecret
     * @param null          $accessToken
     *
     * @return mixed
     */
    protected function createRequest(
        $data,
        $url,
        $method = self::POST,
        $isEncoded = false,
        $clientSecret = null,
        $accessToken = null
    )
    {
        if (!$isEncoded) {
            $data = is_object($data) ? $this->JSONEncodeObject($data, $clientSecret) : json_encode($data);
        }

        $httpClient = new Client();

        //new interface(v6.0)
        $request = new Request($method, $url);
        $request->withHeader("Authorization", "Bearer $accessToken");

        $response = $httpClient->send(
            $request,
            [
                'headers' => ['Content-type' => 'application/json'],
                'body'    => $data,
                'future'  => true,
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
     * Example: Client URL => "http://necktie.com"
     *          Entity(Product) URL => "product" -> addicted to annotations (method and prefix)
     *          result: http://necktie.com/product
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

        return $url.$class;
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
                'body'    => json_encode(
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