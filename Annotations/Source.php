<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Annotations;

/**
 * Class Source.
 *
 *
 * @Annotation
 */
class Source
{
    /** @var array */
    protected $columns;

    /**
     * @param array $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->columns = (isset($metadata['columns']) && $metadata['columns'] != '') ? array_map(
            'trim',
            explode(',', $metadata['columns'])
        ) : [];
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return bool
     */
    public function hasColumns()
    {
        return !empty($this->columns);
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function hasColumn( $column)
    {
        $cols = [];
        foreach ($this->columns as $c) {
            $cols[] = strtolower($c);
        }

        return in_array(strtolower($column), $cols);
    }
}
