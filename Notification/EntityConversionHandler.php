<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 28.04.16
 * Time: 12:15.
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Kernel;
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
     *              "user" => "App\Entity\User,
     *              "product" => "App\Entity\Product,
     *              ...
     *            ]
     */
    protected $entities;

    /** @var  string */
    protected $entityIdField;

    /** @var bool */
    protected $disableEntityStateViolations;

    /**
     * EntityConversionHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param FormFactoryInterface     $formFactory
     * @param EntityConverter          $entityConverter
     * @param array                    $forms
     * @param array                    $entities
     * @param string                   $entityIdField
     * @param bool                     $disableEntityStateViolations
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FormFactoryInterface $formFactory,
        EntityConverter $entityConverter,
        array $forms,
        array $entities,
        string $entityIdField,
        bool $disableEntityStateViolations
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->entityConverter = $entityConverter;
        $this->forms = $forms;
        $this->entities = $entities;
        $this->entityIdField = $entityIdField;
        $this->disableEntityStateViolations = $disableEntityStateViolations;
    }

    /**
     * @param NotificationEntityInterface $entity
     * @param array                       $data
     * @param array                       $changeSet
     * @param bool                        $forceUpdate
     * @param string                      $entityName
     *
     * @return NotificationEntityInterface
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Trinity\NotificationBundle\Exception\UnexpectedEntityStateException
     * @throws \Trinity\NotificationBundle\Exception\InvalidDataException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function performEntityUpdate(
        NotificationEntityInterface $entity,
        array $data,
        array $changeSet,
        bool $forceUpdate = false,
        string $entityName
    ): NotificationEntityInterface {
        $form = $this->createFormForEntity($entity, $entityName);
        $data = $this->removeExtraNotificationData($data, $form);

        if (!$forceUpdate && !$this->disableEntityStateViolations) {
            $this->validateCurrentEntityState($entity, $changeSet, $data);
        }

        $this->useForm($entity, $form, $data);

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
     *
     * @throws \ReflectionException
     */
    public function performEntityCreate(string $entityName, array $data): NotificationEntityInterface
    {
        $entity = $this->createEntity($entityName);
        $form = $this->createFormForEntity($entity, $entityName);
        $data = $this->removeExtraNotificationData($data, $form);

        $this->useForm($entity, $form, $data);

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
    protected function useForm(NotificationEntityInterface $entity, FormInterface $form, array $data): NotificationEntityInterface
    {
        $form->submit($data, false);

        if (!$form->isValid()) {
            $errorStrings = [];
            foreach ($form->getErrors(true) as $error) {
                $errorStrings[] = 'Validation of entity of class ' . get_class($entity) . ' with id ' . $data['id'] .
                    ' failed for field: ' . $error->getOrigin()->getName() . ' with cause ' . $error->getCause() .
                    ' caused message:' . $error->getMessage() . ' because of invalid value';
            }

            throw new InvalidDataException(implode(';', $errorStrings));
        }

        return $entity;
    }

    /**
     * Create a form for the given entity
     *
     * @param NotificationEntityInterface $entity
     * @param string $entityName
     * @return FormInterface
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    protected function createFormForEntity(NotificationEntityInterface $entity, string $entityName): FormInterface
    {
        return $this->createForm(
            $entityName,
            $entity
        );
    }

    /**
     * Creates and returns a new array without the fields, that are not in the list of form inputs.
     *
     * The original array is not changed
     *
     * @param array         $data
     * @param FormInterface $form
     *
     * @return array
     */
    protected function removeExtraNotificationData(array $data, FormInterface $form): array
    {
        /** @var array $keys */
        $keys = array_keys($data);

        //prevent errors when the data has extra fields("This form should not contain extra fields.")
        foreach ($keys as $key) {
            if (!$form->has($key)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Validate if the current entity state corresponds with the given changeset from the notification.
     *
     * @param NotificationEntityInterface $entity
     * @param array                       $changeSet Changeset in format: ['propertyName' => ['old' => 'old-value',
     *     'new' => 'new-value']`
     * @param array                       $data Notification data
     *
     * @throws UnexpectedEntityStateException
     * @throws \Trinity\NotificationBundle\Exception\NotificationException
     */
    protected function validateCurrentEntityState(NotificationEntityInterface $entity, array $changeSet, array $data)
    {
        if (count($changeSet) === 0) {
            //there is nothing to do or validate, the original entity state is gone
            return;
        }

        //iterate over the changeSet and check if the entity's properties do match with the old changeSet values
        //in the standard flow the entity has not been changed yet

        $violations = [];
        foreach ($changeSet as $propertyName => $values) {

            //skip the properties which are not in the data array
            //validating the ignored properties causes errors
            if (!array_key_exists($propertyName, $data)) {
                continue;
            }

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
                'Entity of class ' . get_class($entity) . ' with common id ' . $entity->{'get' . ucfirst($this->entityIdField)}()
                . ' has unexpected state.'
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
     * @throws \ReflectionException
     */
    protected function createEntity(string $entityName, array $constructorArguments = []): NotificationEntityInterface
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
    ): FormInterface {
        // since 2.8, the form creation is different
        // see http://symfony.com/doc/2.7/forms.html#creating-form-classes and
        // http://symfony.com/doc/2.8/forms.html#creating-form-classes
        //so, require that the form class is registered as a serviceRequire tha
        $class = $this->getFormClassName($entityName);

        return $this->formFactory->create($class, $entity, $options);
    }

    /**
     * @param string $entityName
     *
     * @return string
     */
    protected function getFormClassName(string $entityName): string
    {
        return $this->forms[$entityName];
    }

    /**
     * @param string $entityName
     *
     * @return string
     */
    protected function getEntityClass(string $entityName): string
    {
        return $this->entities[$entityName];
    }
}
