<?php

namespace Trinity\NotificationBundle\Entity;

use Trinity\Bundle\MessagesBundle\Message\Message;

/**
 * Class SynchronizationStoppedMessage.
 */
class SynchronizationStoppedMessage extends Message
{
    const ENTITY_ID_KEY = 'entityId';
    const ENTITY_ALIAS_KEY = 'entityAlias';

    const MESSAGE_TYPE = 'synchronizationStopped';

    /** @var  array */
    protected $rawData;

    /**
     * SynchronizationStoppedMessage constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::MESSAGE_TYPE;
        $this->rawData = [self::ENTITY_ID_KEY => '0', self::ENTITY_ALIAS_KEY => 'entityAlias'];
    }

    /**
     * Convert each notification to array and return array of that arrays.
     *
     * @return array
     */
    public function getArrayOfNotificationsConvertedToArray()
    {
        $data = [];
        /** @var Notification $notification */
        foreach ($this->rawData as $notification) {
            $data[] = $notification->toArray();
        }

        return $data;
    }

    /**
     * @param Message $message
     *
     * @return SynchronizationStoppedMessage
     */
    public static function createFromMessage(Message $message)
    {
        $stoppedMessage = new self();
        $message->copyTo($stoppedMessage);

        if (!$stoppedMessage->rawData) {
            $stoppedMessage->rawData = json_decode($stoppedMessage->jsonData, true);
        }

        return $stoppedMessage;
    }

    /**
     * Unpack message.
     *
     * @param string $messageJson
     *
     * @return SynchronizationStoppedMessage
     *
     * @throws \Trinity\Bundle\MessagesBundle\Exception\DataNotValidJsonException
     */
    public static function unpack( $messageJson)
    {
        return self::createFromMessage(parent::unpack($messageJson));
    }

    /**
     * @return string
     */
    public function getEntityAlias()
    {
        return $this->rawData[self::ENTITY_ALIAS_KEY];
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->rawData[self::ENTITY_ID_KEY];
    }

    /**
     * @param string $entityAlias
     */
    public function setEntityAlias( $entityAlias)
    {
        $this->rawData[self::ENTITY_ALIAS_KEY] = $entityAlias;
    }

    /**
     * @param string $entityId
     */
    public function setEntityId( $entityId)
    {
        $this->rawData[self::ENTITY_ID_KEY] = $entityId;
    }
}
