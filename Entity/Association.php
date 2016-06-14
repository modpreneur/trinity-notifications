<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 29.04.16
 * Time: 11:53
 */

namespace Trinity\NotificationBundle\Entity;

/**
 * Class Association
 */
class Association
{
    /** @var  string Method which is used to get the association */
    protected $getterMethod;

    /** @var  string Method which is used to get the association */
    protected $setterMethod;

    /** @var  string Class name of the target entity */
    protected $targetClassName;

    /**
     * Association constructor.
     *
     * @param string $getterMethod
     * @param string $setterMethod
     * @param string $targetClassName
     */
    public function __construct(string $getterMethod, string $setterMethod, string $targetClassName)
    {
        $this->getterMethod = $getterMethod;
        $this->setterMethod = $setterMethod;
        $this->targetClassName = $targetClassName;
    }

    /**
     * @return string
     */
    public function getGetterMethod() : string
    {
        return $this->getterMethod;
    }

    /**
     * @param string $getterMethod
     */
    public function setGetterMethod(string $getterMethod)
    {
        $this->getterMethod = $getterMethod;
    }

    /**
     * @return string
     */
    public function getSetterMethod() : string
    {
        return $this->setterMethod;
    }

    /**
     * @param string $setterMethod
     */
    public function setSetterMethod(string $setterMethod)
    {
        $this->setterMethod = $setterMethod;
    }

    /**
     * @return string
     */
    public function getTargetClassName() : string
    {
        return $this->targetClassName;
    }

    /**
     * @param string $targetClassName
     */
    public function setTargetClassName($targetClassName)
    {
        $this->targetClassName = $targetClassName;
    }
}
