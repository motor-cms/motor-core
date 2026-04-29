<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Motor\Core\Filter\Base;

class SelectRenderer extends Base
{
    protected ?array $options = [];

    protected ?string $operator = '=';

    /**
     * Render the filter
     *
     * @return Application|Factory|View|void
     */
    public function render()
    {
        if (! is_null($this->optionPrefix)) {
            foreach ($this->options as $key => $value) {
                $this->options[$key] = $this->optionPrefix.': '.$value;
            }
        }

        if ($this->visible) {
            return view('motor-backend::filters.select', [
                'name'              => $this->name,
                'options'           => $this->options,
                'value'             => $this->getValue(),
                'emptyOptionString' => $this->emptyOptionString,
            ]);
        }
    }

    /**
     * Run query for the filter
     */
    public function query(Builder|\Laravel\Scout\Builder $query): object
    {
        if ($query instanceof Builder) {
            return $query->where($query->getModel()
                ->getTable().'.'.$this->field, $this->operator, $this->getValue());
        }

        return $query->where($this->name, $this->getValue());
    }
}
