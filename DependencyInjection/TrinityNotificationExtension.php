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
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        //load generic services
        $loader->load('services.yml');

        $this->setShared($container, $config);

        //load services for client or server
        if ($container->getParameter('trinity.notification.is_client')) {
            $loader->load('client/services.yml');
        } else {
            $loader->load('server/services.yml');
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @param array $config
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    private function setShared(ContainerBuilder $container, array $config)
    {
        $container->setParameter(
            'trinity.notification.is_client',
            $config['mode'] === 'client'
        );

        $container->setParameter(
            'trinity.notification.enabled_drivers',
            $config['drivers']
        );

        $container->setParameter(
            'trinity.notification.client.id',
            $this->getValue($config, 'client_id')
        );

        $container->setParameter(
            'trinity.notification.entity_id_field',
            $this->getValue($config, 'entity_id_field')
        );

        $container->setParameter(
            'trinity.notification.listening.queues',
            $this->getValue($config, 'listening_queues')
        );

        $container->setParameter(
            'trinity.notification.entities',
            $this->getValue($config, 'entities')
        );

        $container->setParameter(
            'trinity.notification.forms',
            $this->getValue($config, 'forms')
        );

        $container->setParameter(
            'trinity.notification.disable_time_violations',
            $this->getValue($config, 'disable_time_violations')
        );

        $parser = $container->getDefinition('trinity.notification.services.notification_parser');

        //add the client strategy first so it will be called before the default one
        if (null !== $config['unknown_entity_strategy']) {
            $parser->addMethodCall('addUnknownEntityStrategy', [new Reference($config['unknown_entity_strategy'])]);
        }

        $parser->addMethodCall(
            'addUnknownEntityStrategy',
            [new Reference('trinity.notification.unknown_entity_name_strategy')]
        );

        if ($config['log_storage'] === 'elastic') {
            $container->setAlias('trinity.notification.log_storage', 'trinity.notification.elastic_log_storage');

            $container->setAlias('trinity.notification.elastic_log_service', $config['elastic_log_service']);
            $container->setAlias('trinity.notification.elastic_read_log_service', $config['elastic_read_log_service']);
        } else if ($config['log_storage'] === 'custom') {
            if (!array_key_exists('log_storage_service', $config)) {
                throw new InvalidConfigurationException(
                    'The key log_storage_service must be configured, when log_storage=custom'
                );
            }

            $container->setAlias('trinity.notification.log_storage', $config['log_storage_service']);

            //these will not be used
            $container->setAlias('trinity.notification.elastic_log_service', 'trinity.notification.dummy_elastic_service');
            $container->setAlias('trinity.notification.elastic_read_log_service', 'trinity.notification.dummy_elastic_service');
        }

        if (array_key_exists('entity_fetcher_service', $config)) {
            $container->setAlias('trinity.notification.database_entity_fetcher', $config['entity_fetcher_service']);
        }

    }

    /**
     * @param $array
     * @param $key
     *
     * @return null|string
     */
    private function getValue($array, $key)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        return null;
    }
}
