<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('trinity_notification');

        $rootNode
            ->children()
                ->arrayNode("entities")
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->prototype("scalar")
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->enumNode("mode")
                    ->values(["client", "server"])
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode("drivers")
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->prototype("scalar")
                    ->end()
                ->end()
                ->scalarNode("client_id")
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode("client_secret")
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode("notify_url")
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode("entity_id_field")
                    ->cannotBeEmpty()
                    ->defaultValue("id")
                ->end()
                ->booleanNode("create_new_entity")
                    ->defaultTrue()
                ->end()
                    ->arrayNode("server_to_clients")
                        ->cannotBeEmpty()
                        ->children()
                            ->scalarNode("dead_letter_exchange_name")
                                ->defaultValue("exchange.dead.server.to.clients.notifications")
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode("dead_letter_queue_name")
                                ->defaultValue("queue.dead.server.to.clients.notifications")
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode("exchange_name")
                                ->defaultValue("exchange.server.to.clients.notifications")
                                ->cannotBeEmpty()
                            ->end()

                            ->scalarNode("error_messages_queue_name")
                                ->defaultValue("queue.server.to.clients.dead.notifications.error.messages")
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode("error_messages_exchange_name")
                                ->defaultValue("exchange.server.to.clients.dead.notifications.error.messages")
                                ->cannotBeEmpty()
                            ->end()

                            ->scalarNode("queue_name_pattern")
                                ->defaultValue("queue.client_:ID")
                                ->cannotBeEmpty()
                                ->validate()
                                ->ifTrue(function($s){
                                    return strpos($s, ":ID") === false;
                                })
                                    ->thenInvalid("The queue_name_pattern should contain :ID as wildcard for client id. %s given.")
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->arrayNode("clients_to_server")
                    ->cannotBeEmpty()
                    ->children()
                        ->scalarNode("dead_letter_exchange_name")
                            ->defaultValue("exchange.dead.clients.to.server.notifications")
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode("dead_letter_queue_name")
                            ->defaultValue("queue.dead.clients.to.server.notifications")
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode("exchange_name")
                            ->cannotBeEmpty()
                            ->defaultValue("exchange.clients.to.server.notifications")
                        ->end()
                        ->scalarNode("queue_name")
                            ->cannotBeEmpty()
                            ->defaultValue("queue.clients.to.server.notifications")
                        ->end()

                        ->scalarNode("error_messages_queue_name")
                            ->cannotBeEmpty()
                            ->defaultValue("queue.clients.to.server.dead.notifications.error.messages")
                        ->end()
                        ->scalarNode("error_messages_exchange_name")
                            ->cannotBeEmpty()
                            ->defaultValue("exchange.clients.to.server.dead.notifications.error.messages")
                        ->end()
                    ->end()
                ->end()
                ->scalarNode("client_output_exchange_name")
                    ->cannotBeEmpty()
                    ->defaultValue("exchange.clients.to.server.notifications")
                ->end()
                ->scalarNode("error_messages_exchange_name")
                    ->cannotBeEmpty()
                    ->defaultValue("exchange.server.to.clients.dead.notifications.error.messages")
                ->end()
                ->scalarNode("listening_queue_name")
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
            ;


        return $treeBuilder;
    }
}
