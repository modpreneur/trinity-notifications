<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 25.03.16
 * Time: 14:54
 */

namespace Trinity\NotificationBundle\Notification;


use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationBatch;

class NotificationReader
{
    /**
     * @var string
     */
    protected $clientSecret;


    /**
     * @var NotificationParser
     */
    protected $parser;


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
     * NotificationReader constructor.
     *
     * @param NotificationParser $parser
     * @param string $clientSecret
     * @param string $entities
     */
    public function __construct(NotificationParser $parser, string $entities, string $clientSecret = null)
    {
        $this->parser = $parser;
        $this->clientSecret = $clientSecret;
        $this->entities = json_decode($entities, true);

        // Replace "_" for "-" in all keys
        foreach ($this->entities as $key => $className) {
            $newKey = str_replace("_", "-", $key);
            unset($this->entities[$key]);
            $this->entities[$newKey] = $className;
        }
    }


    /**
     * @param string $message
     *
     * @throws \Exception
     */
    public function read(string $message)
    {
        $batch = new NotificationBatch();
        $batch->unpackBatch($message);

//        dump($batch);die();

        $batch->setClientSecret($this->getClientSecret($batch));

        dump($batch);

        if (!$batch->isHashValid()) {
            throw new \Exception("Hash does not match");
        }

        /** @var Notification $notification */
        foreach ($batch->getNotifications() as $notification) {
            $entityName = $notification->getData()["entityName"];

            if(!array_key_exists($entityName, $this->entities)) {
                throw new \Exception("No classname found for entityName: \"".$entityName."\". Have you defined it in the configuration under trinity_notification:entities?");
            }

            $this->parser->parseNotification($notification->getData(), $this->entities[$entityName], $notification->getMethod());
        }
    }


    /**
     * Get client secret from the batch.
     *
     * Override this method to customize the behaviour.
     *
     * @param NotificationBatch $batch
     *
     * @return string
     */
    public function getClientSecret(NotificationBatch $batch = null)
    {
        return $this->clientSecret;
    }


}