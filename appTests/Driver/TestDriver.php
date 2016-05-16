<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\AppTests\Driver;

use Trinity\NotificationBundle\Drivers\BaseDriver;


/**
 * Class TestDriver.
 */
class TestDriver extends BaseDriver
{
    /**
     * @param object $entity
     * @param array $params
     *
     * @return mixed
     */
    public function execute($entity, $params = [])
    {
    }


    /**
     * Return name of driver-.
     *
     * @return string
     */
    public function getName()
    {
        return "testDriver";
    }
}
