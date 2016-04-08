<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Notification;

use Trinity\NotificationBundle\Annotations\Methods;
use Trinity\NotificationBundle\Exception\NotificationException;
use Trinity\NotificationBundle\Exception\SourceException;
use Trinity\NotificationBundle\Notification\AnnotationsUtils;


/**
 * Class NotificationUtils.
 */
class NotificationUtils
{
    /** @var  AnnotationsUtils */
    private $annotationsUtils;


    /**
     * NotificationUtils constructor.
     *
     * @param AnnotationsUtils $annotationsUtils
     */
    public function __construct($annotationsUtils)
    {
        $this->annotationsUtils = $annotationsUtils;
    }


    /**
     * Check GET, POST, PUT, ...
     *
     * @param object $entity
     * @param string $method
     *
     * @return bool
     */
    public function hasHTTPMethod($entity, $method)
    {
        /** @var Methods $classAnnotation */
        $classAnnotation = $this->annotationsUtils->getEntityAnnotation(
            $entity,
            AnnotationsUtils::ANNOTATION_METHOD_CLASS
        );
        if ($classAnnotation === null) {
            return true;
        }

        return $classAnnotation->hasType($method);
    }


    /**
     * @param object $entity
     *
     * @return bool
     * @throws NotificationException
     */
    public function isNotificationEntity($entity)
    {
        $class = $this->annotationsUtils->getEntityClass($entity);

        if (in_array('\Trinity\NotificationBundle\Entity\NotificationEntityInterface', class_implements($class))) {
            throw new NotificationException("Notification entity($class) must be extended via NotificationEntityInterface.");
        }

        $reflectionObject = new \ReflectionClass($class);
        $classSourceAnnotation = $this->annotationsUtils->getReader()->getClassAnnotation(
            $reflectionObject,
            AnnotationsUtils::ANNOTATION_CLASS
        );

        return ($classSourceAnnotation !== null);
    }


    /**
     * @param object $entity
     * @param null $method
     *
     * @return mixed|null|string
     */
    public function getUrlPostfix($entity, $method = null)
    {
        $annotations = $this->annotationsUtils->getClassAnnotations(
            $entity,
            AnnotationsUtils::ANNOTATION_URL_CLASS
        );
        $postfix = null;

        if (!empty($annotations)) {
            if ($method === null) {
                foreach ($annotations as $annotation) {
                    if ($annotation->isWithoutMethods()) {
                        $postfix = $annotation->getPostfix();
                        break;
                    }
                }
            } else {
                foreach ($annotations as $annotation) {
                    if ($annotation->hasMethod($method)) {
                        $postfix = $annotation->getPostfix();
                    }
                }
            }
        }

        if ($postfix === null) {
            $reflectionClass = new \ReflectionClass($entity);
            $className = strtolower(preg_replace('/([A-Z])/', '-$1', lcfirst($reflectionClass->getShortName())));
            $postfix = $className;
        }

        $postfix = str_replace('/', '', $postfix);

        return $postfix;
    }


    /**
     * @param object $entity
     * @param string $source
     *
     * @return mixed
     *
     * @throws SourceException
     */
    public function hasSource($entity, $source)
    {
        return $this->annotationsUtils->getClassSourceAnnotation($entity)->hasColumn($source);
    }


    /**
     * @param object $entity
     * @param string $source
     *
     * @return bool
     */
    public function hasDependedSource($entity, $source)
    {
        $annotation = $this->annotationsUtils->getClassDependedSourceAnnotation($entity);
        if ($annotation === null) {
            return false;
        }

        return $annotation->hasColumn($source);
    }


    /**
     * @param string $class
     * @param string $action
     * @param string $annotationClass
     *
     * @return null|object
     */
    public function getControllerActionAnnotation($class, $action, $annotationClass)
    {
        $annotationsSource = null;
        $obj = new \ReflectionClass($class);

        foreach ($obj->getMethods() as $method) {
            if ($action == $method->getName()) {
                $annotationsSource = $this->annotationsUtils->getReader()->getMethodAnnotations($method);
                break;
            }
        }

        $actionAnnotations = [];

        if ($annotationsSource) {
            foreach ($annotationsSource as $annotations) {
                if ($annotations instanceof $annotationClass) {
                    $actionAnnotations[] = $annotations;
                }
            }
        }

        if (!empty($actionAnnotations)) {
            $result = reset($actionAnnotations);
        } else {
            $result = null;
        }

        return $result;
    }


    /**
     * @param object|string $controller
     * @param string $action
     *
     * @return bool
     */
    public function isControllerOrActionDisabled($controller, $action = null)
    {
        $annotations = $this->annotationsUtils->getClassAnnotation(
            $controller,
            AnnotationsUtils::DISABLE_ANNOTATION_CLASS
        );

        if ($annotations !== null) {
            return true;
        }

        return ($this->getControllerActionAnnotation(
            $controller,
            $action,
            AnnotationsUtils::DISABLE_ANNOTATION_CLASS
        )) !== null;
    }
}
