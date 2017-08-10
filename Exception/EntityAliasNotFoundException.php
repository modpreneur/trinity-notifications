<?php

namespace Trinity\NotificationBundle\Exception;

/**
 * Class EntityAliasNotFoundException.
 */
class EntityAliasNotFoundException extends NotificationException
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass( $class)
    {
        $this->class = $class;
    }
}
