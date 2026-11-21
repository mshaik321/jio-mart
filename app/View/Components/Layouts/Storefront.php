<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;
use Illuminate\View\View;

class Storefront extends Component
{
    /**
     * @param  string  $section  Key of the active top-level shop section (see config/storefront.php).
     */
    public function __construct(public ?string $title = null, public string $section = 'groceries') {}

    public function render(): View
    {
        return view('layouts.storefront');
    }
}
