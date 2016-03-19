<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Driver;

use Doctrine\ORM\EntityManager;
use Nette\Utils\Strings;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Exception\ClientException;
use Trinity\NotificationBundle\Exception\MethodException;
use Trinity\NotificationBundle\Notification\Annotations\NotificationUtils;
use Trinity\NotificationBundle\Notification\EntityConverter;


/**
 * Class BaseDriver.
 */
abstract class BaseDriver implements NotificationDriverInterface
{
    /** @var  EntityConverter */
    protected $entityConverter;

    /** @var  NotificationUtils */
    protected $notificationUtils;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    protected $tokenStorage;

    /** @var  EntityManager */
    protected $entityManager;

    /**
     * @var array Array which contains already processed entities(should fix deleting errors).
     *
     * First level indexes are classnames.
     * Second level indexes are entity ids.
     */
    protected $notifiedEntities = [];


    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface   $eventDispatcher
     * @param EntityConverter   $entityConverter
     * @param NotificationUtils $notificationUtils
     * @param TokenStorage      $tokenStorage
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        TokenStorage $tokenStorage = null

    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityConverter = $entityConverter;
        $this->notificationUtils = $notificationUtils;
        $this->tokenStorage = $tokenStorage;
    }


    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Returns object encoded in json.
     * Encode only first level (FK are expressed as ID strings).
     *
     * @param array  $entity
     * @param string $secret
     *
     * @param array  $extraFields
     *
     * @return string
     * @throws \Exception
     */
    protected function JSONEncodeObject($entity, $secret, $extraFields = [])
    {
        foreach ($extraFields as $extraFieldKey => $extraFieldValue) {
            $entity[$extraFieldKey] = $extraFieldValue;
        }

        $entity['timestamp'] = (new \DateTime())->getTimestamp();
        $entity['hash'] = hash('sha256', $secret.(implode(',', $entity)));

        // error fix...
        // todo: it is necessary to distinguish null and empty string
        $entity = str_replace('null', '""', $entity);

        return json_encode($entity);
    }


    /**
     * Join client URL with entity url.
     *
     * Example: TestClient URL => "http://example.com"
     *          Entity(Product) URL => "product" -> addicted to annotations (method and prefix)
     *          result: http://example.com/product
     *
     * @param string $url
     * @param NotificationEntityInterface $entity
     * @param string $HTTPMethod
     *
     * @return array
     *
     * @throws ClientException
     * @throws MethodException
     */
    protected function prepareURL($url, NotificationEntityInterface $entity, $HTTPMethod)
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
     * Add entity to notifiedEntities array.
     *
     * @param NotificationEntityInterface $entity
     */
    protected function addEntityToNotifiedEntities(NotificationEntityInterface $entity)
    {
        // add id to the entity array so it will not be notified again
        $this->notifiedEntities[get_class($entity)][] = $entity->getId();
    }


    /**
     * Check if the current entity was already processed.
     *
     * @param NotificationEntityInterface $entity
     *
     * @return bool
     */
    protected function isEntityAlreadyProcessed(NotificationEntityInterface $entity)
    {
        $class = get_class($entity);

        return array_key_exists($class, $this->notifiedEntities) && in_array($entity->getId(), $this->notifiedEntities[$class]);
    }
}

