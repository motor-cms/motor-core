<?php

namespace Motor\Core\Filter;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class Base
 */
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
        $this->name = $name;
        $this->field = $name;
    }

    /**
     * Set join table
     *
     * @param $table
     * @return object
     */
    public function setJoin($table): object
    {
        $this->join = $table;

        return $this;
    }

    /**
     * Get join table
     *
     * @return string
     */
    public function getJoin(): string
    {
        return $this->join;
    }

    /**
     * Set field for query
     *
     * @param $field
     * @return object
     */
    public function setField($field): object
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field for query
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Set visibility of the filter
     *
     * @param $visible
     * @return object
     */
    public function isVisible($visible): object
    {
        $this->visible = $visible;

        // Don't allow values to be changed if the filter is not visible
        if (! $visible) {
            $this->setValue($this->defaultValue);
        }

        return $this;
    }

    /**
     * Get visibility setting for filter
     *
     * @return bool
     */
    public function getVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Set option prefix
     *
     * @param $prefix
     * @return object
     */
    public function setOptionPrefix($prefix): object
    {
        $this->optionPrefix = $prefix;

        return $this;
    }

    /**
     * Get option prefix
     *
     * @return string
     */
    public function getOptionPrefix(): string
    {
        return $this->optionPrefix;
    }

    /**
     * Set empty option for a select filter
     *
     * @param $string
     * @return object
     */
    public function setEmptyOption($string): object
    {
        $this->emptyOptionString = $string;

        return $this;
    }

    /**
     * Get empty option for a select filter
     *
     * @return string
     */
    public function getEmptyOption(): string
    {
        return $this->emptyOptionString;
    }

    /**
     * Set options for select filter
     *
     * @param  array  $options
     * @return object
     */
    public function setOptions($options = []): object
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options for select filter
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set operator for query
     *
     * @param  string  $operator
     * @return object
     */
    public function setOperator($operator = '='): object
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator for query
     *
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Set default value for filter
     *
     * @param $defaultValue
     * @return object
     */
    public function setDefaultValue($defaultValue): object
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Update filter values from request
     */
    public function updateValues(): void
    {
        $request = request();

        if (! is_null($request->get($this->name))) {
            $this->setValue($request->get($this->name));
        }
    }

    /**
     * Set base name of filter
     *
     * @param $name
     */
    public function setBaseName($name): void
    {
        $this->baseName = $name;
    }

    /**
     * Get name of filter
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set value of filter
     *
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
     * Get current value of filter
     *
     * @return mixed
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
        if (! is_null($this->options)) {
            if (! isset($this->options[$returnValue])) {
                return null;
            }
        }

        return $returnValue;
    }

    /**
     * Get default value for filter
     *
     * @return string|null
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * Get session value of filter
     *
     * @return string|null
     */
    protected function getSessionValue(): ?string
    {
        return session('filters.'.$this->baseName.'.'.$this->name, null);
    }

    /**
     * Set session value of filter
     *
     * @param $value
     */
    protected function setSessionValue($value): void
    {
        session()->put('filters.'.$this->baseName.'.'.$this->name, $value);
    }

    /**
     * Get query for filter
     *
     * @param $query
     * @return object
     */
    public function query(Builder $query): object
    {
        return $query;
    }

    /**
     * Define if null is a valid filter value
     *
     * @param $allow
     * @return object
     */
    public function setAllowNull($allow): object
    {
        $this->allowNull = $allow;

        return $this;
    }

    /**
     * Get value of allowNull
     *
     * @return bool
     */
    public function getAllowNull(): bool
    {
        return $this->allowNull;
    }
}
