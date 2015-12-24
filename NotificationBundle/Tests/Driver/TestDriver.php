<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\Tests\Driver;

use Trinity\NotificationBundle\Driver\BaseDriverInterface;


/**
 * Class TestDriver.
 */
class TestDriver extends BaseDriverInterface
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
}
