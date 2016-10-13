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
use Trinity\NotificationBundle\Services\EntityAliasTranslator;

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

    /** @var  EntityAliasTranslator */
    protected $entityAliasTranslator;

    /** @var  string */
    protected $entityIdField;

    /**
     * EntityConversionHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param FormFactoryInterface     $formFactory
     * @param EntityConverter          $entityConverter
     * @param EntityAliasTranslator    $aliasTranslator
     * @param array                    $forms
     * @param string                   $entityIdField
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FormFactoryInterface $formFactory,
        EntityConverter $entityConverter,
        EntityAliasTranslator $aliasTranslator,
        array $forms,
        string $entityIdField
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->entityConverter = $entityConverter;
        $this->entityAliasTranslator = $aliasTranslator;
        $this->forms = $forms;
        $this->entityIdField = $entityIdField;
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param string $entityName
     * @param array $data
     * @param array $changeSet
     * @param bool $forceUpdate
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Trinity\NotificationBundle\Exception\UnexpectedEntityStateException
     *
     * @return NotificationEntityInterface
     */
    public function performEntityUpdate(
        NotificationEntityInterface $entity,
        string $entityName,
        array $data,
        array $changeSet,
        bool $forceUpdate = false
    ) : NotificationEntityInterface {
        if (!$forceUpdate) {
            $this->validateCurrentEntityState($entity, $changeSet, $data);
        }

        $this->useForm($entity, $entityName, $data);

        return $entity;
    }

    /**
     * @param string $entityName
     * @param array  $data
     *
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Trinity\NotificationBundle\Exception\EntityAliasNotFoundException
     *
     * @return NotificationEntityInterface
     */
    public function performEntityCreate(string $entityName, array $data) : NotificationEntityInterface
    {
        $entity = $this->createEntity($entityName);
        $this->useForm($entity, $entityName, $data);

        return $entity;
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param string                      $entityName
     * @param array                       $data
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     *
     * @return NotificationEntityInterface
     */
    public function useForm(NotificationEntityInterface $entity, string $entityName, array $data)
    : NotificationEntityInterface
    {
        $form = $this->createForm(
            $entityName,
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
        $this->checkIfFormIsValid($form);

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
     * @param FormInterface $form
     *
     * @throws InvalidDataException
     */
    protected function checkIfFormIsValid(FormInterface $form)
    {
        if (!$form->isValid()) {
            $errorStrings = [];
            foreach ($form->getErrors(true) as $error) {
                $errorStrings[] = $error->getOrigin()->getName().' with cause '.$error->getCause().
                    ' caused message:'.$error->getMessage().'because of invalid value';
            }

            throw new InvalidDataException(implode(';', $errorStrings));
        }
    }

    /**
     * Validate if the current entity state corresponds with the given changeset from the notification.
     *
     * @param NotificationEntityInterface $entity
     * @param array                       $changeSet
     * @param array                       $data
     *
     * @throws UnexpectedEntityStateException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
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
            $entityPropertyValue = $this->entityConverter->getPropertyValue(
                $entity,
                $propertyName === 'id' ? $this->entityIdField : $propertyName
            )[$propertyName];
            $changeSetOldValue = $values['old'];

            //the type unsafe comparision is used intentionally
            /* @noinspection TypeUnsafeComparisonInspection */
            if ($entityPropertyValue != $changeSetOldValue) {
                $violations[$propertyName] = ['expected' => $values['old'], 'actual' => $entityPropertyValue];
            }
        }

        $this->checkViolations($entity, $violations);
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param array                       $violations
     *
     * @throws UnexpectedEntityStateException
     */
    protected function checkViolations(NotificationEntityInterface $entity, array $violations)
    {
        if (count($violations) > 0) {
            $exception = new UnexpectedEntityStateException(
                'Entity of class '.get_class($entity).' with common id '.
                $entity->{'get'.ucfirst($this->entityIdField)}().' has unexpected state.'
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
     *
     * @throws \Trinity\NotificationBundle\Exception\EntityAliasNotFoundException
     */
    protected function createEntity(string $entityName, array $constructorArguments = []) : NotificationEntityInterface
    {
        $entityClass = new \ReflectionClass(
            $this->getEntityClass($entityName)
        );

        /* @var NotificationEntityInterface $entityClass */
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
     *
     * @throws \Trinity\NotificationBundle\Exception\EntityAliasNotFoundException
     */
    protected function getEntityClass(string $entityName) : string
    {
        return $this->entityAliasTranslator->getClassFromAlias($entityName);
    }
}
