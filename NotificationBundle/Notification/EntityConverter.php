<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Trinity\NotificationBundle\Exception\SourceException;

/**
 * Class EntityConverter.
 */
class EntityConverter
{
    /** @var  AnnotationsUtils */
    private $annotationsUtils;

    /**
     * EntityConverter constructor.
     *
     * @param AnnotationsUtils $annotationsUtils
     */
    public function __construct(AnnotationsUtils $annotationsUtils)
    {
        $this->annotationsUtils = $annotationsUtils;
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
            $methodName = 'get'.ucfirst($property);

            if ($property === '*') {
                continue;
            }

            if (property_exists($entity, $property)) {
                $array = $this->processProperty($entity, $property, $methodName);
            } elseif (method_exists($entity, $methodName) || method_exists($entity, $property)) {
                $array = $this->processMethod($entity, $property, $methodName);
            } else {
                throw new \Exception("No method or property $property.");
            }

            $entityArray = array_merge(
                $entityArray,
                $array
            );
        }

        return $entityArray;
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
     * @param object $entity
     * @param string $name
     * @param string $longName (getName)
     *
     * @return array
     */
    private function processGetMethod($entity, $name, $longName)
    {
        try {
            $resultArray[$name] = call_user_func_array(array($entity, $longName), []);
            if ($resultArray[$name] instanceof \DateTime) {
                $resultArray[$name] = $resultArray[$name]->format('Y-m-d H:i:s');
            }
        } catch (\Exception $ex) {
            $resultArray[$name] = null;
        }

        return $resultArray;
    }
}
