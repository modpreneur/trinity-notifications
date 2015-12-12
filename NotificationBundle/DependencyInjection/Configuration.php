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

        // The property which will be mapped to the id which is in the notification request.
        // This field is used to fetch an entity from the database
        // Value "id" is typically on master
        // Value "masterId" is typically on client
        $rootNode->children()->scalarNode('entity_id_field')->defaultValue("id");
        $rootNode->children()->scalarNode('master_notify_url')->defaultValue("");
        $rootNode->children()->scalarNode('master_oauth_url')->defaultValue("");
        $rootNode->children()->scalarNode('master_client_id')->defaultValue("");
        $rootNode->children()->scalarNode('master_client_secret')->defaultValue("");
        $rootNode->children()->scalarNode('create_new_entity')->defaultFalse();
        $rootNode
            ->children()
                ->arrayNode("drivers")
                    ->prototype("scalar");

        return $treeBuilder;
    }
}
