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

        foreach ($container->findTaggedServiceIds('trinity.notification.driver') as $serviceId => $key) {
            $definition->addMethodCall('addDriver', [new Reference($serviceId)]);
        }
    }
}
