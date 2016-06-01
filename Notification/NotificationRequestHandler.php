<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.05.16
 * Time: 19:23
 */

namespace Trinity\NotificationBundle\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Trinity\NotificationBundle\Entity\Message;
use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationBatch;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Entity\NotificationRequest;
use Trinity\NotificationBundle\Entity\NotificationRequestMessage;
use Trinity\NotificationBundle\Event\NotificationRequestEvent;
use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;
use Trinity\NotificationBundle\Exception\DataNotValidJsonException;
use Trinity\NotificationBundle\Interfaces\ClientSecretProviderInterface;
use Trinity\NotificationBundle\Message\MessageManager;

/**
 * Class NotificationRequestHandler
 *
 * @package Trinity\NotificationBundle\Notification
 */
class NotificationRequestHandler
{
    /** @var  MessageManager */
    protected $messageManager;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /** @var ClientSecretProviderInterface */
    protected $clientSecretProvider;

    /** @var  EntityConverter */
    protected $entityConverter;

    /** @var  NotificationUtils */
    protected $notificationUtils;

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


    /** @var  bool */
    protected $isClient;


    /**
     * NotificationRequestListener constructor.
     *
     * @param MessageManager         $messageManager
     * @param EntityManagerInterface $entityManager
     * @param MessageManager         $messageManager
     * @param EntityConverter        $entityConverter
     * @param NotificationUtils      $notificationUtils
     * @param array                  $entities
     * @param bool                   $isClient
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MessageManager $messageManager,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        array $entities,
        bool $isClient
    ) {
        $this->messageManager = $messageManager;
        $this->entityManager = $entityManager;
        $this->entityConverter = $entityConverter;
        $this->notificationUtils = $notificationUtils;
        $this->entities = $entities;
        $this->isClient = $isClient;
    }


    /**
     * @param AssociationEntityNotFoundException $exception
     *
     * @throws \Trinity\NotificationBundle\Exception\MissingClientIdException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientSecretException
     * @throws \Trinity\NotificationBundle\Exception\MissingMessageTypeException
     * @throws \Trinity\NotificationBundle\Exception\DataNotValidJsonException
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
        $responseMessage->setClientSecret($this->clientSecretProvider->getClientSecret($requestMessage->getClientId()));
        $responseMessage->setPreviousNotifications($notifications);

        $notificationRequest = new NotificationRequest($exception->getEntityName(), $exception->getEntityId());
        $responseMessage->setRequest($notificationRequest);

        $this->messageManager->sendMessage($responseMessage, 'message');
    }


    /**
     * @param NotificationRequestEvent $event
     *
     * @throws \Trinity\NotificationBundle\Exception\DataNotValidJsonException
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientIdException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientSecretException
     * @throws \Trinity\NotificationBundle\Exception\MissingMessageTypeException
     */
    public function handleMissingEntityRequestEvent(NotificationRequestEvent $event)
    {
        $requestMessage = NotificationRequestMessage::createFromMessage($event->getMessage());

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
     * @param ClientSecretProviderInterface $clientSecretProvider
     */
    public function setClientSecretProvider(ClientSecretProviderInterface $clientSecretProvider)
    {
        $this->clientSecretProvider = $clientSecretProvider;
    }


    /**
     * Send response for the notification request message
     *
     * @param NotificationRequestMessage  $message
     * @param NotificationEntityInterface $entity
     *
     * @throws \Exception
     * @throws \Trinity\NotificationBundle\Exception\SourceException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientIdException
     * @throws \Trinity\NotificationBundle\Exception\MissingClientSecretException
     * @throws \Trinity\NotificationBundle\Exception\MissingMessageTypeException
     */
    protected function sendNotificationResponse(
        NotificationRequestMessage $message,
        NotificationEntityInterface $entity
    ) {
        //convert entity to array
        $entityArray = $this->entityConverter->toArray($entity);

        //get entity "name", e.g. "product", "user"
        $entityArray['entityName'] = $this->notificationUtils->getUrlPostfix($entity);

        $responseMessage = new NotificationBatch();
        $responseMessage->setClientSecret($this->clientSecretProvider->getClientSecret($message->getClientId()));
        $responseMessage->setClientId($message->getClientId());
        $responseMessage->setParentMessageUid($message->getUid());
        $responseMessage->setType(NotificationBatch::MESSAGE_TYPE);

        $notification = new Notification();
        $notification->setData($entityArray);
        $notification->setMethod('POST');
        $notification->setMessageId($responseMessage->getUid());

        $responseMessage->addNotification($notification);
        $responseMessage->addNotifications($message->getPreviousNotifications());

        //send a notification about the entity
        $this->messageManager->addMessage($responseMessage);
        $this->messageManager->send();
    }

    /**
     * Get client secret from the message.
     *
     * @param Message $message
     *
     * @return string
     */
    protected function getClientSecret(Message $message) : string
    {
        return $this->clientSecretProvider->getClientSecret($message->getClientId());
    }
}