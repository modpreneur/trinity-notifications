<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Trinity\NotificationBundle\Exception;
use Trinity\NotificationBundle\Exception\HashMismatchException;

/**
 * Responsible for parsing notification request and performing entity edits
 *
 * Class NotificationParser
 */
class NotificationParser
{
    protected $logger;

    /** @var object EntityManagerInterface */
    protected $entityManager;

    protected $entityConverter;

    protected $clientSecret;

    protected $request;

    protected $parametersArray;

    // The set method is not called on the entity
    // These fields has special meaning
    protected $ignoredFields = ["id", "timestamp", "hash", "notification_oauth_client_id"];

    protected $entityIdFieldName;


    public function __construct(LoggerInterface $logger, EntityConverter $entityConverter, $entityIdFieldName)
    {
        $this->logger = $logger;
        $this->entityConverter = $entityConverter;
        $this->parametersArray = [];
        $this->entityIdFieldName = $entityIdFieldName;
    }


    /**
     * @param  $parameters     array  Request data as named array
     * @param  $fullClassName  string Full classname(with namespace) of the entity. e.g. AppBundle\\Entity\\Product\\StandardProduct
     * @param  $method         string HTTP method of the request
     * @param  $clientSecret   string Oauth secret of the client from which the notification came from
     *
     * @return null|object Returns changed entity(on new[POST] or update[PUT]) or null on delete[DELETE]
     *
     * @throws HashMismatchException When the hash does not match
     */
    public function parseNotification($parameters, $fullClassName, $method, $clientSecret)
    {
        $this->parametersArray = $parameters;

        //check, if the data was not modified
        if(!$this->isHashOk($clientSecret))
        {
            throw new HashMismatchException("Request hash does not match");
        }

        $method = strtoupper($method);

        //get existing entity from database or create a new one
        $entityObject = $this->getEntityObject($fullClassName, $this->entityIdFieldName);

        if($method == "POST" || $method == "PUT")
        {
            $this->logger->emergency("METHOD: POST||PUT:" . $method);

            $entityObject = $this->entityConverter->performEntityChanges($entityObject, $this->parametersArray, $this->ignoredFields);

            $this->entityManager->persist($entityObject);
            $this->entityManager->flush();

            return $entityObject;
        }
        else if($method == "DELETE")
        {
            $this->logger->emergency("METHOD: DELETE " . $method);
            $this->entityManager->remove($entityObject);
            $this->entityManager->flush();

            return null;
        }
        else
        {
            $this->logger->emergency("method is not supported" . $method);
        }

        return null;
    }


    /**
     * Check if the received data isn't modified(the given hash matches the newly generated hash)
     *
     * @param $clientSecret string Oauth secret of the client from which the notification came from
     *
     * @return bool
     */
    protected function isHashOk($clientSecret)
    {
        //copy received data and remove hash
        $data = $this->parametersArray;
        $oldHash = $data["hash"];
        unset($data["hash"]);

        //hash received data without hash
        $newHash = hash("sha256", ($clientSecret . implode(',', $data)));

        //if the hashes don't match the data is malformed
        return $oldHash == $newHash;
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
        $entityObject = $this
            ->entityManager
            ->getRepository($fullClassName)
            ->findOneBy([$fieldName => $this->parametersArray["id"]]);

        if($entityObject)
        {
            return $entityObject;
        }

        $entityClass = new \ReflectionClass($fullClassName);
        $entityObject =  $entityClass->newInstanceArgs();
        call_user_func_array([$entityObject, "set" . ucfirst($fieldName)], [$this->parametersArray["id"]]);
        return $entityObject;
    }


    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->entityConverter->setEntityManager($entityManager);
    }
}