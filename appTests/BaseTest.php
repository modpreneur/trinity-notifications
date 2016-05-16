<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\AppTests;

use Trinity\FrameworkBundle\Utils\BaseWebTest;


/**
 * Class BaseTest.
 */
abstract class BaseTest extends BaseWebTest
{
    protected $port;


    /**
     * Creates an AppKernel.
     *
     * @param array $options The array with options for the kernel.
     *
     * @return \Symfony\Component\HttpKernel\Kernel The app kernel
     */
    protected function createKernel(array $options = array())
    {
        $kernel = parent::createKernel();

        return new \AppKernel(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true,
            $this->port
        );
    }

}
