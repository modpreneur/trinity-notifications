<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
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
    const ANNOTATION_DEPENDED_CLASS = '\\Trinity\\NotificationBundle\\Annotations\\DependedSource';

    /** @var  AnnotationReader */
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
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @param object $entity
     *
     * @return string
     */
    public function getEntityClass($entity)
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
        //$class = $this->getEntityClass($entity);

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
    public function getClassAnnotations($entity, $annotationClass)
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
     * @return NULL|object
     *
     * @throws SourceException
     */
    public function getClassSourceAnnotation($entity)
    {
        $classAnn = $this->getEntityAnnotation($entity, self::ANNOTATION_CLASS);
        if (!$classAnn) {
            throw new SourceException('Entity has not annotations source.');
        }

        return $classAnn;
    }


    /**
     * @param object $entity
     *
     * @return NULL|object
     *
     */
    public function getClassDependedSourceAnnotation($entity)
    {
        $classAnn = $this->getEntityAnnotation($entity, self::ANNOTATION_DEPENDED_CLASS);
        return $classAnn;
    }
}
