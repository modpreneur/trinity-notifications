<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Notification;

use Trinity\NotificationBundle\Driver\INotificationDriver;
use Trinity\NotificationBundle\Exception\ClientException;
use Trinity\NotificationBundle\Exception\MethodException;
use Trinity\NotificationBundle\Exception\NotificationDriverException;



/**
 * Class NotificationManager.
 */
class NotificationManager
{
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';

    /** @var  INotificationDriver[] */
    private $drivers;

    /** @var string */
    private $driverName;

    /** @var INotificationDriver */
    private $driver;



    /**
     * NotificationManager constructor.
     *
     * @param string $driverName
     */
    public function __construct($driverName)
    {
        $this->driverName = $driverName;

        $this->drivers = [];
    }



    /**
     * @param INotificationDriver $driver
     */
    public function addDriver(INotificationDriver $driver)
    {
        $this->drivers[] = $driver;
    }



    /**
     * @return INotificationDriver
     */
    public function getDriver()
    {
        $this->setDriver();

        return $this->driver;
    }



    /**
     *  Send notification to client (App).
     *
     *
     * @param object $entity
     * @param string $HTTPMethod
     *
     * @return mixed|string|void
     *
     * @throws ClientException
     * @throws MethodException
     */
    public function send($entity, $HTTPMethod = 'GET')
    {
        $response = $this->getDriver()->execute($entity, ['HTTPMethod' => $HTTPMethod]);

        return $response;
    }



    /**
     * @param INotificationDriver|null $driver
     *
     * @throws NotificationDriverException
     */
    protected function setDriver($driver = null)
    {
        if ($driver === null) {
            if ($this->driver === null) {
                foreach ($this->drivers as $driver) {
                    if ($driver->getName() === $this->driverName) {
                        $this->driver = $driver;
                    }
                }
            }

            if ($this->driver === null && $this->driverName) {
                throw new NotificationDriverException("Driver ".$this->driverName." not found.");
            } elseif ($this->driver === null) {
                throw new NotificationDriverException("Notification driver is probably not set.");
            }

        } else {
            $this->driver = $driver;
        }
    }
}
