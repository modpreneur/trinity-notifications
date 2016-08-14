<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Annotations;

/**
 * Class Methods.
 *
 *
 * @Annotation
 */
class Methods
{
    /** @var  array */
    protected $types;

    /**
     * @param array $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->types = (isset($metadata['types']) && $metadata['types'] != '') ? ($metadata['types']) : [];
    }

    /**
     * @param string $typeName
     *
     * @return bool
     */
    public function hasType($typeName) : bool
    {
        return in_array(strtolower($typeName), $this->getTypes());
    }

    /**
     * @return array
     */
    public function getTypes() : array
    {
        return $this->types;
    }
}
