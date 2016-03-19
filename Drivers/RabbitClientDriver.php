<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 18.03.16
 * Time: 14:58
 */

namespace Trinity\NotificationBundle\Driver;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Trinity\FrameworkBundle\Entity\ClientInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Notification\Annotations\NotificationUtils;
use Trinity\NotificationBundle\Notification\EntityConverter;
use Trinity\NotificationBundle\RabbitMQ\ClientProducer;

class RabbitClientDriver extends BaseDriver
{
    /**
     * @var  ClientProducer
     */
    protected $producer;


    /**
     * @var string
     */
    protected $clientId;


    /**
     * @var string
     */
    protected $clientSecret;


    /**
     * NotificationManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityConverter $entityConverter
     * @param NotificationUtils $notificationUtils
     * @param ClientProducer $producer
     * @param TokenStorage $tokenStorage
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityConverter $entityConverter,
        NotificationUtils $notificationUtils,
        ClientProducer $producer,
        TokenStorage $tokenStorage,
        string $clientId,
        string $clientSecret
    ) {
        parent::__construct($eventDispatcher, $entityConverter, $notificationUtils, $tokenStorage);

        $this->producer = $producer;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }


    /**
     * @param NotificationEntityInterface $entity
     * @param ClientInterface             $client
     * @param array                       $params
     *
     * @return void
     */
    public function execute(NotificationEntityInterface $entity, ClientInterface $client = null, $params = [])
    {
        if ($this->isEntityAlreadyProcessed($entity)) {
            return;
        }

        $this->addEntityToNotifiedEntities($entity);

        //convert entity to array
        $entityArray = $this->entityConverter->toArray($entity);

        $entityArray["clientId"] = $this->clientId;

        //get entity "name", e.g. "product", "user"
        $entityArray["entityName"] = $this->notificationUtils->getUrlPostfix($entity);

        //encode object array to JSON
        $json = $this->JSONEncodeObject($entityArray, $this->clientSecret);

        $this->producer->publish($json);

        //$entity->setNotificationStatus(null, 'unknown'); //todo: fix!
    }


    /**
     * Return name of driver-.
     *
     * @return string
     */
    public function getName()
    {
        return "rabbit_server_driver";
    }
}