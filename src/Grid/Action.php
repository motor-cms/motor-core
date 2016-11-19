<?php

namespace Motor\Core\Grid;

use Auth;

class Action extends Base
{

    protected $link = '';

    protected $label = '';

    protected $parameters = [];

    protected $value = '';

    protected $type = 'button';

    protected $permission = '';

    protected $conditionColumn = null;

    protected $conditionValue = null;

    protected $conditionOperator = '=';


    public function __construct($label, $link, $parameters = [])
    {
        $this->label      = $label;
        $this->link       = $link;
        $this->parameters = $parameters;

        if (isset( $parameters['type'] )) {
            $this->type = $parameters['type'];
        }
    }


    public function needsPermissionTo($permission)
    {
        $this->permission = $permission;

        return $this;
    }


    public function onCondition($column, $value, $operator = '=')
    {
        $this->conditionColumn = $column;
        $this->conditionValue = $value;
        $this->conditionoperator = $operator;

        return $this;
    }


    public function getLabel()
    {
        return $this->label;
    }


    public function render($record)
    {
        if ($this->permission != '' && ( ! Auth::user()->hasRole('SuperAdmin') && ! Auth::user()->hasPermissionTo($this->permission) )) {
            return false;
        }

        if (!is_null($this->conditionColumn)) {
            $condition = false;

            switch ($this->conditionOperator) {
                case '=':
                    if ($record->{$this->conditionColumn} == $this->conditionValue) {
                        $condition = true;
                    }
                    break;
                case '>':
                    if ($record->{$this->conditionColumn} > $this->conditionValue) {
                        $condition = true;
                    }
                    break;
                case '<':
                    if ($record->{$this->conditionColumn} < $this->conditionValue) {
                        $condition = true;
                    }
                    break;
                case '>=':
                    if ($record->{$this->conditionColumn} >= $this->conditionValue) {
                        $condition = true;
                    }
                    break;
                case '<=':
                    if ($record->{$this->conditionColumn} <= $this->conditionValue) {
                        $condition = true;
                    }
                    break;
            }

            if (!$condition) {
                return false;
            }
        }

        switch ($this->type) {
            case 'form':
                $view = 'grid.actions.form';
                break;
            case 'duplicate':
                $view = 'grid.actions.duplicate';
                break;
            case 'edit':
                $view = 'grid.actions.edit';
                break;
            case 'delete';
                $view = 'grid.actions.delete';
                break;
            default:
                $view = 'grid.actions.button';
        }

        return \View::make($view, [ 'link' => $this->link, 'record' => $record, 'label' => $this->label, 'parameters' => $this->parameters ])->render();
    }
}