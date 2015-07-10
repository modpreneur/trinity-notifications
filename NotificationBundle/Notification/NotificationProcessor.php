<?php

    /*
     * This file is part of the Trinity project.
     *
     */

    namespace Trinity\NotificationBundle\Notification\Annotations;

    use Doctrine\Common\Annotations\AnnotationReader;
    use Doctrine\Common\Annotations\Reader;
    use Trinity\AnnotationsBundle\Annotations\Notification\SourceAnnotation;
    use Trinity\NotificationBundle\Exception\MethodException;
    use Trinity\NotificationBundle\Exception\SourceException;



    class NotificationProcessor {


        const ANNOTATION_CLASS = "\\Trinity\\AnnotationsBundle\\Annotations\\Notification\\Source";
        const ANNOTATION_METHOD_CLASS = "\\Trinity\\AnnotationsBundle\\Annotations\\Notification\\Methods";
        const ANNOTATION_URL_CLASS = "\\Trinity\\AnnotationsBundle\\Annotations\\Notification\\Url";
        const DISABLE_ANNOTATION_CLASS = "\\Trinity\\AnnotationsBundle\\Annotations\\Notification\\DisableNotification";
        const SERIALIZED_NAME = "\\JMS\\Serializer\\Annotation\\SerializedName";
        const FIX_NAMESPACE = "Proxies\\__CG__\\";


        /** @var  AnnotationReader */
        protected $reader;


        function __construct( Reader $reader = NULL ) {
            $this->reader = $reader;

            if($reader === NULL){
                $this->reader = new AnnotationReader();
            }
        }


        /**
         * @param Object $entity
         * @param string $method
         *
         * @return bool
         */
        public function isMethodEnabled( $entity, $method ) {
            /** @var Methods $classAnnotation */
            $classAnnotation = $this->getEntityAnnotation( $entity, self::ANNOTATION_METHOD_CLASS );
            if ( $classAnnotation === null ) {
                return true;
            }

            return $classAnnotation->hasType( $method );
        }


        /**
         * @param Object $entity
         *
         * @return bool
         */
        public function isNotificationEntity( $entity ) {
            $class                 = $this->getEntityClass( $entity );
            $reflectionObject      = new \ReflectionClass( $class );
            $classSourceAnnotation = $this->reader->getClassAnnotation( $reflectionObject,
                SourceAnnotation::ANNOTATION_CLASS );

            return ( $classSourceAnnotation !== null );
        }


        /**
         * @param Object $entity
         *
         * @return string
         */
        private function getEntityClass( $entity ) {
            return str_replace( self::FIX_NAMESPACE, "", get_class( $entity ) );
        }


        /**
         * @param Object $entity
         * @param $annotationClass
         *
         * @return null|object
         */
        public function getEntityAnnotation( $entity, $annotationClass ) {
            $class = $this->getEntityClass( $entity );

            return $this->getClassAnnotation( $class, $annotationClass );
        }


        /**
         * @param string $class
         * @param string $action
         * @param string $annotationClass
         *
         * @return null|object
         */
        public function getControllerActionAnnotation( $class, $action, $annotationClass ) {
            foreach ( $this->getControllerActionAnnotations( $class, $action ) as $annot ) {

                if ( $annot instanceof $annotationClass ) {
                    return $annot;
                }
            }

            return null;
        }


        public function getControllerActionAnnotations( $controller, $action ) {
            $obj = new \ReflectionClass( $controller );

            foreach ( $obj->getMethods() as $method ) {
                if ( $action == $method->getName() ) {
                    return $this->reader->getMethodAnnotations( $method );
                }
            }
        }


        /**
         * @param $class
         * @param $annotationClass
         *
         * @return null|object
         */
        public function getClassAnnotation( $class, $annotationClass ) {
            $reflectionObject = new \ReflectionClass( $class );

            return $this->reader->getClassAnnotation( $reflectionObject, $annotationClass );
        }


        /**
         * @param Object $entity
         * @param $annotationClass
         *
         * @return array
         */
        public function getClassAnnotations( $entity, $annotationClass ) {
            $class            = $this->getEntityClass( $entity );
            $reflectionObject = new \ReflectionClass( $class );
            $annotations      = $this->reader->getClassAnnotations( $reflectionObject );

            $ants = [ ];
            foreach ( $annotations as $annotation ) {
                if ( $annotation instanceof $annotationClass ) {
                    $ants[] = $annotation;
                }
            }

            return $ants;
        }


        /**
         * @param Object $entity
         * @param null $method
         *
         * @return mixed|null|string
         */
        public function getUrlPostfix( $entity, $method = null ) {

            $annotations = $this->getClassAnnotations( $entity, self::ANNOTATION_URL_CLASS );
            $postfix     = null;

            if ( ! empty( $annotations ) ) {

                if ( $method === null ) {
                    foreach ( $annotations as $annotation ) {
                        if ( $annotation->isWithoutMethods() ) {
                            $postfix = $annotation->getPostfix();
                            break;
                        }
                    }
                } else {
                    foreach ( $annotations as $annotation ) {
                        if ( $annotation->hasMethod( $method ) ) {
                            $postfix = $annotation->getPostfix();
                            break;
                        }
                    }
                }
            }

            if ( $postfix === null ) {
                $reflectionClass = new \ReflectionClass( $entity );
                $className       = strtolower( preg_replace( '/([A-Z])/', '-$1',
                    lcfirst( $reflectionClass->getShortName() ) ) );
                $postfix         = $className;
            }

            $postfix = str_replace( "/", "", $postfix );

            return $postfix;
        }


        /**
         * @param Object $entity
         *
         * @return array
         * @throws MethodException
         * @throws SourceException
         */
        public function convertJson( $entity ) {
            $entityArray = [ ];
            $class       = $this->getEntityClass( $entity );

            $rc                    = new \ReflectionClass( $class );

            $classSourceAnnotation = $this->getClassSourceAnnotation( $entity, $class );

            if ( $classSourceAnnotation->hasColumns() ) {

                $columns = $classSourceAnnotation->getColumns();

                if ( $classSourceAnnotation->isAllColumnsSelected() ) {
                    foreach ( $rc->getProperties() as $prop ) {
                        $columns[] = $prop->getName();
                    }
                }

                foreach ( $columns as $property ) {
                    $name = ucfirst( $property );
                    //@todo is method
                    $methodName = "get" . $name;

                    if ( is_callable( array( $entity, $methodName ) ) ) {

                        if ( property_exists( $class, $property ) ) {
                            $reflectionProperty = new \ReflectionProperty( $class, $property );
                            $annotation         = ( $this->reader->getPropertyAnnotation( $reflectionProperty,
                                self::SERIALIZED_NAME ) );
                        } else {
                            $reflectionMethod = new \ReflectionMethod( $class, $methodName );
                            $annotation       = $this->reader->getMethodAnnotation( $reflectionMethod,
                                self::SERIALIZED_NAME );
                        }

                        if ( $annotation ) {
                            $property = $annotation->name;
                        }

                        try {
                            $entityArray[ $property ] = ( call_user_func_array( array( $entity, $methodName ), [ ] ) );
                        } catch ( \Exception $e ) {
                            $entityArray[ $property ] = null;
                        }


                        if ( is_object( $entityArray[ $property ] ) and $entityArray[ $property ] instanceof \DateTime ) {
                            $entityArray[ $property ] = $entityArray[ $property ]->format( 'Y-m-d H:i:s' );
                        } else {
                            if ( is_object( $entityArray[ $property ] ) ) {
                                if ( ! method_exists( $entityArray[ $property ], "getId" ) ) {
                                    throw new MethodException( "Method 'getId' not exists in entity." );
                                }
                                $entityArray[ $property ] = $entityArray[ $property ]->getId();
                            }
                        }
                    }
                }
            }

            return $entityArray;
        }


        /**
         * @param Object $entity
         * @param $class
         *
         * @return null|object
         * @throws SourceException
         */
        public function getClassSourceAnnotation( $entity, $class ) {
            var_dump(
                $this->getClassAnnotations($entity, self::ANNOTATION_CLASS )
            );

            $classSourceAnnotation = $this->getEntityAnnotation( $entity, self::ANNOTATION_CLASS );
            if ( ! $classSourceAnnotation ) {
                throw new SourceException( "Class $class has not annotations source." );
            }

            return $classSourceAnnotation;
        }


        /**
         * @param Object $entity
         * @param $source
         *
         * @return mixed
         * @throws SourceException
         */
        public function hasSource( $entity, $source ) {
            $class                 = $this->getEntityClass( $entity );
            $classSourceAnnotation = $this->getClassSourceAnnotation( $entity, $class );

            return $classSourceAnnotation->hasColumn( $source );
        }


        /**
         * @param string $controller
         * @param string $action
         *
         * @return bool
         */
        public function isControllerOrActionDisabled( $controller, $action = null ) {
            $annotations = $this->getClassAnnotation( $controller, self::DISABLE_ANNOTATION_CLASS );

            if ( $annotations != null ) {
                return true;
            }

            $annotations = $this->getControllerActionAnnotation(
                $controller,
                $action,
                self::DISABLE_ANNOTATION_CLASS );

            return $annotations != null;
        }


    }
