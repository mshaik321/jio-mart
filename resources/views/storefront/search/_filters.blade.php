<form method="GET" class="bg-white border border-gray-200 rounded-lg p-4 space-y-5">
    @foreach(request()->except(['brands', 'min_price', 'max_price', 'in_stock', 'page', 'sort']) as $key => $value)
        @if(is_string($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    @if($brands->isNotEmpty())
        <div>
            <p class="text-sm font-semibold text-gray-900 mb-2">{{ __('Brand') }}</p>
            <div class="space-y-1 max-h-48 overflow-y-auto">
                @foreach($brands as $brand)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="brands[]" value="{{ $brand->id }}"
                            @checked(in_array($brand->id, (array) request('brands', [])))
                            class="rounded border-gray-300 text-emerald-700">
                        {{ $brand->name }}
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <p class="text-sm font-semibold text-gray-900 mb-2">{{ __('Price (₹)') }}</p>
        <div class="flex gap-2">
            <input type="number" name="min_price" min="0" value="{{ request('min_price') }}" placeholder="{{ __('Min') }}" aria-label="{{ __('Minimum price') }}" class="w-1/2 rounded-md border-gray-300 text-sm">
            <input type="number" name="max_price" min="0" value="{{ request('max_price') }}" placeholder="{{ __('Max') }}" aria-label="{{ __('Maximum price') }}" class="w-1/2 rounded-md border-gray-300 text-sm">
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="in_stock" value="1" @checked(request('in_stock')) class="rounded border-gray-300 text-emerald-700">
        {{ __('In stock only') }}
    </label>

    <div>
        <p class="text-sm font-semibold text-gray-900 mb-2">{{ __('Sort by') }}</p>
        <select name="sort" class="w-full rounded-md border-gray-300 text-sm" aria-label="{{ __('Sort by') }}">
            <option value="">{{ __('Relevance') }}</option>
            <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('Price: Low to High') }}</option>
            <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('Price: High to Low') }}</option>
            <option value="newest" @selected(request('sort') === 'newest')>{{ __('Newest') }}</option>
        </select>
    </div>

    <button type="submit" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold py-2 rounded-md">
        {{ __('Apply Filters') }}
    </button>
</form>
