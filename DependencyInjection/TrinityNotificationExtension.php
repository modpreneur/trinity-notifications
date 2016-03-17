<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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

        //If the app is in client mode
        if (array_key_exists('client', $config)) {
            $config = $config['client'];

            $container->setParameter('trinity.notification.is_client', true);

            //If the app is in server mode
        } else {
            if (array_key_exists('server', $config)) {
                $config = $config["server"];

                $container->setParameter('trinity.notification.is_client', false);
            } else {
                throw new InvalidConfigurationException('TestClient or server node is not specified.');
            }
        }

        //Add string with driver names which will be processed in DriverCompilerPass
        $container->setParameter('trinity.notification.enabled_drivers', implode(',', $config["drivers"]));

        //Add other paramters
        $container->setParameter(
            'trinity.notification.server_notify_url',
            $this->getValue($config, 'server_notify_url')
        );
        $container->setParameter('trinity.notification.server_oauth_url', $this->getValue($config, 'server_oauth_url'));
        $container->setParameter('trinity.notification.server_client_id', $this->getValue($config, 'server_client_id'));
        $container->setParameter(
            'trinity.notification.server_client_secret',
            $this->getValue($config, 'server_client_secret')
        );
        $container->setParameter('trinity.notification.entity_id_field', $this->getValue($config, 'entity_id_field'));
        $container->setParameter(
            'trinity.notification.create_new_entity',
            $this->getValue($config, 'create_new_entity')
        );

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }


    /**
     * @param $array
     * @param $key
     * @return null
     */
    private function getValue($array, $key)
    {
        if (array_key_exists($key, $array) && isset($array[$key])) {
            return $array[$key];
        }

        return null;
    }
}
