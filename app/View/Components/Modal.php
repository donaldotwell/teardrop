<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    public $id;
    public $title;
    public $triggerText;
    public $triggerClass;
    public $contentClass;

    /**
     * Create a new component instance.
     */
    public function __construct($id, $title = null, $triggerText = null, $triggerClass = null, $contentClass = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->triggerText = $triggerText;
        $this->triggerClass = $triggerClass;
        $this->contentClass = $contentClass;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.modal');
    }
}
