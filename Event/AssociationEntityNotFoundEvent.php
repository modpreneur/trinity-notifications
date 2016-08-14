<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 21.04.16
 * Time: 16:04.
 */
namespace Trinity\NotificationBundle\Event;

use Trinity\NotificationBundle\Exception\AssociationEntityNotFoundException;

/**
 * Class AssociationEntityNotFoundEvent.
 */
class AssociationEntityNotFoundEvent extends NotificationEvent
{
    const NAME = 'trinity.notifications.associationEntityNotFound';

    /** @var  AssociationEntityNotFoundException */
    protected $exception;

    /**
     * AssociationEntityNotFoundEvent constructor.
     *
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
