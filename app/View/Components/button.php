<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class button extends Component
{
    public $type;
    public $variant;
    public $size;
    public $width;
    public $disabled;
    public $customColor;
    public $fontWeight;
    public $fontSize;
    public $src;
    public $tooltip;

    /**
     * Create a new component instance.
     */
    public function __construct($type = 'button', $variant = 'primary', $size = 'sm', $width = 'auto', $disabled = false, $customColor = null, $fontWeight = 'normal', $fontSize = '6', $src = null, $tooltip = null)
    {
        $this->type = $type;
        $this->variant = $variant;
        $this->size = $size;
        $this->width = $width;
        $this->disabled = filter_var($disabled, FILTER_VALIDATE_BOOLEAN);
        $this->customColor = $customColor;
        $this->fontWeight = $fontWeight;
        $this->fontSize = $fontSize;
        $this->src = $src;
        $this->tooltip = $tooltip;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.button');
    }
}
