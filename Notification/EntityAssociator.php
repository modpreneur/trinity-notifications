<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 28.04.16
 * Time: 18:58
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManagerInterface;
use Trinity\NotificationBundle\Annotations\AssociationSetter;
use Trinity\NotificationBundle\Entity\Association;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;

/**
 * Class EntityAssociator
 */
class EntityAssociator
{
    /** @var  bool */
    protected $isClient;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /** @var  AnnotationsUtils */
    protected $annotationsUtils;

    /** @var  array */
    protected $preparedAssociations = [];

    /** @var  string Name of the field which is mapped to the "id" field in the notification data */
    protected $serverIdField;

    /** @var  string Name of the method which is used to get property with name $serverIdField from entity */
    protected $getServerIdMethod;

    /**
     * @var array Indexed array of entities' aliases and real class names.
     * format:
     * [
     *    "user" => "App\Entity\User,
     *    "product" => "App\Entity\Product,
     *    ....
     * ]
     */
    protected $entities;

    /**
     * EntityAssociator constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param AnnotationsUtils       $annotationsUtils
     * @param bool                   $isClient
     * @param string                 $serverIdField
     * @param array                  $entities
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AnnotationsUtils $annotationsUtils,
        bool $isClient,
        string $serverIdField,
        array $entities
    ) {
        $this->isClient = $isClient;
        $this->entityManager = $entityManager;
        $this->annotationsUtils = $annotationsUtils;
        $this->serverIdField = $serverIdField;
        $this->entities = $entities;
        $this->getServerIdMethod = 'get' . ucfirst($serverIdField);
    }


    /**
     * Associate given entities
     * This action makes sense only when creating entities
     *
     * @param array $entities
     *
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     */
    public function associate(array $entities)
    {
        //set a local variable for convenience(the syntax without it would be more crazier)
        $getServerIdMethod = $this->getServerIdMethod;

        //prepare set of associations for each class - for optimization
        $this->prepareAssociations($entities);

        //associate each entity
        foreach ($entities as $entity) {
            //get entity and it's associations
            $entityAssociations = $this->getPreparedAssociations($entity);
            //for each entity association
            foreach ($entityAssociations as $entityAssociation) {
                $getterMethod = $entityAssociation->getGetterMethod();
                /** @var NotificationEntityInterface $associatedEntity */
                $associatedEntity = $entity->$getterMethod();

                if ($associatedEntity === null) {
                    continue; //do not throw, just continue with the next association
                    //todo: fails when the product does not have a default billing plan.
                    //should log somewhere as error to be viewed by @JakubFajkus

//                    throw new NotificationException(
//                        'Associated entity is null. This should not happen when using data transformer in the form'
//                    );
                }

                //get id of the association entity
                //if the entity has already a id
                //the entity has the primary key - so it existed before the notification came
                if ($associatedEntity->getId() !== null) {
                    continue;
                } elseif ($associatedEntity->$getServerIdMethod() !== null) {
                    //if the associated entity was already in the database
                    $repository = $this->getEntityRepository(get_class($associatedEntity));
                    if ($repository && $persistedEntity = $repository->findOneBy(
                            [$this->serverIdField => $associatedEntity->$getServerIdMethod()]
                        )
                    ) {
                        //call the setter method
                        $entity->{$entityAssociation->getSetterMethod()}($persistedEntity);
                    } else {
                        //associate entity with a entity from the $entities array
                        $this->associateEntity($entity, $entityAssociation, $associatedEntity, $entities);
                    }
                }
            }
        }
    }


    /**
     * Associate given $entity with $associatedEntity.
     *
     * @param NotificationEntityInterface $entity
     * @param Association                 $entityAssociation
     * @param NotificationEntityInterface $associatedEntity
     * @param array                       $entities
     *
     * @throws AssociationEntityNotFoundException
     */
    protected function associateEntity(
        NotificationEntityInterface $entity,
        Association $entityAssociation,
        NotificationEntityInterface $associatedEntity,
        array $entities
    ) {
        $getServerIdMethod = $this->getServerIdMethod;

        foreach ($entities as $entityToAssociate) {
            if (get_class($entityToAssociate) === get_class($associatedEntity) &&
                $entityToAssociate->$getServerIdMethod() === $associatedEntity->$getServerIdMethod()
            ) {
                //it is the same entity
                //call the set method on the $entity, pass the $entityToAssociate
                $entity->{$entityAssociation->getSetterMethod()}($entityToAssociate);
                return;
            }
        }

        //the entity was not associated
        //probably the entityToAssociate does not exist
        $entityName = array_search(get_class($associatedEntity), $this->entities, false);
        $entityId = $associatedEntity->$getServerIdMethod();
        $exception = new AssociationEntityNotFoundException(
            "Association entity '$entityName' with id '$entityId'' was not found"
        );
        $exception->setEntityName($entityName);
        $exception->setEntityId($entityId);

        throw $exception;
    }


    /**
     * Get prepared associations for given entity
     *
     * @param NotificationEntityInterface $entity
     *
     * @return Association[]
     */
    protected function getPreparedAssociations(NotificationEntityInterface $entity) : array
    {
        return $this->preparedAssociations[get_class($entity)];
    }

    /**
     * Get associations of the given $entity and store them.
     *
     * @param NotificationEntityInterface $entity
     */
    protected function prepareEntityAssociations(NotificationEntityInterface $entity)
    {
        $setterMethodsInfo = $this->annotationsUtils->getNotificationSetterMethods($entity);
        $getterMethodsInfo = $this->annotationsUtils->getNotificationGetterMethods($entity);
        $this->preparedAssociations[get_class($entity)] = [];

        foreach ($setterMethodsInfo as $setterMethodInfo) {
            /** @var \ReflectionMethod $setterMethod */
            $setterMethod = $setterMethodInfo['method'];
            /** @var AssociationSetter $setterAnnotation */
            $setterAnnotation = $setterMethodInfo['annotation'];
            $setterParameterType = $setterAnnotation->getTargetEntity();

            //if the annotation was not empty
            if ($setterParameterType !== null) {
                $repository = $this->getEntityRepository($setterParameterType);
                //and the annotation value was classname of an entity
                if ($repository !== null) {
                    //find a getter method
                    foreach ($getterMethodsInfo as $getterMethodInfo) {
                        /** @var \ReflectionMethod $getterMethod */
                        $getterMethod = $getterMethodInfo['method'];

                        $setterName = str_replace('get', 'set', $getterMethod->getName());
                        //if the name without set and get is the same
                        if ($setterName === $setterMethod->getName()) {
                            $this->preparedAssociations[get_class($entity)][] = new Association(
                                $getterMethod->getName(),
                                $setterMethod->getName(),
                                $setterParameterType
                            );
                        }
                    }
                }
            }
        }
    }


    /**
     * Get doctrine repository of given className or null
     *
     * @param $className string Full classname(with namespace) of the entity. e.g.
     *                   AppBundle\\Entity\\Product\\StandardProduct
     *
     * @return \Doctrine\ORM\EntityRepository|null
     */
    protected function getEntityRepository($className)
    {
        try {
            return $this->entityManager->getRepository($className);
        } catch (MappingException $e) {
            return null;
        }
    }


    /**
     * @param array [NotificationEntityInterface] $entities
     */
    protected function prepareAssociations(array $entities)
    {
        foreach ($entities as $entity) {
            $this->prepareEntityAssociations($entity);
        }
    }
}
