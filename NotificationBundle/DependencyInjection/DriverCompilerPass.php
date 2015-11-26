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

        // Get enabled drivers whose were set in the TrinityNotificationExtension
        $enabledDrivers = explode(",", $container->getParameter("trinity.enabled_drivers"));

        foreach ($container->findTaggedServiceIds('trinity.notification.driver') as $serviceId => $key) {
            // If the driver was configured as enabled or there are no configured drivers
            if(in_array($serviceId, $enabledDrivers) || empty($enabledDrivers))
            {
                $definition->addMethodCall('addDriver', [new Reference($serviceId)]);
            }
        }
    }
}
