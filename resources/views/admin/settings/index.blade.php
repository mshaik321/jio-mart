<x-layouts.admin title="Settings">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Settings</h2>
    <form action="{{ route('admin.settings.update') }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
            <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
            <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email']) }}" class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
            <input type="text" name="support_phone" value="{{ old('support_phone', $settings['support_phone']) }}" class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Default Tax Rate (%)</label>
            <input type="number" step="0.01" name="default_tax_rate" value="{{ old('default_tax_rate', $settings['default_tax_rate']) }}" class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Default Low Stock Threshold</label>
            <input type="number" name="low_stock_threshold_default" value="{{ old('low_stock_threshold_default', $settings['low_stock_threshold_default']) }}" class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 py-2 rounded-md text-sm">Save Settings</button>
    </form>
</x-layouts.admin>
