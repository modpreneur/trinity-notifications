<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Tests\Driver;

use Trinity\NotificationBundle\Driver\BaseDriver;

/**
 * Class TestDriver.
 */
class TestDriver extends BaseDriver
{
    /**
     * @param object $entity
     * @param array  $params
     *
     * @return mixed
     */
    public function execute($entity, $params = [])
    {
    }
}
