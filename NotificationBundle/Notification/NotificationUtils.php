<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Notification\Annotations;

use Trinity\AnnotationsBundle\Annotations\Notification\Methods;
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
     * @param $annotationsUtils
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
     */
    public function isNotificationEntity($entity)
    {
        $class = $this->annotationsUtils->getEntityClass($entity);
        $reflectionObject = new \ReflectionClass($class);
        $classSourceAnnotation = $this->annotationsUtils->getReader()->getClassAnnotation(
            $reflectionObject,
            AnnotationsUtils::ANNOTATION_CLASS
        );

        return ($classSourceAnnotation !== null);
    }

    /**
     * @param object $entity
     * @param null   $method
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
     * @param $source
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

        //dump(reset($actionAnnotations));

        if (!empty($actionAnnotations)) {
            $result = reset($actionAnnotations);
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * @param string $controller
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
        )) === null;
    }
}
