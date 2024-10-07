<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DashboardCard extends Component
{
    public $route;
    public $title;
    public $count;

    public function __construct($route = null, $title = '', $count = 0)
    {
        $this->route = $route;
        $this->title = $title;
        $this->count = $count;
    }

    public function render()
    {
        return view('components.dashboard-card');
    }
}