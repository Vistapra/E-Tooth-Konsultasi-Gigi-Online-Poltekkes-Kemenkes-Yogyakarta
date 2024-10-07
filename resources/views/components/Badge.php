<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Badge extends Component
{
    public $label;
    public $color;

    public function __construct($label, $color = 'gray')
    {
        $this->label = $label;
        $this->color = $color;
    }

    public function render()
    {
        return view('components.badge');
    }
}