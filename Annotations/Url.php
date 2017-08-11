<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Annotations;

use Doctrine\Common\Annotations\AnnotationException;

/**
 * Class Url.
 *
 * @Annotation
 */
class Url
{
    /** @var  string|null */
    protected $postfix;

    /** @var  array */
    protected $methods = [];

    /**
     * @param array $metadata
     *
     * @throws AnnotationException
     */
    public function __construct(array $metadata = [])
    {
        $this->postfix = null;

        if (isset($metadata['postfix']) && $metadata['postfix'] != '') {
            $this->postfix = $metadata['postfix'];
        }

        if ($this->postfix === null) {
            throw new AnnotationException('Annotation error: Url postfix is not set.');
        }

        $this->methods = (isset($metadata['methods']) && $metadata['methods'] != '') ? $metadata['methods'] : [];
    }

    /**
     * @return string
     */
    public function getPostfix() : string
    {
        return $this->postfix;
    }

    /**
     * @return bool
     */
    public function isWithoutMethods() : bool
    {
        return empty($this->getMethods()) || count($this->getMethods()) == 0;
    }

    /**
     * @return array
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    public function hasMethod(string $method) : bool
    {
        if (empty($this->getMethods())) {
            return true;
        }

        return in_array(strtolower($method), $this->getMethods());
    }
}
