<?php

namespace Motor\Core\Filter;

class Base
{

    /**
     * @var bool
     */
    protected $allowNull = false;

    /**
     * @var
     */
    protected $name;

    /**
     * @var
     */
    protected $field;

    /**
     * @var null
     */
    protected $options = null;

    /**
     * @var null
     */
    protected $join = null;

    /**
     * @var
     */
    protected $baseName;

    /**
     * @var null
     */
    protected $value = null;

    /**
     * @var null
     */
    protected $defaultValue = null;

    /**
     * @var bool
     */
    protected $visible = true;

    /**
     * @var null
     */
    protected $emptyOptionString = null;

    /**
     * @var null
     */
    protected $optionPrefix = null;

    /**
     * @var null
     */
    protected $operator = null;


    /**
     * Base constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name  = $name;
        $this->field = $name;
    }


    /**
     * @param $table
     * @return object
     */
    public function setJoin($table): object
    {
        $this->join = $table;

        return $this;
    }


    /**
     * @return string
     */
    public function getJoin(): string
    {
        return $this->join;
    }


    /**
     * @param $field
     * @return object
     */
    public function setField($field): object
    {
        $this->field = $field;

        return $this;
    }


    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }


    /**
     * @param $visible
     * @return object
     */
    public function isVisible($visible): object
    {
        $this->visible = $visible;

        // Don't allow values to be changed if the filter is not visible
        if ( ! $visible) {
            $this->setValue($this->defaultValue);
        }

        return $this;
    }


    /**
     * @return bool
     */
    public function getVisible(): bool
    {
        return $this->visible;
    }


    /**
     * @param $prefix
     * @return object
     */
    public function setOptionPrefix($prefix): object
    {
        $this->optionPrefix = $prefix;

        return $this;
    }


    /**
     * @return string
     */
    public function getOptionPrefix(): string
    {
        return $this->optionPrefix;
    }


    /**
     * @param $string
     * @return object
     */
    public function setEmptyOption($string): object
    {
        $this->emptyOptionString = $string;

        return $this;
    }


    /**
     * @return string
     */
    public function getEmptyOption(): string
    {
        return $this->emptyOptionString;
    }


    /**
     * @param array $options
     * @return object
     */
    public function setOptions($options = []): object
    {
        $this->options = $options;

        return $this;
    }


    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }


    /**
     * @param string $operator
     * @return object
     */
    public function setOperator($operator = '='): object
    {
        $this->operator = $operator;

        return $this;
    }


    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }


    /**
     * @param $defaultValue
     * @return object
     */
    public function setDefaultValue($defaultValue): object
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }


    /**
     *
     */
    public function updateValues(): void
    {
        $request = request();

        if ( ! is_null($request->get($this->name))) {
            $this->setValue($request->get($this->name));
        }
    }


    /**
     * @param $name
     */
    public function setBaseName($name): void
    {
        $this->baseName = $name;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @param $value
     */
    public function setValue($value): void
    {
        if ($value === '') {
            $value = null;
        }
        if ($this->getVisible()) {
            $this->value = $value;
            $this->setSessionValue($value);
        }
    }


    /**
     * @return string|null
     */
    public function getValue(): ?string
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
        if ( ! is_null($this->options)) {
            if ( ! isset($this->options[$returnValue])) {
                return null;
            }
        }

        return $returnValue;
    }


    /**
     * @return string|null
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }


    /**
     * @return string|null
     */
    protected function getSessionValue(): ?string
    {
        return session('filters.' . $this->baseName . '.' . $this->name, null);
    }


    /**
     * @param $value
     */
    protected function setSessionValue($value): void
    {
        session()->put('filters.' . $this->baseName . '.' . $this->name, $value);
    }


    /**
     * @param $query
     * @return object
     */
    public function query($query): object
    {
        return $query;
    }


    /**
     * @param $allow
     * @return object
     */
    public function setAllowNull($allow): object
    {
        $this->allowNull = $allow;

        return $this;
    }


    /**
     * @return bool
     */
    public function getAllowNull(): bool
    {
        return $this->allowNull;
    }

}
