<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\MappingException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trinity\NotificationBundle\Event\AfterPerformEntityChangesEvent;
use Trinity\NotificationBundle\Event\BeforePerformEntityChangesEvent;
use Trinity\NotificationBundle\Event\Events;
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


    /**
     * EntityConverter constructor.
     *
     * @param AnnotationsUtils $annotationsUtils
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param string $entityIdFieldName
     * @param bool $isClient
     */
    public function __construct(
        AnnotationsUtils $annotationsUtils,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        $entityIdFieldName = "",
        bool $isClient = false
    )
    {
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
     * @param object $entity
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

            $methodNames = [
                'get' => 'get' . ucfirst($property),
                'is' => 'is' . ucfirst($property),
                'has' => 'has' . ucfirst($property)
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
                        throw new \Exception("No method or property $property.");
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

        $resultArray = $this->processGetMethod($entity, $property, $methodName);

        return $resultArray;
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
            $resultArray[$name] = $resultArray[$name]->format('Y-m-d H:i:s');
        }

        if (is_object($resultArray[$name]) && method_exists($resultArray[$name], 'getId')) {
            $resultArray[$name] = $resultArray[$name]->getId();
        } elseif (is_object($resultArray[$name]) && !method_exists($resultArray[$name], 'getId')) {
            $resultArray[$name] = null;
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

        $resultArray = $this->processGetMethod($entity, $method, $methodName);

        return $resultArray;
    }


    /**
     * Perform entity changes on the given entity
     *
     * @param $entityObject object Doctrine entity
     *
     * @param $data array Data to be applied to entity as named array.
     * @param $ignoredFields array Fields whose will not be applied on the entity.
     *
     * @return object Changed $entityObject
     */
    public function performEntityChanges($entityObject, $data, $ignoredFields)
    {
        // If there are listeners for this event, fire it and get the message from it
        //(it allows changing the entityObject, data and ignoredFields)
        if ($this->eventDispatcher->hasListeners(Events::BEFORE_PERFORM_ENTITY_CHANGES)) {
            $beforePerformEntityChangesEvent = new BeforePerformEntityChangesEvent($entityObject, $data, $ignoredFields);
            /** @var BeforePerformEntityChangesEvent $beforePerformEntityChangesEvent */
            $beforePerformEntityChangesEvent = $this->eventDispatcher->dispatch(Events::BEFORE_PERFORM_ENTITY_CHANGES, $beforePerformEntityChangesEvent);
            $entityObject = $beforePerformEntityChangesEvent->getEntityObject();
            $data = $beforePerformEntityChangesEvent->getData();
            $ignoredFields = $beforePerformEntityChangesEvent->getIgnoredFields();
        }

        //check if the method exists
        //determine the type of the parameter. It will be used for inserting entities(User,Product...)
        //if is it an object:
        //check if the object is DateTime. if so, convert it from string
        //if it is not an datetime, try to find entity with the id
        //if is the entity found, call the set method. continue otherwise
        //if not, just call the set method

        foreach ($data as $propertyName => $propertyValue) {
            //if the property is in the ignored list skip it
            if (in_array($propertyName, $ignoredFields)) {
                $this->logger->info("property in ignored fields:" . $propertyName);
                continue;
            }

//            //If the property contains null skip it.
//            This may cause errors... but it's needed e.g. when setting some properties to null(delete description of product)
//            if ($propertyValue == null) {
//                $this->logger->info("property: ".$propertyName." contains null, skipped");
//                continue;
//            }

            //Rename field "id" to the string which was used in config as "entityIdFieldName".
            if ("id" === $propertyName) {
                $propertyName = $this->entityIdFieldName;

            }

            $methodName = 'set' . ucfirst($propertyName);

            if (!method_exists($entityObject, $methodName)) {
                $this->logger->info("non existing method:" . $methodName);
                continue;
            }

            //Create reflection of the method to get the type(classname) of the first parameter.
            $reflectionMethod = new \ReflectionMethod($entityObject, $methodName);
            $methodParameters = $reflectionMethod->getParameters();

            //Setter method with 0 or 2 or more parameters is weird.
            if (count($methodParameters) != 1) {
                $this->logger->info(
                    "count of method parameters is not an 1 " . count($methodParameters) . "in method: " . $methodName
                );
                continue;
            }

            //Get classname of the first parameter or null.
            $methodParameterClass = $methodParameters[0]->getClass();
            $methodParameterType = (is_object($methodParameterClass)) ? $methodParameterClass->getName() : null;

            //If the method parameter type is DateTime
            if ($methodParameterType == "DateTime") {
                //Convert it to DateTime object
                $propertyValue = \DateTime::createFromFormat("Y-m-d H:i:s", $propertyValue);
                if (!$propertyValue) {
                    $this->logger->info("unsuccessful datetime conversion");
                    continue;
                }
            } //If the method parameter type is doctrine entity.
            else {
                if ($methodParameterType != null && ($doctrineRepository = $this->getEntityRepository(
                        $methodParameterType
                    ))
                ) {
                    //Try to find an object with given server entity id.
                    $propertyValue = $doctrineRepository->findOneBy([$this->entityIdFieldName => $propertyValue]);

                    if (!$propertyValue) {
                        $this->logger->error(
                            "association entity not found " . $methodParameterType . "with id: " . $propertyValue
                        );
                        continue;
                    }
                }
            }

            $this->logger->info("CALL_METHOD: " . $methodName);
            //Call the setter method
            call_user_func_array([$entityObject, $methodName], [$propertyValue]);
        }

        // If there are listeners for this event, fire it and get the message from it
        //(it allows changing the entityObject, data and ignoredFields)
        if ($this->eventDispatcher->hasListeners(Events::AFTER_PERFORM_ENTITY_CHANGES)) {
            $afterPerformEntityChangesEvent = new AfterPerformEntityChangesEvent($entityObject);
            /** @var AfterPerformEntityChangesEvent $afterPerformEntityChangesEvent */
            $afterPerformEntityChangesEvent = $this->eventDispatcher->dispatch(Events::AFTER_PERFORM_ENTITY_CHANGES, $afterPerformEntityChangesEvent);
            $entityObject = $afterPerformEntityChangesEvent->getEntityObject();
        }

        return $entityObject;
    }


    /**
     * Get doctrine repository of given className or null
     *
     * @param $className string Full classname(with namespace) of the entity. e.g. AppBundle\\Entity\\Product\\StandardProduct
     *
     * @return \Doctrine\ORM\EntityRepository|null
     */
    public function getEntityRepository($className)
    {
        try {
            return $this->entityManager->getRepository($className);
        } catch (MappingException $e) {
            return null;
        }
    }


    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
