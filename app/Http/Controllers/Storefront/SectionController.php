<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SectionController extends Controller
{
    public function housekeeping(): View
    {
        return $this->comingSoon('housekeeping');
    }

    public function food(): View
    {
        return $this->comingSoon('food');
    }

    public function medicines(): View
    {
        return $this->comingSoon('medicines');
    }

    private function comingSoon(string $key): View
    {
        return view('storefront.sections.coming-soon', [
            'sectionKey' => $key,
            'section' => config("storefront.sections.{$key}"),
        ]);
    }
}
