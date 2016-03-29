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
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('trinity.notification.is_client', $config["mode"] == "client");

        //Add string with driver names which will be processed in DriverCompilerPass
        $container->setParameter('trinity.notification.enabled_drivers', implode(',', $config['drivers']));

        //Add other parameters
        $container->setParameter('trinity.notification.client.id', $this->getValue($config, "client_id"));
        $container->setParameter('trinity.notification.client.secret', $this->getValue($config, "client_secret"));

        $container->setParameter('trinity.notification.entity_id_field', $this->getValue($config, 'entity_id_field'));
        $container->setParameter('trinity.notification.create_new_entity', $this->getValue($config, 'create_new_entity'));

        if (array_key_exists("server_to_clients", $config)) {
            $container->setParameter('trinity.notification.server.to.clients.dead.letter.exchange.name', $this->getValue($config["server_to_clients"], "dead_letter_exchange_name")); // DLX for N client queues
            $container->setParameter('trinity.notification.server.to.clients.dead.letter.queue.name', $this->getValue($config["server_to_clients"], "dead_letter_queue_name")); //for N client queues
            $container->setParameter('trinity.notification.server.to.clients.exchange.name', $this->getValue($config["server_to_clients"], "exchange_name")); //for N client queues
            $container->setParameter('trinity.notification.server.to.clients.queue.name.pattern', $this->getValue($config["server_to_clients"], "queue_name_pattern"));//for N client queues

            $container->setParameter('trinity.notification.server.to.clients.error.messages.queue.name', $this->getValue($config["server_to_clients"], "error_messages_queue_name"));
            $container->setParameter('trinity.notification.server.to.clients.error.messages.exchange.name', $this->getValue($config["server_to_clients"], "error_messages_exchange_name"));
        }

        if (array_key_exists("clients_to_server", $config)) {
            $container->setParameter('trinity.notification.clients.to.server.dead.letter.exchange.name', $this->getValue($config["clients_to_server"], "dead_letter_exchange_name"));// DLX for 1 server queue
            $container->setParameter('trinity.notification.clients.to.server.dead.letter.queue.name', $this->getValue($config["clients_to_server"], "dead_letter_queue_name")); //for 1 server notification queue
            $container->setParameter('trinity.notification.clients.to.server.exchange.name', $this->getValue($config["clients_to_server"], "exchange_name"));// DLX for 1 server queue
            $container->setParameter('trinity.notification.clients.to.server.queue.name', $this->getValue($config["clients_to_server"], "queue_name"));// DLX for 1 server queue

            $container->setParameter('trinity.notification.clients_to_server.error.messages.queue.name', $this->getValue($config["clients_to_server"], "error_messages_queue_name"));
            $container->setParameter('trinity.notification.clients_to_server.error.messages.exchange.name', $this->getValue($config["clients_to_server"], "error_messages_exchange_name"));
        }

        $container->setParameter('trinity.notification.output.exchange.name', $this->getValue($config, "client_output_exchange_name"));

        $container->setParameter('trinity.notification.entities', json_encode($this->getValue($config, "entities")));

        $container->setParameter('trinity.notification.output.error.exchange.name', $this->getValue($config, "error_messages_exchange_name"));
        $container->setParameter('trinity.notification.listening.queue.name', $this->getValue($config, "listening_queue_name"));

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yml');
        if ($container->getParameter("trinity.notification.is_client")) {
            $loader->load('client/services.yml');
        } else {
            $loader->load('server/services.yml');

            // Inject provider into reader service
            $container->getDefinition('trinity.notification.reader')->addMethodCall("setClientSecretProvider", [new Reference($config["client_secret_provider"])]);
        }



    }


    /**
     * @param $array
     * @param $key
     *
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
