<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\NotificationBundle\Exception\NotificationException;
use Trinity\NotificationBundle\Exception\SourceException;


/**
 * Class EntityConverter.
 */
class EntityConverter
{
    /** @var  AnnotationsUtils */
    private $annotationsUtils;

    /** @var  LoggerInterface */
    protected $logger;

    /** @var  EntityManager */
    protected $entityManager;

    /** @var EntityConverter */
    protected $entityConverter;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  string Name of the property of a entity which will be mapped to the Id from the notification */
    protected $entityIdFieldName;

    /** @var  bool */
    protected $isClient;

    /** @var  array */
    protected $convertedEntities = [];

    /** @var  array  Entities that were not successfully processed and the next try could fix the problem,
     * e.g. the association entity was not found*/
    protected $unfinishedEntities = [];

    /**
     * EntityConverter constructor.
     *
     * @param AnnotationsUtils         $annotationsUtils
     * @param LoggerInterface          $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $entityIdFieldName
     * @param bool                     $isClient
     */
    public function __construct(
        AnnotationsUtils $annotationsUtils,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        $entityIdFieldName = '',
        bool $isClient = false
    ) {
        $this->annotationsUtils = $annotationsUtils;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->entityIdFieldName = $entityIdFieldName;
        $this->isClient = $isClient;
    }


    /**
     * Return entity convert to array.
     * Property can be rename via SerializedName annotations.
     *
     * [
     *   'id' => 1,
     *   'name' => 'Product name',
     *   'description' => 'Product description'
     * ]
     *
     * @param $entity
     *
     * @return array
     *
     * @throws SourceException
     * @throws \Exception
     */
    public function toArray($entity)
    {
        $entityArray = [];

        /** @var \Trinity\NotificationBundle\Annotations\Source $entityDataSource */
        $entityDataSource = $this->annotationsUtils->getClassSourceAnnotation($entity);
        $columns = $entityDataSource->getColumns();

        $refCLass = new \ReflectionClass($entity);
        if ($entityDataSource->isAllColumnsSelected()) {
            foreach ($refCLass->getProperties() as $prop) {
                $columns[] = $prop->getName();
            }
        }

        foreach ($columns as $property) {
            if ($property === '*') {
                continue;
            }

            $ucFirst = ucfirst($property);
            $methodNames = [
                'get' => 'get' . $ucFirst,
                'is' => 'is' . $ucFirst,
                'has' => 'has' . $ucFirst
            ];

            $array = [];
            // Try all known methods - "getter", "isser" and "hasser"
            foreach ($methodNames as $methodName) {
                try {
                    if (property_exists($entity, $property)) {
                        $array = $this->processProperty($entity, $property, $methodName);
                    } elseif (method_exists($entity, $methodName) || method_exists($entity, $property)) {
                        $array = $this->processMethod($entity, $property, $methodName);
                    } else {
                        throw new NotificationException("No method or property $property.");
                    }

                    // if no exception thrown, the method getting was successful and there is no need to iterate again(most cases)
                    break;
                } catch (\Exception $e) {
                    //if there is an exception, continue with the methods(there is no "getter", so try "isser" and so)
                    continue;
                }
            }

            $entityArray = array_merge(
                $entityArray,
                $array
            );
        }

        return $entityArray;
    }


    /**
     * Transform property to array.
     *
     * ([ 'property-name' => 'property-value' ]).
     *
     *
     * @param object $entity
     * @param string $property
     * @param string $methodName
     *
     * @return array
     */
    private function processProperty($entity, $property, $methodName)
    {
        $reflectionProperty = new \ReflectionProperty($entity, $property);

        $annotation = $this->annotationsUtils->getReader()->getPropertyAnnotation(
            $reflectionProperty,
            AnnotationsUtils::SERIALIZED_NAME
        );

        if ($annotation) {
            $property = $annotation->name;
        }

        return $this->processGetMethod($entity, $property, $methodName);
    }


    /**
     * @param object $entity
     * @param string $name
     * @param string $longName (getName)
     *
     * @return array
     */
    private function processGetMethod($entity, $name, $longName)
    {
        $resultArray[$name] = call_user_func_array([$entity, $longName], []);
        if ($resultArray[$name] instanceof \DateTime) {
            /** @noinspection PhpUndefinedMethodInspection */
            $resultArray[$name] = $resultArray[$name]->format('Y-m-d H:i:s');
        }

        if (is_object($resultArray[$name])) {
            if (method_exists($resultArray[$name], 'getId')) {
                $resultArray[$name] = $resultArray[$name]->getId();
            } else {
                $resultArray[$name] = null;
            }
        }

        return $resultArray;
    }


    /**
     * @param object $entity
     * @param string $method
     * @param string $methodName
     *
     * @return array
     */
    private function processMethod($entity, $method, $methodName)
    {
        if (method_exists($entity, $method)) {
            $methodName = $method;
        }

        $reflectionMethod = new \ReflectionMethod($entity, $methodName);

        $annotation = $this->annotationsUtils->getReader()->getMethodAnnotation(
            $reflectionMethod,
            AnnotationsUtils::SERIALIZED_NAME
        );

        if ($annotation) {
            $method = $annotation->name;
        }

        return $this->processGetMethod($entity, $method, $methodName);
    }


    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
