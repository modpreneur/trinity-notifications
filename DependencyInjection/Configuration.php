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
     * @return TreeBuilder
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('trinity_notification');

        $rootNode
            ->children()
                ->arrayNode('entities')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            
            //full class names of the forms
                ->arrayNode('forms')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            
                ->enumNode('mode')
                    ->values(['client', 'server'])
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            
                ->arrayNode('drivers')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->prototype('scalar')
                    ->end()
                ->end()
            
                ->scalarNode('client_id')
                    ->cannotBeEmpty()
                ->end()
            
                ->scalarNode('entity_id_field')
                    ->cannotBeEmpty()
                ->end()

                ->arrayNode('server_to_clients')
                    ->cannotBeEmpty()
                    ->children()

                        ->scalarNode('dead_letter_exchange_name')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('dead_letter_queue_name')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('exchange_name')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('queue_name_pattern')
                            ->cannotBeEmpty()
                            ->validate()
                            ->ifTrue(
                                function ($s) {
                                    return strpos($s, ':ID') === false;
                                }
                            )
                                ->thenInvalid('The queue_name_pattern should contain :ID as wildcard for client id. %s given.')
                            ->end()
                        ->end()

                    ->end()
                ->end()

                ->arrayNode('clients_to_server')
                    ->cannotBeEmpty()
                    ->children()

                        ->scalarNode('dead_letter_exchange_name')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('dead_letter_queue_name')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('notifications_exchange_name')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('notifications_queue_name')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('messages_exchange_name')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('messages_queue_name')
                            ->cannotBeEmpty()
                        ->end()

                    ->end()
                ->end()

                ->scalarNode('client_notifications_exchange_name')
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('client_messages_exchange_name')
                    ->cannotBeEmpty()
                ->end()

                ->arrayNode('listening_queues')
                    ->cannotBeEmpty()
                    ->isRequired()
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()

            //reference to a service - starting with '@'
                ->scalarNode('client_secret_provider')
                    ->cannotBeEmpty()
                    ->beforeNormalization()
            //if the string starts with @, e.g. @service.name
                    ->ifTrue(
                        function ($v) {
                            return is_string($v) && 0 === strpos($v, '@');
                        }
                    )
            //return it's name without '@', e.g. service.name
                    ->then(function ($v) {
                        return substr($v, 1);
                    })
                    ->end()
                ->end()
            ;

        return $treeBuilder;
    }
}
