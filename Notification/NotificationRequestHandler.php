<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.05.16
 * Time: 19:23
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Trinity\Bundle\BunnyBundle\Producer\Producer;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Event\NotificationRequestEvent;
use Trinity\NotificationBundle\EventListener\NotificationEventsListener;
use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;
use Trinity\NotificationBundle\Exception\DataNotValidJsonException;
use Trinity\NotificationBundle\RabbitMQ\ServerProducer;

/**
 * Class NotificationRequestHandler
 *
 * @package Trinity\NotificationBundle\Notification
 */
class NotificationRequestHandler
{
    /** @var  Producer */
    protected $producer;


    /** @var  EntityManagerInterface */
    protected $entityManager;


    /** @var  NotificationManager */
    protected $notificationManager;


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
     * NotificationRequestListener constructor.
     *
     * @param Producer               $producer
     * @param EntityManagerInterface $entityManager
     * @param NotificationManager    $notificationManager
     * @param array                  $entities
     */
    public function __construct(
        Producer $producer,
        EntityManagerInterface $entityManager,
        NotificationManager $notificationManager,
        array $entities
    ) {
        $this->producer = $producer;
        $this->entityManager = $entityManager;
        $this->notificationManager = $notificationManager;
        $this->entities = $entities;

        // Replace "_" for "-" in all keys
        foreach ($this->entities as $key => $className) {
            $newKey = str_replace('_', '-', $key);
            unset($this->entities[$key]);
            $this->entities[$newKey] = $className;
        }
    }


    /**
     * @param AssociationEntityNotFoundException $exception
     */
    public function handleMissingEntityException(AssociationEntityNotFoundException $exception)
    {
        //todo!
        $data['messageType'] = NotificationEventsListener::NOTIFICATION_REQUEST_MESSAGE_TYPE;
        $data['originalMessageUid'] = $exception->getMessageId();
        $data['entityName'] = $exception->getEntityName();
        $data['associationEntityId'] = $exception->getEntityId();
        $data['uid'] = uniqid('', true);
        $data['timestamp'] = (new \DateTime('now'))->getTimestamp();

        $this->producer->publish(json_encode($data));
    }


    /**
     * @param NotificationRequestEvent $event
     *
     * @throws \Trinity\NotificationBundle\Exception\DataNotValidJsonException
     */
    public function handleMissingEntityRequestEvent(NotificationRequestEvent $event)
    {
        //todo!
        $messageDataJson = $event->getMessage()->getRawData();

        /** @var array $notificationsArray */
        $notificationsArray = \json_decode($messageDataJson, true);
        if ($notificationsArray === null) {
            throw new DataNotValidJsonException();
        }

        /** @var Notification[] $notificationsObjects */
        $notificationsObjects = [];
        foreach ($notificationsArray as $item) {
            $notificationsObjects[] = Notification::fromArray($item);
        }

        $data = $notificationsObjects[0]->getData();

        //get association entity name
        //convert it into classname
        $className = $this->entities[$data['entityName']];

        //get the association entity from database
        $entity = $this->entityManager->getRepository($className)->find($data['associationEntityId']);

        //send a notification about the entity
        $this->notificationManager->queueEntity($entity, 'POST', $this->producer instanceof ServerProducer);
        $this->notificationManager->sendBatch();
    }
}