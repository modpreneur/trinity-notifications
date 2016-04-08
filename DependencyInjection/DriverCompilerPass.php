<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


/**
 * Class DriverCompilerPass.
 */
class DriverCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('trinity.notification.manager')) {
            return;
        }

        $definition = $container->getDefinition('trinity.notification.manager');

        // Get enabled drivers string which was set in the TrinityNotificationExtension
        $enabledDrivers = explode(",", $container->getParameter("trinity.notification.enabled_drivers"));

        foreach ($container->findTaggedServiceIds('trinity.notification.driver') as $serviceId => $key) {
            // If the driver was configured as enabled
            if (in_array($serviceId, $enabledDrivers)) {
                $definition->addMethodCall('addDriver', [new Reference($serviceId)]);
            }
        }
    }
}
