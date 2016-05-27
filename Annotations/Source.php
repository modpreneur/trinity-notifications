<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Annotations;

/**
 * Class Source
 *
 * @package Trinity\NotificationBundle\Annotations
 *
 * @Annotation
 */
class Source
{
    /** @var array */
    protected $columns;

    /** @var bool */
    protected $allColumnsSelected = false;


    /**
     * @param array $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->columns = (isset($metadata['columns']) && $metadata['columns'] != '') ? array_map(
            'trim',
            explode(',', $metadata['columns'])
        ) : [];

        foreach ($this->getColumns() as &$column) {
            if ($column == '*') {
                $this->allColumnsSelected = true;
                unset($column);

                return;
            };
        }
    }


    /**
     * @return array
     */
    public function getColumns() : array
    {
        return $this->columns;
    }


    /**
     * @return bool
     */
    public function hasColumns() : bool
    {
        return !empty($this->columns);
    }


    /**
     * Rename?
     *
     * @return bool
     */
    public function isAllColumnsSelected() : bool
    {
        return $this->allColumnsSelected;
    }


    /**
     * @param string $column
     *
     * @return bool
     */
    public function hasColumn(string $column) : bool
    {
        $cols = [];
        foreach ($this->columns as $c) {
            $cols[] = strtolower($c);
        }

        return in_array(strtolower($column), $cols);
    }
}
