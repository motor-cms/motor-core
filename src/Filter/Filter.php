<?php

namespace Motor\Core\Filter;

use Illuminate\Support\Facades\Auth;
use Motor\Core\Filter\Renderers\SelectRenderer;

class Filter
{
    protected string $parent;

    protected array $filters = [];

    protected array $sortableFields = [];

    protected array $sorting = ['id', 'ASC'];

    public function __construct(string|object $parent)
    {
        $this->parent = is_object($parent) ? get_class($parent) : $parent;
    }

    public function get(string $name): ?Base
    {
        return $this->filters[$name] ?? null;
    }

    public function add(Base $filter): Base
    {
        $filter->setBaseName($this->parent);
        $filter->updateValues();
        $this->filters[$filter->getName()] = $filter;

        return $filter;
    }

    public function addClientFilter(): void
    {
        if (Auth::user()->client_id > 0) {
            $this->add(new SelectRenderer('client_id'))
                ->setOptions([Auth::user()->client_id => Auth::user()->client->name])
                ->setDefaultValue(Auth::user()->client_id)
                ->isVisible(false);
        } else {
            $clients = config('motor-admin.models.client')::orderBy('name')->pluck('name', 'id');
            $this->add(new SelectRenderer('client_id'))->setOptions($clients);
        }
    }

    public function filters(): array
    {
        return $this->filters;
    }
}
