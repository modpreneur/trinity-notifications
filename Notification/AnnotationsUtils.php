<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Trinity\NotificationBundle\Annotations\AssociationGetter;
use Trinity\NotificationBundle\Annotations\AssociationSetter;
use Trinity\NotificationBundle\Exception\SourceException;

/**
 * Class AnnotationsUtils.
 */
class AnnotationsUtils
{
    const ANNOTATION_CLASS = '\\Trinity\\NotificationBundle\\Annotations\\Source';
    const ANNOTATION_METHOD_CLASS = '\\Trinity\\NotificationBundle\\Annotations\\Methods';
    const ANNOTATION_URL_CLASS = '\\Trinity\\NotificationBundle\\Annotations\\Url';
    const DISABLE_ANNOTATION_CLASS = '\\Trinity\\NotificationBundle\\Annotations\\DisableNotification';
    const SERIALIZED_NAME = '\\JMS\\Serializer\\Annotation\\SerializedName';
    const FIX_NAMESPACE = 'Proxies\\__CG__\\';
    const ANNOTATION_DEPENDED_CLASS = '\\Trinity\\NotificationBundle\\Annotations\\DependentSources';

    /** @var  AnnotationReader|Reader */
    protected $reader;

    /**
     * @param Reader|null $reader
     */
    public function __construct(Reader $reader = null)
    {
        $this->reader = $reader;

        if ($reader === null) {
            $this->reader = new AnnotationReader();
        }
    }

    /**
     * @return AnnotationReader
     */
    public function getReader() : AnnotationReader
    {
        return $this->reader;
    }

    /**
     * @param object $entity
     *
     * @return string
     */
    public function getEntityClass($entity) : string
    {
        return str_replace(self::FIX_NAMESPACE, '', get_class($entity));
    }

    /**
     * @param object $entity
     * @param string $annotationClass
     *
     * @return null|object
     */
    public function getEntityAnnotation($entity, $annotationClass)
    {
        return $this->getClassAnnotation($entity, $annotationClass);
    }

    /**
     * @param object $class
     * @param string $annotationClass
     *
     * @return null|object
     */
    public function getClassAnnotation($class, $annotationClass)
    {
        $reflectionObject = new \ReflectionClass($class);

        return $this->reader->getClassAnnotation($reflectionObject, $annotationClass);
    }

    /**
     * @param object $entity
     * @param string $annotationClass
     *
     * @return array
     */
    public function getClassAnnotations($entity, $annotationClass) : array
    {
        $class = $this->getEntityClass($entity);
        $reflectionObject = new \ReflectionClass($class);
        $annotations = $this->reader->getClassAnnotations($reflectionObject);

        $ants = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationClass) {
                $ants[] = $annotation;
            }
        }

        return $ants;
    }

    /**
     * @param object $entity
     *
     * @return null|object
     *
     * @throws SourceException
     */
    public function getClassSourceAnnotation($entity)
    {
        $classAnn = $this->getEntityAnnotation($this->getEntityClass($entity), self::ANNOTATION_CLASS);

        if (!$classAnn) {
            throw new SourceException('Entity('.get_class($entity).') has not annotations source.');
        }

        return $classAnn;
    }

    /**
     * @param object $entity
     *
     * @return null|object
     */
    public function getClassDependedSourceAnnotation($entity)
    {
        return $this->getEntityAnnotation($entity, self::ANNOTATION_DEPENDED_CLASS);
    }

    /**
     * @param $entity
     *
     * @return array Array of arrays with items('method' => \ReflectionMethod, 'annotation' => Annotation)
     */
    public function getNotificationSetterMethods($entity) : array
    {
        return $this->getClassMethodsWithAnnotation($entity, AssociationSetter::class);
    }

    /**
     * @param $entity
     *
     * @return array Array of arrays with items('method' => \ReflectionMethod, 'annotation' => Annotation)
     */
    public function getNotificationGetterMethods($entity) : array
    {
        return $this->getClassMethodsWithAnnotation($entity, AssociationGetter::class);
    }

    /**
     * @param        $entity
     * @param string $annotationClass
     *
     * @return array Array of arrays with items('method' => \ReflectionMethod, 'annotation' => Annotation)
     */
    protected function getClassMethodsWithAnnotation($entity, string $annotationClass) : array
    {
        $return = [];
        $class = $this->getEntityClass($entity);
        $reflectionClass = new \ReflectionClass($class);
        $classMethods = $reflectionClass->getMethods();

        foreach ($classMethods as $method) {
            /** @var Annotation[] $methodAnnotations */
            $methodAnnotations = $this->reader->getMethodAnnotations($method);
            if (count($methodAnnotations) > 0) {
                foreach ($methodAnnotations as $methodAnnotation) {
                    if ($methodAnnotation instanceof $annotationClass) {
                        $return[] = ['method' => $method, 'annotation' => $methodAnnotation];
                    }
                }

//
            }
        }

        return $return;
    }
}
