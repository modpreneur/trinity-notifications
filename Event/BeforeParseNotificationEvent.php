<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 14:51.
 */
namespace Trinity\NotificationBundle\Event;

/**
 * Class BeforeParseNotificationEvent.
 */
class BeforeParseNotificationEvent extends NotificationEvent
{
    const NAME = 'trinity.notifications.beforeParseNotification';

    /** @var array */
    protected $data;

    /** @var array */
    protected $changeSet;

    /** @var string */
    protected $classname;

    /** @var string */
    protected $httpMethod;

    /**
     * BeforeParseNotificationEvent constructor.
     *
     * @param array  $data
     * @param array  $changeSet
     * @param string $classname
     * @param string $httpMethod
     */
    public function __construct(array $data, array $changeSet, string $classname, string $httpMethod)
    {
        $this->data = $data;
        $this->changeSet = $changeSet;
        $this->classname = $classname;
        $this->httpMethod = $httpMethod;
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getChangeSet(): array
    {
        return $this->changeSet;
    }

    /**
     * @param array $changeSet
     */
    public function setChangeSet(array $changeSet)
    {
        $this->changeSet = $changeSet;
    }

    /**
     * @return string
     */
    public function getClassname() : string
    {
        return $this->classname;
    }

    /**
     * @param string $classname
     */
    public function setClassname(string $classname)
    {
        $this->classname = $classname;
    }

    /**
     * @return string
     */
    public function getHttpMethod() : string
    {
        return $this->httpMethod;
    }

    /**
     * @param string $httpMethod
     */
    public function setHttpMethod(string $httpMethod)
    {
        $this->httpMethod = $httpMethod;
    }
}
