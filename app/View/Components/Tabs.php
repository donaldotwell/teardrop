<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tabs extends Component
{
    public $tabs;

    /**
     * Create a new component instance.
     * @param array $tabs
     */
    public function __construct($tabs = [])
    {
        $this->tabs = $tabs;
    }

    /**
     * Get the view / contents that represent the component.
     * @return View|Closure|string
     */
    public function render(): View|Closure|string
    {
        return view('components.tabs');
    }
}
