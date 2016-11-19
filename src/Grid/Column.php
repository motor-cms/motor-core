<?php

namespace Motor\Core\Grid;

class Column extends Base
{

    protected $name;

    protected $label = '';

    protected $sortable = false;

    protected $sortableField = '';

    protected $filters = [ ];

    protected $cellClosure = null;

    protected $renderer = 'Motor\Core\Grid\Renderers\TextRenderer';

    protected $renderOptions;

    /**
     * Column constructor.
     *
     * @param      $name
     * @param      $label
     * @param bool $sortable
     */
    public function __construct($name, $label, $sortable = false)
    {
        $this->name = $name;

        // Check for filters
        $filter = strstr($name, '|');
        if ($filter) {
            $this->name = strstr($name, '|', true);
            $this->setFilters(trim($filter, '|'));
        }

        $this->label = $label;
        $this->setSortable($sortable);
    }


    /**
     * Set column type
     *
     * @param $type
     */
    public function renderer($renderer, $options=[])
    {
        $this->renderer = $renderer;
        $this->renderOptions = $options;

        return $this;
    }


    /**
     * Get renderer
     *
     * @return string
     */
    public function getRenderer()
    {
        return $this->renderer;
    }


    /**
     * Get options for renderer
     *
     * @return mixed
     */
    public function getRenderOptions()
    {
        return $this->renderOptions;
    }


    /**
     * Get name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Set sortable status and field
     *
     * @param $sortable
     *
     * @return $this
     */
    protected function setSortable($sortable)
    {
        $this->sortable = (bool) $sortable;
        if ($this->sortable) {
            $this->sortableField = ( is_string($sortable) ) ? $sortable : $this->name;
        }

        return $this;
    }


    /**
     * Return sortable status
     *
     * @return bool
     */
    public function isSortable()
    {
        return $this->sortable;
    }


    /**
     * Get column label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }


    /**
     * Get sortable field
     *
     * @return null
     */
    public function getSortableField()
    {
        return $this->sortableField;
    }


    /**
     * Set filters if available
     *
     * @param $filters
     *
     * @return $this
     */
    protected function setFilters($filters)
    {
        if (is_string($filters)) {
            $filters = explode('|', trim($filters));
        }
        if (is_array($filters)) {
            $this->filters = $filters;
        }

        return $this;
    }


    /**
     * Get filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }


    /**
     * Check for cell closure
     *
     * @return bool
     */
    public function hasCellClosure()
    {
        if ( ! is_null($this->cellClosure)) {
            return true;
        }

        return false;
    }


    /**
     * Get cell closure
     *
     * @return mixed
     */
    public function getCellClosure()
    {
        return $this->cellClosure;
    }


    /**
     * Set cell closure
     *
     * @param \Closure $closure
     *
     * @return $this
     */
    public function cell(\Closure $closure)
    {
        $this->cellClosure = $closure;

        return $this;
    }
}