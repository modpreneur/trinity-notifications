<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 28.04.16
 * Time: 12:15.
 */
namespace Trinity\NotificationBundle\Notification;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Exception\InvalidDataException;
use Trinity\NotificationBundle\Exception\UnexpectedEntityStateException;

/**
 * Class EntityConversionHandler.
 */
class EntityConversionHandler
{
    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  FormFactoryInterface */
    protected $formFactory;

    /** @var  EntityConverter */
    protected $entityConverter;

    /** @var  array */
    protected $forms;

    /**
     * @var array Indexed array of entities' aliases and real class names.
     *            format:
     *            [
     *            "user" => "App\Entity\User,
     *            "product" => "App\Entity\Product,
     *            ....
     *            ]
     */
    protected $entities;

    /** @var  string */
    protected $entityIdField;

    /**
     * EntityConversionHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param FormFactoryInterface     $formFactory
     * @param EntityConverter          $entityConverter
     * @param array                    $forms
     * @param array                    $entities
     * @param string                   $entityIdField
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FormFactoryInterface $formFactory,
        EntityConverter $entityConverter,
        array $forms,
        array $entities,
        string $entityIdField
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->entityConverter = $entityConverter;
        $this->forms = $forms;
        $this->entities = $entities;
        $this->entityIdField = $entityIdField;
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param array                       $data
     * @param array                       $changeSet
     * @param bool                        $forceUpdate
     *
     * @return NotificationEntityInterface
     *
     * @throws \Trinity\NotificationBundle\Exception\UnexpectedEntityStateException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function performEntityUpdate(
        NotificationEntityInterface $entity,
        array $data,
        array $changeSet,
        bool $forceUpdate = false
    ) : NotificationEntityInterface {
        if (!$forceUpdate) {
            $this->validateCurrentEntityState($entity, $changeSet, $data);
        }

        $this->useForm($entity, $data);

        return $entity;
    }

    /**
     * @param string $entityName
     * @param array  $data
     *
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     *
     * @return NotificationEntityInterface
     */
    public function performEntityCreate(string $entityName, array $data) : NotificationEntityInterface
    {
        $entity = $this->createEntity($entityName);
        $this->useForm($entity, $data);

        return $entity;
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param array                       $data
     *
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     *
     * @return NotificationEntityInterface
     *
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     */
    public function useForm(NotificationEntityInterface $entity, array $data) : NotificationEntityInterface
    {
        $form = $this->createForm(
            array_search(
                str_replace('Proxies\__CG__\\', '', get_class($entity)),
                $this->entities,
                true
            ),
            $entity
        );

        /** @var array $keys */
        $keys = array_keys($data);

        //prevent errors when the data has extra fields("This form should not contain extra fields.")
        foreach ($keys as $key) {
            if (!$form->has($key)) {
                unset($data[$key]);
            }
        }

        $form->submit($data, false);

        if (!$form->isValid()) {
            $errorStrings = [];
            foreach ($form->getErrors(true) as $error) {
                $errorStrings[] = $error->getOrigin()->getName().' with cause '.$error->getCause().
                    ' caused message:'.$error->getMessage().'because of invalid value';
            }

            throw new InvalidDataException(implode(';', $errorStrings));
        }

        return $entity;
    }

    /**
     * When the changeset is empty there is need to create a fake one. It will contain no changes.
     * This is required because of validation against the database.
     * Without the changeset there would not be possibility to compare it to the database data.
     *
     * @param array $entityArray
     *
     * @return array
     */
    public function createFakeChangeset(array $entityArray)
    {
        $changeset = [];

        foreach ($entityArray as $property => $value) {
            $changeset[$property] = ['old' => $value, 'new' => $value];
        }

        return $changeset;
    }

    /**
     * Validate if the current entity state corresponds with the given changeset from the notification.
     *
     * @param NotificationEntityInterface $entity
     * @param array                       $changeSet
     * @param array                       $data
     *
     * @throws UnexpectedEntityStateException
     */
    protected function validateCurrentEntityState(NotificationEntityInterface $entity, array $changeSet, array $data)
    {
        if (count($changeSet) === 0) {
            $changeSet = $this->createFakeChangeset($data);
        }

        //the changeset is in the format: ['propertyName' => ['old' => 'old-value', 'new' => 'new-value']
        //iterate over the changeset and check if the entity's properties do match with the old changeset values
        //in the standard flow the entity has not been changed yet

        $violations = [];
        foreach ($changeSet as $propertyName => $values) {
            $entityPropertyValue = $this->entityConverter
                ->getPropertyValue($entity, $propertyName === 'id' ? $this->entityIdField : $propertyName)[$propertyName];
            $changeSetOldValue = $values['old'];

            //the type unsafe comparision is used intentionally
            if ($entityPropertyValue != $changeSetOldValue) {
                $violations[$propertyName] = ['expected' => $values['old'], 'actual' => $entityPropertyValue];
            }
        }

        if (count($violations) > 0) {
            $exception = new UnexpectedEntityStateException(
                'Entity of class '.get_class($entity).' with common id '.$entity->{'get'.ucfirst($this->entityIdField)}()
                .' has unexpected state.'
            );
            $exception->setViolations($violations);
            $exception->setEntity($entity);

            throw $exception;
        }
    }

    /**
     * Create instance of given class.
     *
     * @param string $entityName
     * @param array  $constructorArguments
     *
     * @return NotificationEntityInterface
     */
    protected function createEntity(string $entityName, array $constructorArguments = []) : NotificationEntityInterface
    {
        $entityClass = new \ReflectionClass(
            $this->getEntityClass($entityName)
        );

        return $entityClass->newInstanceArgs($constructorArguments);
    }

    /**
     * @param string                      $entityName
     * @param NotificationEntityInterface $entity
     * @param array                       $options
     *
     * @return FormInterface
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    protected function createForm(
        string $entityName,
        NotificationEntityInterface $entity,
        array $options = []
    ) : FormInterface {
        return $this->formFactory->create(
            $this->getFormClassName($entityName),
            $entity,
            $options
        );
    }

    /**
     * @param string $entityName
     *
     * @return string
     */
    protected function getFormClassName(string $entityName) : string
    {
        return $this->forms[$entityName];
    }

    /**
     * @param string $entityName
     *
     * @return string
     */
    protected function getEntityClass(string $entityName) : string
    {
        return $this->entities[$entityName];
    }
}
