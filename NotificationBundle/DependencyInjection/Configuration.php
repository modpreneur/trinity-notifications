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

        //Add root for client
        $rootNode
            ->children()
                ->arrayNode("client")
                    ->children()
                        ->scalarNode("entity_id_field")
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode("master_oauth_url")
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode("master_notify_url")
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode("master_client_id")
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode("master_client_secret")
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->booleanNode("create_new_entity")
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode("drivers")
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->prototype("scalar")
                        ->end()
                    ->end();


        //Add root for master
        $rootNode
            ->children()
                ->arrayNode("master")
                    ->children()
                        ->scalarNode("create_new_entity")
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode("entity_id_field")
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode("drivers")
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->prototype("scalar")
                        ->end();

        //Ensure that there is only one node. "client" or "master"
        $rootNode->validate()
            ->ifTrue(function($v){
                return !(is_array($v) && count($v) == 1);
            })
            ->thenInvalid("Please define exactly one node: client, master");

        return $treeBuilder;
    }
}
