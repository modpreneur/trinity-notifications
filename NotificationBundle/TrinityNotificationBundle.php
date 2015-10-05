<?php

/**
 * This file is part of the Trinity project.
 */
namespace Trinity\NotificationBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Trinity\NotificationBundle\DependencyInjection\DriverCompilerPass;


/**
 * Class NecktieNotificationBundle.
 */
class TrinityNotificationBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DriverCompilerPass());
    }
}
