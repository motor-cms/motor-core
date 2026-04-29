<?php

namespace Motor\Core\Filter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Base
{
    protected bool $allowNull = false;

    protected string $name;

    protected string $field;

    protected ?array $options = null;

    protected ?string $join = null;

    protected ?string $baseName = null;

    protected mixed $value = null;

    protected mixed $defaultValue = null;

    protected bool $visible = true;

    protected ?string $emptyOptionString = null;

    protected ?string $optionPrefix = null;

    protected ?string $operator = null;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->field = $name;
    }

    public function setJoin(string $table): static
    {
        $this->join = $table;

        return $this;
    }

    public function getJoin(): ?string
    {
        return $this->join;
    }

    public function setField(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function isVisible(bool $visible): static
    {
        $this->visible = $visible;

        if (! $visible) {
            $this->value = $this->defaultValue;
        }

        return $this;
    }

    public function getVisible(): bool
    {
        return $this->visible;
    }

    public function setOptionPrefix(?string $prefix): static
    {
        $this->optionPrefix = $prefix;

        return $this;
    }

    public function getOptionPrefix(): ?string
    {
        return $this->optionPrefix;
    }

    public function setEmptyOption(string $string): static
    {
        $this->emptyOptionString = $string;

        return $this;
    }

    public function getEmptyOption(): ?string
    {
        return $this->emptyOptionString;
    }

    public function setOptions(array|Collection $options = []): static
    {
        $this->options = $options instanceof Collection ? $options->all() : $options;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOperator(string $operator = '='): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setDefaultValue(mixed $defaultValue): static
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function updateValues(): void
    {
        $request = request();

        if ($request->has($this->name)) {
            $this->setValue($request->get($this->name));
        }
    }

    public function setBaseName(string $name): void
    {
        $this->baseName = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setValue(mixed $value): void
    {
        if ($value === '') {
            $value = null;
        }
        if ($this->getVisible()) {
            $this->value = $value;
        }
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    protected function getSessionValue(): ?string
    {
        return session('filters.'.$this->baseName.'.'.$this->name, null);
    }

    protected function setSessionValue(mixed $value): void
    {
        session()->put('filters.'.$this->baseName.'.'.$this->name, $value);
    }

    public function query(Builder $query): object
    {
        return $query;
    }

    public function setAllowNull(bool $allow): static
    {
        $this->allowNull = $allow;

        return $this;
    }

    public function getAllowNull(): bool
    {
        return $this->allowNull;
    }
}
