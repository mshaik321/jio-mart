<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const KEYS = [
        'site_name', 'support_email', 'support_phone',
        'default_tax_rate', 'low_stock_threshold_default',
    ];

    public function index()
    {
        $this->authorize('settings.manage');

        $settings = collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => Setting::getValue($key)]);

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->authorize('settings.manage');

        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email'],
            'support_phone' => ['nullable', 'string', 'max:20'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'low_stock_threshold_default' => ['nullable', 'integer', 'min:0'],
        ]);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        return redirect()->route('admin.settings.index')->with('status', 'Settings updated.');
    }
}
