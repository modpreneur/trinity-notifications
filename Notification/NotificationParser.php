<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Psr\Log\LoggerInterface;
use Trinity\NotificationBundle\Exception;
use Trinity\NotificationBundle\Exception\HashMismatchException;


/**
 * Responsible for parsing notification request and performing entity edits
 *
 * Class NotificationParser
 */
class NotificationParser
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var object EntityManagerInterface */
    protected $entityManager;

    /** @var EntityConverter */
    protected $entityConverter;

    /** @var string TestClient secret */
    protected $clientSecret;

    /** @var array Array of request data */
    protected $parametersArray;

    // The set method is not called on the entity for
    /** @var array Fields with special meaning. The set method is not called on the entity for those fields. */
    protected $ignoredFields = ["timestamp", "hash", "notification_oauth_client_id", "entityName"];

    /** @var string Field name which will be mapped to the id from the notification request */
    protected $entityIdFieldName;

    /** @var  bool */
    protected $isClient;


    /**
     * NotificationParser constructor.
     * @param LoggerInterface $logger
     * @param EntityConverter $entityConverter
     * @param $entityIdFieldName
     * @param $isClient
     */
    public function __construct(
        LoggerInterface $logger,
        EntityConverter $entityConverter,
        $entityIdFieldName,
        $isClient
    )
    {
        $this->logger = $logger;
        $this->entityConverter = $entityConverter;
        $this->parametersArray = [];
        $this->entityIdFieldName = $entityIdFieldName;
        $this->isClient = $isClient;
    }


    /**
     * @param  $parameters     array  Request data as named array
     * @param  $fullClassName  string Full classname(with namespace) of the entity. e.g. AppBundle\\Entity\\Product\\StandardProduct
     * @param  $method         string HTTP method of the request
     *
     * @return null|object Returns changed entity(on new[POST] or update[PUT]) or null on delete[DELETE]
     *
     * @throws HashMismatchException When the hash does not match
     */
    public function parseNotification($parameters, $fullClassName, $method)
    {
        $this->parametersArray = $parameters;

        $method = strtoupper($method);

        //get existing entity from database or create a new one
        $entityObject = $this->getEntityObject($fullClassName, $this->entityIdFieldName);

        if ($method == "POST" || $method == "PUT") {
            $this->logger->info("METHOD: POST||PUT:" . $method);

            $entityObject = $this->entityConverter->performEntityChanges(
                $entityObject,
                $this->parametersArray,
                $this->ignoredFields
            );

            $this->entityManager->persist($entityObject);
            $this->entityManager->flush();

            return $entityObject;
        } else {
            if ($method == "DELETE") {
                $this->logger->info("METHOD: DELETE " . $method);
                $this->entityManager->remove($entityObject);
                $this->entityManager->flush();

                return null;
            } else {
                $this->logger->info("method is not supported" . $method);
            }
        }

        return null;
    }


    /**
     * Get existing entity or create a new one
     *
     * @param $fullClassName string Full classname(with namespace) of the entity. e.g. AppBundle\\Entity\\Product\\StandardProduct
     *
     * @param $fieldName string Entity field which will be mapped to field "id" from request
     *
     * @return null|object
     */
    protected function getEntityObject($fullClassName, $fieldName)
    {
        $entityObject = $this->entityManager->getRepository($fullClassName)->findOneBy(
            [$fieldName => $this->parametersArray["id"]]
        );

        //set server id
        //only on client
        if ($entityObject && $this->isClient) {
            call_user_func_array([$entityObject, "set" . ucfirst($fieldName)], [$this->parametersArray["id"]]);
        }

        if ($entityObject) {
            return $entityObject;
        }

        $entityClass = new \ReflectionClass($fullClassName);
        $entityObject = $entityClass->newInstanceArgs();

        return $entityObject;
    }


    /**
     * @param $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->entityConverter->setEntityManager($entityManager);
    }
}