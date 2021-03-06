<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
     *
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('trinity_notification');

        $rootNode
            ->children()
                ->arrayNode('entities')
                    ->normalizeKeys(false)
                    ->performNoDeepMerging()
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()

            //full class names of the forms
                ->arrayNode('forms')
                    ->normalizeKeys(false)
                    ->performNoDeepMerging()
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

                ->booleanNode('disable_time_violations')
                    ->isRequired()
                ->end()

                ->scalarNode('entity_id_field')
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ;

        $this->addClientSecretProviderNode($rootNode);
        $this->addClientUnknownEntityStrategyNode($rootNode);
        $this->addNotificationLoggerNode($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     *
     * @throws \RuntimeException
     */
    protected function addClientSecretProviderNode(ArrayNodeDefinition $node)
    {
        //reference to a service - starting with '@'
        $node->children()->scalarNode('client_secret_provider')
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
        ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     *
     * @throws \RuntimeException
     */
    protected function addClientUnknownEntityStrategyNode(ArrayNodeDefinition $node)
    {
        //reference to a service - starting with '@'
        $node->children()->scalarNode('unknown_entity_strategy')
            ->cannotBeEmpty()
            ->defaultValue(null)
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
        ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     *
     * @throws \RuntimeException
     */
    protected function addNotificationLoggerNode(ArrayNodeDefinition $node)
    {
        //reference to a service - starting with '@'
        $node->children()->scalarNode('notification_logger')
            ->cannotBeEmpty()
            ->isRequired()
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
        ->end();
    }
}
