<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Driver;

use Doctrine\Common\Collections\Collection;
use Nette\Utils\Strings;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Trinity\FrameworkBundle\Entity\IClient;
use Trinity\NotificationBundle\Exception\ClientException;
use Trinity\NotificationBundle\Exception\MethodException;
use Trinity\NotificationBundle\Notification\Annotations\NotificationUtils;
use Trinity\NotificationBundle\Notification\EntityConverter;



/**
 * Class BaseDriver.
 */
abstract class BaseDriver implements INotificationDriver
{
    /** @var  EntityConverter */
    protected $entityConverter;

    /** @var  NotificationUtils */
    protected $notificationUtils;

    /** @var  EventDispatcher */
    protected $eventDispatcher;



    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcher $eventDispatcher
     * @param EntityConverter $entityConverter
     * @param NotificationUtils $notificationUtils
     */
    public function __construct(
        $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityConverter = $entityConverter;
        $this->notificationUtils = $notificationUtils;
    }



    /**
     * Returns object encoded in json.
     * Encode only first level (FK are expressed as ID strings).
     *
     * @param object $entity
     * @param string $secret
     *
     * @return string
     */
    protected function JSONEncodeObject($entity, $secret)
    {
        $result = $this->entityConverter->toArray($entity);
        $result['timestamp'] = (new \DateTime())->getTimestamp();
        $result['hash'] = hash('sha256', $secret.(implode(',', $result)));

        return json_encode($result);
    }



    /**
     * Transform clients collection to array.
     *
     * @param IClient|Collection|array $clientsCollection
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
    protected function prepareURL($url, $entity, $HTTPMethod)
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
}
