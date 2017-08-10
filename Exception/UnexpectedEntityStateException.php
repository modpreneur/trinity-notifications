<?php

namespace Trinity\NotificationBundle\Exception;

use Trinity\NotificationBundle\Entity\Notification;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;

/**
 * Class UnexpectedEntityStateException.
 */
class UnexpectedEntityStateException extends NotificationException
{
    /** @var  NotificationEntityInterface */
    protected $entity;

    /**
     * @var array
     *            Format:
     *            [
     *            "propertyName1" => ['expected' => 'expectedValue1, 'actual' => 'actual-value1'],
     *            "propertyName2" => ['expected' => 'expectedValue2, 'actual' => 'actual-value2'],
     *            ]
     */
    protected $violations = [];

    /** @var  Notification Notification which was containig the data */
    protected $notification;

    /**
     * @return array
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @param array $violations
     * @param bool  $recreateMessage
     */
    public function setViolations(array $violations, $recreateMessage = false)
    {
        $this->violations = $violations;

        if ($recreateMessage || $this->message === '') {
            $this->createMessage();
        }
    }

    /**
     * @return NotificationEntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param NotificationEntityInterface $entity
     */
    public function setEntity(NotificationEntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     *
     */
    protected function createMessage()
    {
        $message = 'Trying to process entity of class '.get_class($this->entity).
            ' but there were violations between the expected state and the current state. The violations are: ';
        foreach ($this->violations as $propertyName => $violation) {
            $expected = $violation['expected'];
            $actual = $violation['actual'];
            $message .=  "Expected $propertyName to have a value of '$expected' but the value is '$actual'";
        }

        $this->message = $message;
    }
}
