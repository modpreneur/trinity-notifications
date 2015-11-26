<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
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

        $container->setParameter('trinity.notification.driver_name', $config['driver']);
        $container->setParameter('trinity.notification.necktie_notify_uri', $config['necktie_notify_uri']);

        // If is the necktie entity specified the application is client
        if(array_key_exists("necktie_notify_uri", $config))
        {
            $container->setParameter("trinity.notification.is_client", true);
        }
        else
        {
            $container->setParameter("trinity.notification.is_client", false);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
