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
        // Phase 7 of ZRMDEV-165: read the first pivot client instead of the
        // dropped users.client_id scalar. V1-strict semantics: a multi-client
        // user collapses to their first pivot row, matching the "primary
        // client" intent that the legacy column used to encode. SuperAdmin /
        // empty-pivot users fall through to the full client list.
        $client = Auth::user()?->clients->first();

        if ($client !== null) {
            $this->add(new SelectRenderer('client_id'))
                ->setOptions([$client->id => $client->name])
                ->setDefaultValue($client->id)
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
