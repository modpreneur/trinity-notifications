<?php
/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Trinity\NotificationBundle\Exception\SourceException;



/**
 * Class AnnotationsUtils
 * @package Trinity\NotificationBundle\Notification
 */
class AnnotationsUtils
{

    const ANNOTATION_CLASS = "\\Trinity\\AnnotationsBundle\\Annotations\\Notification\\Source";
    const ANNOTATION_METHOD_CLASS = "\\Trinity\\AnnotationsBundle\\Annotations\\Notification\\Methods";
    const ANNOTATION_URL_CLASS = "\\Trinity\\AnnotationsBundle\\Annotations\\Notification\\Url";
    const DISABLE_ANNOTATION_CLASS = "\\Trinity\\AnnotationsBundle\\Annotations\\Notification\\DisableNotification";
    const SERIALIZED_NAME = "\\JMS\\Serializer\\Annotation\\SerializedName";
    const FIX_NAMESPACE = "Proxies\\__CG__\\";


    /** @var  AnnotationReader */
    protected $reader;



    /**
     * @param Reader|null $reader
     */
    function __construct(Reader $reader = null)
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
     * @param Object $entity
     *
     * @return string
     */
    public function getEntityClass($entity)
    {
        return str_replace(self::FIX_NAMESPACE, "", get_class($entity));
    }



    /**
     * @param Object $entity
     * @param string $annotationClass
     *
     * @return null|object
     */
    public function getEntityAnnotation($entity, $annotationClass)
    {
        $class = $this->getEntityClass($entity);

        return $this->getClassAnnotation($class, $annotationClass);
    }



    /**
     * @param Object $class
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
     * @param Object $entity
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
     * @param Object $entity
     *
     * @return NULL|object
     * @throws SourceException
     */
    public function getClassSourceAnnotation($entity)
    {
        $classSourceAnnotation = $this->getEntityAnnotation($entity, self::ANNOTATION_CLASS);
        if (!$classSourceAnnotation) {
            throw new SourceException("Entity has not annotations source.");
        }

        return $classSourceAnnotation;
    }

}