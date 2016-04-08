<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 08.04.16
 * Time: 14:51
 */

namespace Trinity\NotificationBundle\Event;


/**
 * Class BeforeParseNotificationEvent
 */
class BeforeParseNotificationEvent extends NotificationEvent
{
    /**
     * @var array
     */
    protected $data;


    /**
     * @var string
     */
    protected $classname;


    /**
     * @var string
     */
    protected $httpMethod;


    /**
     * BeforeParseNotificationEvent constructor.
     * @param array $data
     * @param string $classname
     * @param string $httpMethod
     */
    public function __construct(array $data, $classname, $httpMethod)
    {
        $this->data = $data;
        $this->classname = $classname;
        $this->httpMethod = $httpMethod;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


    /**
     * @return string
     */
    public function getClassname()
    {
        return $this->classname;
    }


    /**
     * @param string $classname
     */
    public function setClassname($classname)
    {
        $this->classname = $classname;
    }


    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }


    /**
     * @param string $httpMethod
     */
    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;
    }
}
