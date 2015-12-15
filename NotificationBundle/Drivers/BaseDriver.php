<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Driver;

use Nette\Utils\Strings;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Trinity\FrameworkBundle\Entity\BaseUser;
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

    protected $tokenStorage;


    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcher $eventDispatcher
     * @param EntityConverter $entityConverter
     * @param NotificationUtils $notificationUtils
     * @param TokenStorage $tokenStorage
     */
    public function __construct(
        $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        TokenStorage $tokenStorage = null

    ) {
        $this->eventDispatcher   = $eventDispatcher;
        $this->entityConverter   = $entityConverter;
        $this->notificationUtils = $notificationUtils;
        $this->tokenStorage      = $tokenStorage;
    }


    /**
     * Returns object encoded in json.
     * Encode only first level (FK are expressed as ID strings).
     *
     * @param object $entity
     * @param string $secret
     *
     * @param array  $extraFields
     *
     * @return string
     * @throws \Exception
     */
    protected function JSONEncodeObject($entity, $secret, $extraFields = [])
    {
        $result = $this->entityConverter->toArray($entity);

        foreach ($extraFields as $extraFieldKey => $extraFieldValue) {
            $result[$extraFieldKey] = $extraFieldValue;
        }

        $result['timestamp'] = (new \DateTime())->getTimestamp();
        $result['hash'] = hash('sha256', $secret.(implode(',', $result)));
        return json_encode($result);
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
