<form method="GET" class="bg-white border border-gray-200 rounded-lg p-4 space-y-5">
    @foreach(request()->except(['brands', 'min_price', 'max_price', 'in_stock', 'page']) as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    @if($brands->isNotEmpty())
        <div>
            <p class="text-sm font-semibold text-gray-900 mb-2">Brand</p>
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
        <p class="text-sm font-semibold text-gray-900 mb-2">Price (₹)</p>
        <div class="flex gap-2">
            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-1/2 rounded-md border-gray-300 text-sm">
            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-1/2 rounded-md border-gray-300 text-sm">
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="in_stock" value="1" @checked(request('in_stock')) class="rounded border-gray-300 text-emerald-700">
        In stock only
    </label>

    <div>
        <p class="text-sm font-semibold text-gray-900 mb-2">Sort by</p>
        <select name="sort" class="w-full rounded-md border-gray-300 text-sm">
            <option value="">Relevance</option>
            <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
            <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
            <option value="newest" @selected(request('sort') === 'newest')>Newest</option>
        </select>
    </div>

    <button type="submit" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold py-2 rounded-md">
        Apply Filters
    </button>
</form>
