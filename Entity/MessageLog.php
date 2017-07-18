<?php

namespace Trinity\NotificationBundle\Entity;

use Trinity\Bundle\MessagesBundle\Message\Message;
use Trinity\Component\Core\Interfaces\EntityInterface;

/**
 * Message Log.  In elasticSearch
 */
class MessageLog extends Message implements EntityInterface
{
    const DEFAULT_TTL = 30;
    const LOG_NAME = 'MessageLog';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var int timestamp
     */
    protected $logCreatedAt;

    /**
     * @var bool
     */
    protected $isDead;

    /**
     * @var string
     */
    protected $messageJson;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $status;
    
    /**
     * @var int
     */
    protected $ttl;
    
    /**
     * @var string
     */
    protected $error;

    /**
     * Message log constructor.
     *
     * @param string $id
     */
    public function __construct($id = '')
    {
        parent::__construct();
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLogCreatedAt()
    {
        return $this->logCreatedAt;
    }

    /**
     * @param int $logCreatedAt
     */
    public function setLogCreatedAt($logCreatedAt)
    {
        $this->logCreatedAt = $logCreatedAt;
    }

    /**
     * @return bool
     */
    public function getIsDead() :bool
    {
        return $this->isDead ?: false;
    }

    /**
     * @param bool $isDead
     */
    public function setIsDead(bool $isDead)
    {
        $this->isDead = $isDead;
    }

    /**
     * @return string
     */
    public function getMessageJson()
    {
        return $this->messageJson;
    }

    /**
     * @param string $messageJson
     */
    public function setMessageJson($messageJson)
    {
        $this->messageJson = $messageJson;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @param mixed $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->id;
    }

    /**
     * Return a human readable string containing only characters.
     * For example: ExceptionLog, IpnLog
     *
     * @return string
     */
    public static function getLogName(): string
    {
        return self::LOG_NAME;
    }

    /**
     * Return a default tll in days.
     *
     * @return int
     */
    public static function getDefaultTtl(): int
    {
        return self::DEFAULT_TTL;
    }
}
