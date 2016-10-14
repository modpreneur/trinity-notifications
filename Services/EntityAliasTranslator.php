<?php

namespace Trinity\NotificationBundle\Services;

use Trinity\NotificationBundle\Exception\EntityAliasNotFoundException;

/**
 * Class EntityNameTranslator.
 */
class EntityAliasTranslator
{
    /**
     * @var array Indexed array of entities' aliases and real class names.
     *            format:
     *            [
     *            "user" => "App\Entity\User,
     *            "product" => "App\Entity\Product,
     *            ...
     *            ]
     */
    protected $entities;

    /**
     * EntityAliasTranslator constructor.
     *
     * @param array $entities
     */
    public function __construct(array $entities)
    {
        $this->entities = $entities;
    }

    /**
     * Get alias from full class name.
     * Example: 'App\Entity\Product' => 'product'.
     *
     * @param string $class
     *
     * @return string
     *
     * @throws EntityAliasNotFoundException
     */
    public function getAliasFromClass(string $class)
    {
        //remove doctrine proxy namespace
        $class = str_replace('Proxies\__CG__\\', '', $class);
        $alias = array_search($class, $this->entities, true);

        if ($alias === false) {
            $exception = new EntityAliasNotFoundException('Could not find an alias for class: '.$class);
            $exception->setClass($class);

            throw $exception;
        } else {
            return $alias;
        }
    }

    /**
     * Get full class name from entity alias.
     * Example: 'product' => 'App\Entity\Product'.
     *
     * @param string $class
     *
     * @return string
     *
     * @throws EntityAliasNotFoundException
     */
    public function getClassFromAlias(string $class)
    {
        if (!array_key_exists($class, $this->entities)) {
            $exception = new EntityAliasNotFoundException('Could not find an alias for class: '.$class);
            $exception->setClass($class);

            throw $exception;
        } else {
            return $this->entities[$class];
        }
    }
}
