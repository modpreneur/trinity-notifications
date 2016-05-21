<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 28.04.16
 * Time: 12:15
 */

namespace Trinity\NotificationBundle\Notification;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Event\BeforePerformEntityChangesEvent;
use Trinity\NotificationBundle\Event\Events;

/**
 * Class EntityConversionHandler
 */
class EntityConversionHandler
{
    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;


    /** @var  FormFactoryInterface */
    protected $formFactory;


    /** @var  array */
    protected $forms;


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
     * EntityConversionHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param FormFactoryInterface     $formFactory
     * @param array                    $forms
     * @param array                    $entities
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FormFactoryInterface $formFactory,
        array $forms,
        array $entities
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->forms = $forms;
        $this->entities = $entities;

        // Replace "_" for "-" in all keys
        foreach ($this->entities as $key => $className) {
            $newKey = str_replace('_', '-', $key);
            unset($this->entities[$key]);
            $this->entities[$newKey] = $className;
        }

        // Replace "_" for "-" in all keys
        foreach ($this->forms as $key => $className) {
            $newKey = str_replace('_', '-', $key);
            unset($this->forms[$key]);
            $this->forms[$newKey] = $className;
        }
    }


    /**
     * @param NotificationEntityInterface $entity
     * @param array                       $data
     *
     * @return NotificationEntityInterface
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function performEntityUpdate(NotificationEntityInterface $entity, array $data):NotificationEntityInterface
    {
        $this->useForm($entity, $data);

        return $entity;
    }


    /**
     * @param string $entityName
     * @param array  $data
     *
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     *
     * @return NotificationEntityInterface
     */
    public function performEntityCreate(string $entityName, array $data):NotificationEntityInterface
    {
        $entity = $this->createEntity($entityName);
        $this->useForm($entity, $data);

        return $entity;

    }


    /**
     * @param NotificationEntityInterface $entity
     *
     * @param array                       $data
     *
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     *
     * @return NotificationEntityInterface
     */
    public function useForm(NotificationEntityInterface $entity, array $data)
    {
        $form = $this->createForm(array_search(get_class($entity), $this->entities, true), $entity);

        /** @var array $keys */
        $keys = array_keys($data);

        //prevent errors when the data has extra fields("This form should not contain extra fields.")
        foreach ($keys as $key) {
            if (!$form->has($key)) {
                unset($data[$key]);
            }
        }

        $form->submit($data, false);

        return $entity;
    }


    /**
     * Create instance of given class
     *
     * @param string $entityName
     *
     * @param array  $constructorArguments
     *
     * @return NotificationEntityInterface
     */
    protected function createEntity(string $entityName, array $constructorArguments = []):NotificationEntityInterface
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
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    protected function createForm(
        string $entityName,
        NotificationEntityInterface $entity,
        array $options = []
    ):FormInterface
    {
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
    protected function getFormClassName(string $entityName):string
    {
        return $this->forms[$entityName];
    }


    /**
     * @param string $entityName
     *
     * @return string
     */
    protected function getEntityClass(string $entityName):string
    {
        return $this->entities[$entityName];
    }
}