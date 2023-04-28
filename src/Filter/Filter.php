<?php

namespace Motor\Core\Filter;

use Illuminate\Support\Facades\Auth;
use Motor\Core\Filter\Renderers\SelectRenderer;

/**
 * Class Filter
 */
class Filter
{
    /**
     * @var string
     */
    protected $parent;

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $sortableFields = [];

    /**
     * @var array
     */
    protected $sorting = ['id', 'ASC'];

    /**
     * Filter constructor.
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
        if (is_object($parent)) {
            $this->parent = get_class($parent);
        }
    }

    /**
     * @return object|null
     */
    public function get($name): ?Base
    {
        if (isset($this->filters[$name])) {
            return $this->filters[$name];
        }

        return null;
    }

    public function add(Base $filter): Base
    {
        $filter->setBaseName($this->parent);
        $filter->updateValues();
        $this->filters[$filter->getName()] = $filter;

        return $filter;
    }

    /**
     * Add the default client filter
     */
    public function addClientFilter(): void
    {
        if (Auth::user()->client_id > 0) {
            $this->add(new SelectRenderer('client_id'))
                ->setOptions([Auth::user()->client_id => Auth::user()->client->name])
                ->setDefaultValue(Auth::user()->client_id)
                ->isVisible(false);
        } else {
            $clients = config('motor-admin.models.client')::orderBy('name', 'ASC')->pluck('name', 'id');
            $this->add(new SelectRenderer('client_id'))->setOptions($clients);
        }
    }

    /**
     * Return array of all currently set filters
     */
    public function filters(): array
    {
        return $this->filters;
    }
}
