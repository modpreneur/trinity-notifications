<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.04.16
 * Time: 16:04
 */

namespace Trinity\NotificationBundle\Event;

use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;

/**
 * Class AssociationEntityNotFoundExceptionThrown
 *
 * @package Trinity\NotificationBundle\Event
 *//*todo: add 'Event' to the name*/
class AssociationEntityNotFoundExceptionThrown extends NotificationEvent
{
    /** @var  AssociationEntityNotFoundException */
    protected $exception;


    /**
     * AssociationEntityNotFoundExceptionThrown constructor.
     * @param AssociationEntityNotFoundException $exception
     */
    public function __construct(AssociationEntityNotFoundException $exception)
    {
        $this->exception = $exception;
    }


    /**
     * @return AssociationEntityNotFoundException
     */
    public function getException() : AssociationEntityNotFoundException
    {
        return $this->exception;
    }


    /**
     * @param AssociationEntityNotFoundException $exception
     */
    public function setException(AssociationEntityNotFoundException $exception)
    {
        $this->exception = $exception;
    }
}

