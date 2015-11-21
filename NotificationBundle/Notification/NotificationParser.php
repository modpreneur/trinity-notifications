<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;
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

    protected $entityManager;

    protected $entityConverter;

    protected $necktieClientSecret;

    protected $request;

    protected $parametersArray;

    //the set method is not called on the entity
    //these fields has special meaning
    protected $ignoredFields = ["id", "timestamp", "hash"];


    public function __construct(LoggerInterface $logger, EntityManager $entityManager, EntityConverter $entityConverter, $necktieClientSecret)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->necktieClientSecret = $necktieClientSecret;
        $this->entityConverter = $entityConverter;
        $this->parametersArray = [];
    }


    /**
     * @param $parameters array Request data as named array
     * @param $fullClassName string Full classname(with namespace) of the entity. e.g. AppBundle\\Entity\\Product\\StandardProduct
     * @param $method string HTTP method of the request
     *
     * @return null|object Returns changed entity(on new[POST] or update[PUT]) or null on delete[DELETE]
     * @throws HashMismatchException When the hash does not match
     */
    public function parseNotification($parameters, $fullClassName, $method)
    {
        $this->parametersArray = $parameters;

        //check, if the data was not modified
        if(!$this->isHashOk())
        {
            throw new HashMismatchException("Request hash does not match");
        }

        $method = strtoupper($method);

        //get existing entity from database or create a new one
        $entityObject = $this->getEntityObject($fullClassName);

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
     * @return bool
     */
    protected function isHashOk()
    {
        //copy received data and remove hash
        $data = $this->parametersArray;
        $oldHash = $data["hash"];
        unset($data["hash"]);

        //hash received data without hash
        $newHash = hash("sha256", ($this->necktieClientSecret . implode(',', $data)));

        //if the hashes don't match the data is malformed
        return $oldHash == $newHash;
    }


    /**
     * Get existing entity or create a new one
     *
     * @param $fullClassName string Full classname(with namespace) of the entity. e.g. AppBundle\\Entity\\Product\\StandardProduct
     *
     * @return null|object
     */
    protected function getEntityObject($fullClassName)
    {
        $entityObject = $this
            ->entityManager
            ->getRepository($fullClassName)
            ->findOneBy(["necktieId" => $this->parametersArray["id"]]);

        if($entityObject)
        {
            return $entityObject;
        }

        $entityClass = new \ReflectionClass($fullClassName);

        return $entityClass->newInstanceArgs()->setNecktieId($this->parametersArray["id"]);
    }
}