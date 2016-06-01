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
        $this->setServerToClients($container, $config);
        $this->setClientsToServer($container, $config);
        $this->setClientOnly($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        //load generic services
        $loader->load('services.yml');

        //load services for client or server
        if ($container->getParameter('trinity.notification.is_client')) {
            $loader->load('client/services.yml');

            // Inject client secret provider into
            $container->getDefinition('trinity.notification.driver.rabbit.client')
                ->addMethodCall(
                    'setClientSecretProvider',
                    [new Reference($config['client_secret_provider'])]
                );
        } else {
            $loader->load('server/services.yml');
        }

        // Inject client secret provider into reader service
        $container->getDefinition('trinity.notification.message_reader')
            ->addMethodCall(
                'setClientSecretProvider',
                [new Reference($config['client_secret_provider'])]
            );

        // Inject client secret provider into request handler
        $container->getDefinition('trinity.notification.notification_request_handler')
            ->addMethodCall(
                'setClientSecretProvider',
                [new Reference($config['client_secret_provider'])]
            );

        // Inject client secret provider into request handler
        $container->getDefinition('trinity.notification.message_status_listener')
            ->addMethodCall(
                'setClientSecretProvider',
                [new Reference($config['client_secret_provider'])]
            );


    }


    /**
     * @param ContainerInterface $container
     * @param array              $config
     */
    private function setClientsToServer(ContainerInterface $container, array $config)
    {
        if (array_key_exists('clients_to_server', $config)) {
            $container->setParameter(
                'trinity.notification.clients.to.server.dead.letter.exchange.name',
                $this->getValue($config['clients_to_server'], 'dead_letter_exchange_name')
            );// DLX for 1 server queue

            $container->setParameter(
                'trinity.notification.clients.to.server.dead.letter.queue.name',
                $this->getValue($config['clients_to_server'], 'dead_letter_queue_name')
            ); //for 1 server notification queue

            $container->setParameter(
                'trinity.notification.clients.to.server.notifications.exchange.name',
                $this->getValue($config['clients_to_server'], 'notifications_exchange_name')
            );// DLX for 1 server queue

            $container->setParameter(
                'trinity.notification.clients.to.server.notifications.queue.name',
                $this->getValue($config['clients_to_server'], 'notifications_queue_name')
            );// DLX for 1 server queue

            $container->setParameter(
                'trinity.notification.clients.to.server.messages.exchange.name',
                $this->getValue($config['clients_to_server'], 'messages_exchange_name')
            );// DLX for 1 server queue

            $container->setParameter(
                'trinity.notification.clients.to.server.messages.queue.name',
                $this->getValue($config['clients_to_server'], 'messages_queue_name')
            );// DLX for 1 server queue
        }
    }

    /**
     * @param ContainerInterface $container
     * @param array              $config
     */
    private function setServerToClients(ContainerInterface $container, array $config)
    {
        if (array_key_exists('server_to_clients', $config)) {
            $container->setParameter(
                'trinity.notification.server.to.clients.dead.letter.exchange.name',
                $this->getValue($config['server_to_clients'], 'dead_letter_exchange_name')
            ); // DLX for N client queues

            $container->setParameter(
                'trinity.notification.server.to.clients.dead.letter.queue.name',
                $this->getValue($config['server_to_clients'], 'dead_letter_queue_name')
            ); //for N client queues

            $container->setParameter(
                'trinity.notification.server.to.clients.exchange.name',
                $this->getValue($config['server_to_clients'], 'exchange_name')
            ); //for N client queues

            $container->setParameter(
                'trinity.notification.server.to.clients.queue.name.pattern',
                $this->getValue($config['server_to_clients'], 'queue_name_pattern')
            );//for N client queues
        }
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

        //Add string with driver names which will be processed in DriverCompilerPass
        $container->setParameter(
            'trinity.notification.enabled_drivers',
            $config['drivers']
        );

        $container->setParameter(
            'trinity.notification.client.id',
            $this->getValue($config, 'client_id')
        );

        $container->setParameter(
            'trinity.notification.client.secret',
            $this->getValue($config, 'client_secret')
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
            $this->transformKeys($this->getValue($config, 'entities'))
        );

        $container->setParameter(
            'trinity.notification.forms',
            $this->transformKeys($this->getValue($config, 'forms'))
        );
    }


    /**
     * @param ContainerInterface $container
     * @param array              $config
     */
    private function setClientOnly(ContainerInterface $container, array $config = [])
    {
        $container->setParameter(
            'trinity.notification.output.messages.exchange.name',
            $this->getValue($config, 'client_messages_exchange_name')
        );

        $container->setParameter(
            'trinity.notification.output.notifications.exchange.name',
            $this->getValue($config, 'client_notifications_exchange_name')
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


    /**
     * Change "_" to "-" in array keys
     *
     * @param array $array
     *
     * @return array
     */
    private function transformKeys(array $array)
    {
        // Replace "_" for "-" in all keys
        foreach ($array as $key => $className) {
            $newKey = str_replace('_', '-', $key);
            unset($array[$key]);
            $array[$newKey] = $className;
        }

        return $array;
    }
}
