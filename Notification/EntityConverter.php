<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Exception\NotificationException;

/**
 * Class EntityConverter.
 */
class EntityConverter
{
    const DATETIME_FORMAT = 'Y-m-d\TH:i:s';

    /** @var  AnnotationsUtils */
    private $annotationsUtils;

    /** @var  LoggerInterface */
    protected $logger;

    /** @var  EntityManager */
    protected $entityManager;

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
     * @param array $properties Properties to be included in the result
     *
     * @return array
     *
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     */
    public function toArray(NotificationEntityInterface $entity, array $properties = []) : array
    {
        if (count($properties) === 0) {
            $properties = $this->annotationsUtils->getClassSourceAnnotation($entity)->getColumns();
        }

        $entityArray = [];

        /** @var \Trinity\NotificationBundle\Annotations\Source $entityDataSource */
        $entityDataSource = $this->annotationsUtils->getClassSourceAnnotation($entity);
        $columns = $entityDataSource->getColumns();

        foreach ($columns as $property) {
            if ($property === '*') {
                continue;
            }

            if (!in_array($property, $properties, true)) {
                continue;
            }

            $entityArray = array_merge($entityArray, $this->getPropertyValue($entity, $property));
        }

        return $entityArray;
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param string $property
     *
     * @return array
     *
     * @throws NotificationException
     */
    public function getPropertyValue(NotificationEntityInterface $entity, string $property)
    {
        $ucFirst = ucfirst($property);
        $methodNames = [
            'get' => 'get'.$ucFirst,
            'is' => 'is'.$ucFirst,
            'has' => 'has'.$ucFirst,
        ];

        // Try all known methods - "getter", "isser" and "hasser"
        foreach ($methodNames as $methodName) {
            try {
                if (property_exists($entity, $property)) {
                    return $this->processProperty($entity, $property, $methodName);
                } elseif (method_exists($entity, $methodName) || method_exists($entity, $property)) {
                    return $this->processMethod($entity, $property, $methodName);
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

        throw new NotificationException(
            "Could not get value from the property '$property' of a object of class ".get_class($entity)
        );
    }

    /**
     * Convert given value to scalar.
     *
     * @param \DateTime|object|string|int $value
     *
     * @return null|string
     */
    public function convertToString($value)
    {
        if ($value instanceof \DateTime) {
            /* @noinspection PhpUndefinedMethodInspection */
            $value = $value->format(self::DATETIME_FORMAT);
        }

        if (is_object($value)) {
            $getterMethod = 'get'.ucfirst($this->entityIdFieldName);
            if (method_exists($value, $getterMethod)) {
                $value = $value->{$getterMethod}();
            } else {
                /* @noinspection CallableParameterUseCaseInTypeContextInspection */
                $value = null;
            }
        }

        return $value;
    }

    /**
     * @param $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
    private function processProperty($entity, $property, $methodName) : array
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
    private function processGetMethod($entity, $name, $longName) : array
    {
        $result = call_user_func_array([$entity, $longName], []);

        $resultArray[$name] = $this->convertToString($result);

        return $resultArray;
    }

    /**
     * @param object $entity
     * @param string $method
     * @param string $methodName
     *
     * @return array
     */
    private function processMethod($entity, $method, $methodName) : array
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
}
