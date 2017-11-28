<?php

namespace Motor\Core\Filter;

class Base
{

    protected $allowNull = false;

    protected $name;

    protected $field;

    protected $options = null;

    protected $baseName;

    protected $value = null;

    protected $defaultValue = null;

    protected $visible = true;

    protected $emptyOptionString = null;

    protected $optionPrefix = null;


    public function __construct($name)
    {
        $this->name  = $name;
        $this->field = $name;
    }

    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }


    public function isVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    public function setOptionPrefix($prefix)
    {
        $this->optionPrefix = $prefix;

        return $this;
    }

    public function setEmptyOption($string)
    {
        $this->emptyOptionString = $string;

        return $this;
    }


    public function setOptions($options = [])
    {
        $this->options = $options;

        return $this;
    }


    public function setOperator($operator = '=')
    {
        $this->operator = $operator;

        return $this;
    }


    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }


    public function updateValues()
    {
        $request = request();

        if ( ! is_null($request->get($this->name))) {
            $this->setValue($request->get($this->name));
        }
    }


    /**
     * @param $name
     */
    public function setBaseName($name)
    {
        $this->baseName = $name;
    }


    /**
     * @param $name
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param $value
     */
    public function setValue($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->value = $value;
        $this->setSessionValue($value);
    }


    /**
     * @return mixed|null
     */
    public function getValue()
    {
        $returnValue = $this->value;
        if (is_null($this->value)) {
            $returnValue = $this->getSessionValue();
            $this->value = $returnValue;
            if (is_null($this->value)) {
                $returnValue = $this->defaultValue;
            }
        }

        // Check if the returnValue is allowed from the options array
        if (!is_null($this->options)) {
            if ( ! isset($this->options[$returnValue])) {
                return null;
            }
        }

        return $returnValue;
    }


    /**
     * @return mixed
     */
    protected function getSessionValue()
    {
        return session('filters.' . $this->baseName . '.' . $this->name, null);
    }


    /**
     * @param $value
     */
    protected function setSessionValue($value)
    {
        return session()->put('filters.' . $this->baseName . '.' . $this->name, $value);
    }


    public function query($query)
    {
        return $query;
    }


    public function setAllowNull($allow)
    {
        $this->allowNull = $allow;

        return $this;
    }


    public function getAllowNull()
    {
        return $this->allowNull;
    }

}
