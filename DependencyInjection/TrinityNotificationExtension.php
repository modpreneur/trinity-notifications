<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setShared($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        //load generic services
        $loader->load('services.yml');

        //load services for client or server
        if ($container->getParameter('trinity.notification.is_client')) {
            $loader->load('client/services.yml');
        } else {
            $loader->load('server/services.yml');
        }

        // Inject client secret provider into request handler
        $container->getDefinition('trinity.notification.notification_request_handler')
            ->addMethodCall(
                'setSecretKeyProvider',
                [new Reference($config['client_secret_provider'])]
            );
    }


    /**
     * @param ContainerInterface $container
     * @param array              $config
     */
    private function setShared(ContainerInterface $container, array $config)
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
