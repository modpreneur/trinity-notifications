<?php
    namespace Trinity\NotificationBundle\Notification;


    use JMS\Serializer\Annotation\SerializedName;
    use Trinity\NotificationBundle\Exception\MethodException;
    use Trinity\NotificationBundle\Exception\SourceException;



    class EntityConverter
    {

        /** @var  AnnotationsUtils */
        private $annotationsUtils;



        /**
         * EntityConverter constructor.
         * @param AnnotationsUtils $annotationsUtils
         */
        public function __construct(AnnotationsUtils $annotationsUtils)
        {
            $this->annotationsUtils = $annotationsUtils;
        }



        /**
         *
         * Transform property to array.
         *
         * ([ 'property-name' => 'property-value' ]).
         *
         *
         * @param $entity
         * @param $property
         * @param $methodName
         * @return array
         */
        private function processProperty($entity, $property, $methodName){
            $resultArray = [];
            $reflectionProperty = new \ReflectionProperty($entity, $property);

            $annotation = $this->annotationsUtils
                ->getReader()
                ->getPropertyAnnotation(
                    $reflectionProperty,
                    AnnotationsUtils::SERIALIZED_NAME
                );

            if ($annotation) {
                $property = $annotation->name;
            }

            try {
                $resultArray[$property] = call_user_func_array(array($entity, $methodName), []);

                if ($resultArray[$property] instanceof \DateTime) {
                    $resultArray[$property] = $resultArray[$property]->format('Y-m-d H:i:s');
                }
            } catch (\Exception $ex) {
                $resultArray[$property] = NULL;
            }

            return $resultArray;
        }


        /**
         *
         * Return entity convert to array.
         * Property can be rename via SerializedName annotations.
         *
         * [
         *   'id' => 1,
         *   'name' => 'Product name',
         *   'description' => 'Product description'
         * ]
         *
         * @param Object $entity
         *
         * @return array
         * @throws MethodException
         * @throws SourceException
         */
        public function toArray($entity)
        {
            $entityArray = [];

            /** @var \Trinity\AnnotationsBundle\Annotations\Notification\Source $entityDataSource */
            $entityDataSource = $this->annotationsUtils->getClassSourceAnnotation($entity);
            $columns = $entityDataSource->getColumns();

            $rc = new \ReflectionClass($entity);
            if ($entityDataSource->isAllColumnsSelected()) {
                foreach ($rc->getProperties() as $prop) {
                    $columns[] = $prop->getName();
                }
            }

            $methods = get_class_methods($entity);

            foreach ($columns as $property) {
                $methodName = "get" . ucfirst($property);
                if(in_array($methodName, $methods)) {
                    $entityArray = array_merge($entityArray, $this->processProperty($entity, $property, $methodName, $entityArray));
                }
            }

            return $entityArray;
        }

    }