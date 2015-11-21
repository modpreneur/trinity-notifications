<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Drivers\ClientApiDriver;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Trinity\FrameworkBundle\Entity\IClient;
use Trinity\NotificationBundle\Driver\BaseDriver;

class ClientApiDriver extends BaseDriver
{
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';


    /**
     * @param object  $entity
     * @param IClient $necktie
     * @param array   $params
     * @param string  $clientSecret
     * @param string  $clientId
     *
     * @return mixed
     */
    public function execute($entity, $necktie, $params = [], $clientSecret = null, $clientId = null)
    {
        $response = '';
        $HTTPMethod = self::POST;

        if (array_key_exists('HTTPMethod', $params)) {
            $HTTPMethod = $params['HTTPMethod'];
        }

        $url = $this->prepareURL($necktie->getNotificationUri(), $entity, $HTTPMethod);
        $json = $this->JSONEncodeObject($entity, $clientSecret);

        try {
            $response = $this->createRequest($json, $url, $HTTPMethod, true);
            //todo:events
            //$this->eventDispatcher->dispatch(
            //    Events::SUCCESS_NOTIFICATION,
            //    new StatusEvent($client, $entity, $entity->getId(), $url, $json, $HTTPMethod, null, null)
            //);
        } catch (\Exception $ex) {
            $message = "$HTTPMethod: URL: ".$url.' returns error: '.$ex->getMessage().'.';

            //todo:events
            //$this->eventDispatcher->dispatch(
            //    Events::ERROR_NOTIFICATION,
            //    new StatusEvent($client, $entity, $entity->getId(), $url, $json, $HTTPMethod, $ex, $message)
            //);

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
     * @param bool $isEncoded
     * @param string $clientSecret
     * @param string $clientId
     * @param string $oauthUrl
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
        $oauthUrl = null
    )
    {
        if (!$isEncoded) {
            $data = is_object($data) ? $this->JSONEncodeObject($data, $clientSecret) : json_encode($data);
        }

        $httpClient = new Client();

        //new interface(v6.0)
        $request = $this->prepareRequestWithAuthorization($oauthUrl, $clientSecret, $clientId);
        $request->withMethod(self::POST);
        $request->withUri($url);

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
     * Return name of driver.
     *
     * @return string
     */
    public function getName()
    {
        return 'client_api_driver';
    }


    /**
     * @param $oauthUrl
     * @param $clientSecret
     * @param $clientId
     *
     * @return Request
     */
    public function prepareRequestWithAuthorization($oauthUrl, $clientSecret, $clientId)
    {
        $authorizedRequest = new Request();
        $httpClient = new Client();
        $oauthRequest = new Request($oauthUrl, self::POST);

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

        ldd($oauthResponse);

        $accessToken = $oauthResponse['access_token'];

        $authorizedRequest->withHeader('Authorization', "Bearer $accessToken");

        return $authorizedRequest;
    }
}