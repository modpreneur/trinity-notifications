<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;


/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TrinityNotificationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('trinity.notification.necktie_notify_url', $config['necktie_notify_url']);
        $container->setParameter('trinity.notification.necktie_oauth_url', $config['necktie_oauth_url']);
        $container->setParameter('trinity.notification.necktie_client_id', $config['necktie_client_id']);
        $container->setParameter('trinity.notification.necktie_client_secret', $config['necktie_client_secret']);

        // If is the necktie entity specified the application is client
        if(array_key_exists("necktie_notify_url", $config))
        {
            $container->setParameter("trinity.notification.is_client", true);
        }
        else
        {
            $container->setParameter("trinity.notification.is_client", false);
        }

        $enabledDrivers = [];
        foreach ($config["drivers"] as $driver) {
            $enabledDrivers[] = $driver;
        }

        // Set enabled drivers to parameters and load them later in DriverCompilerPass
        $container->setParameter("trinity.enabled_drivers", implode(",", $enabledDrivers));

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
