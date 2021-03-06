<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.05.16
 * Time: 19:23.
 */
namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Trinity\Bundle\MessagesBundle\Exception\DataNotValidJsonException;
use Trinity\Bundle\MessagesBundle\Interfaces\SecretKeyProviderInterface;
use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\Bundle\MessagesBundle\Sender\MessageSender;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\NotificationRequest;
use Trinity\NotificationBundle\Entity\NotificationRequestMessage;
use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;

/**
 * Class NotificationRequestHandler.
 */
class NotificationRequestHandler
{
    /** @var  MessageSender */
    protected $messageSender;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /** @var  EntityConverter */
    protected $entityConverter;

    /** @var  NotificationUtils */
    protected $notificationUtils;

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

    /** @var  bool */
    protected $isClient;

    /**
     * NotificationRequestListener constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param MessageSender          $messageSender
     * @param EntityConverter        $entityConverter
     * @param NotificationUtils      $notificationUtils
     * @param array                  $entities
     * @param bool                   $isClient
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MessageSender $messageSender,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        array $entities,
        bool $isClient
    ) {
        $this->messageSender = $messageSender;
        $this->entityManager = $entityManager;
        $this->entityConverter = $entityConverter;
        $this->notificationUtils = $notificationUtils;
        $this->entities = $entities;
        $this->isClient = $isClient;
    }

    /**
     * @param AssociationEntityNotFoundException $exception
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\DataNotValidJsonException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     */
    public function handleMissingEntityException(AssociationEntityNotFoundException $exception)
    {
        $requestMessage = $exception->getMessageObject();
        /** @var array $requestMessageData */
        $requestMessageData = \json_decode($requestMessage->getJsonData(), true);
        $notifications = [];

        if (!is_array($requestMessageData)) {
            throw new DataNotValidJsonException();
        }

        foreach ($requestMessageData as $item) {
            $notifications[] = Notification::fromArray($item);
        }

        $responseMessage = new NotificationRequestMessage();
        $responseMessage->setType(NotificationRequestMessage::MESSAGE_TYPE);
        $responseMessage->setParentMessageUid($requestMessage->getUid());
        $responseMessage->setClientId($requestMessage->getClientId());
        $responseMessage->setPreviousNotifications($notifications);

        $notificationRequest = new NotificationRequest($exception->getEntityName(), $exception->getEntityId());
        $responseMessage->setRequest($notificationRequest);
        $responseMessage->setDestination($requestMessage->getSender());

        $this->messageSender->sendMessage($responseMessage);
    }

    /**
     * @param Message $message
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     */
    public function handleMissingEntityRequestMessage(Message $message)
    {
        $requestMessage = NotificationRequestMessage::createFromMessage($message);

        //get association entity name
        //convert it into classname
        $className = $this->entities[$requestMessage->getRequest()->getEntityName()];

        //get the association entity from database
        /** @var NotificationEntityInterface $entity */
        $entity = $this->entityManager->getRepository($className)->find(
            $requestMessage->getRequest()->getAssociationEntityId()
        );

        $this->sendNotificationResponse($requestMessage, $entity);
    }

    /**
     * Send response for the notification request message.
     *
     * @param NotificationRequestMessage  $message
     * @param NotificationEntityInterface $entity
     *
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageDestinationException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSendMessageListenerException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingClientIdException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingSecretKeyException
     * @throws \Trinity\Bundle\MessagesBundle\Exception\MissingMessageUserException
     */
    protected function sendNotificationResponse(
        NotificationRequestMessage $message,
        NotificationEntityInterface $entity
    ) {
        //convert entity to array
        $entityArray = $this->entityConverter->toArray($entity);

        $responseMessage = new NotificationBatch();
        $responseMessage->setClientId($message->getClientId());
        $responseMessage->setParentMessageUid($message->getUid());
        $responseMessage->setType(NotificationBatch::MESSAGE_TYPE);

        $notification = new Notification();
        $notification->setData($entityArray);
        $notification->setMethod('POST');
        $notification->setMessageId($responseMessage->getUid());
        //todo: should not be always false, but the error system should be refactored anyway
        $notification->setIsForced(false);
        //get entity "name", e.g. "product", "user"
        $notification->setEntityName($this->notificationUtils->getUrlPostfix($entity));

        $responseMessage->addNotification($notification);
        $responseMessage->addNotifications($message->getPreviousNotifications());

        if (!$this->isClient) {
            $responseMessage->setDestination('client_'.$message->getClientId());
        }

        //send a notification about the entity
        $this->messageSender->sendMessage($responseMessage);
    }
}
