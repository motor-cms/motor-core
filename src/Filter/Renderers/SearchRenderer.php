<?php

namespace Motor\Core\Filter\Renderers;

use Motor\Core\Filter\Base;

class SearchRenderer extends Base
{

    public function render()
    {
        return view('motor-backend::filters.search', [ 'value' => $this->getValue() ]);
    }


    public function query($query)
    {
        return $query->search($this->getValue());
    }
}