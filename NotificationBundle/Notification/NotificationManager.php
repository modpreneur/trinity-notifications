<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Collections\Collection;
use GuzzleHttp\Client;
use Nette\Utils\Strings;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Trinity\FrameworkBundle\Entity\IClient;
use Trinity\NotificationBundle\Event\Events;
use Trinity\NotificationBundle\Event\SendEvent;
use Trinity\NotificationBundle\Event\StatusEvent;
use Trinity\NotificationBundle\Exception\ClientException;
use Trinity\NotificationBundle\Exception\MethodException;
use Trinity\NotificationBundle\Notification\Annotations\NotificationUtils;

/**
 * Class NotificationManager.
 */
class NotificationManager
{
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';

    /** @var  NotificationUtils */
    protected $notificationUtils;

    /** @var  EventDispatcher */
    protected $eventDispatcher;

    /** @var  EntityConverter */
    protected $entityConverter;

    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcher   $eventDispatcher
     * @param NotificationUtils $annotationProcessor
     * @param EntityConverter   $entityConverter
     */
    public function __construct(
        $eventDispatcher,
        NotificationUtils $annotationProcessor,
        EntityConverter $entityConverter
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationUtils = $annotationProcessor;
        $this->entityConverter = $entityConverter;
    }

    /**
     *  Send notification to client (App).
     *
     *
     * @param object $entity
     * @param string $HTTPMethod
     *
     * @return mixed|string|void
     *
     * @throws ClientException
     * @throws MethodException
     */
    public function send($entity, $HTTPMethod = 'GET')
    {
        $response = '';

        // before send event
        $this->eventDispatcher->dispatch(Events::BEFORE_NOTIFICATION_SEND, new SendEvent($entity));
        $clients = $this->clientsToArray($entity->getClients());

        if ($clients) {
            foreach ($clients as $client) {
                if (!$client->isNotificationEnabled()) {
                    continue;
                }

                $url = $this->prepareURL($client->getNotifyUrl(), $entity, $HTTPMethod);
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
        }

        //dump($url);

        $this->eventDispatcher->dispatch(Events::AFTER_NOTIFICATION_SEND, new SendEvent($entity));

        return $response;
    }

    /**
     * Transform clients collection to array.
     *
     * @param Client|Collection|array $clientsCollection
     *
     * @return Object[]
     *
     * @throws ClientException
     */
    protected function clientsToArray($clientsCollection)
    {
        $clients = [];

        if ($clientsCollection instanceof Collection) {
            $clients = $clientsCollection->toArray();
        } elseif ($clientsCollection instanceof IClient) {
            $clients[] = $clientsCollection;
        } elseif (is_array($clientsCollection)) {
            $clients = $clientsCollection;
        }

        return $clients;
    }

    /**
     * Join client URL with entity url.
     *
     * Example: Client URL => "http://example.com"
     *          Entity(Product) URL => "product" -> addicted to annotations (method and prefix)
     *          result: http://example.com/product
     *
     * @param string $url
     * @param object $entity
     * @param string $HTTPMethod
     *
     * @return array
     *
     * @throws ClientException
     * @throws MethodException
     */
    private function prepareURL($url, $entity, $HTTPMethod)
    {
        $methodName = 'getClients';
        if (!is_callable([$entity, $methodName])) {
            throw new MethodException("Method '$methodName' not exists in entity.");
        }

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
     * Returns object encoded in json.
     * Encode only first level (FK are expressed as ID strings).
     *
     * @param object $entity
     * @param string $secret
     *
     * @return string
     *
     * @internal param string $hash
     */
    private function JSONEncodeObject($entity, $secret)
    {
        $result = $this->entityConverter->toArray($entity);
        $result['timestamp'] = (new \DateTime())->getTimestamp();
        $result['hash'] = hash('sha256', $secret.(implode(',', $result)));

        return json_encode($result);
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
    )
    {
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
}
