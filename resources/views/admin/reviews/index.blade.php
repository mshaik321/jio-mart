<x-layouts.admin title="Reviews">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Reviews</h2>

    <form method="GET" class="mb-4">
        <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
            <option value="">All statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>
    </form>

    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Product</th><th class="px-4 py-2">Customer</th><th class="px-4 py-2">Rating</th><th class="px-4 py-2">Review</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($reviews as $review)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $review->product->name }}</td>
                        <td class="px-4 py-2">{{ $review->customer->user->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $review->rating }}/5</td>
                        <td class="px-4 py-2 text-gray-600 max-w-xs truncate">{{ $review->title }} {{ $review->body }}</td>
                        <td class="px-4 py-2">{{ ucfirst($review->status->value) }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            @can('reviews.moderate')
                                @if($review->status->value !== 'approved')
                                    <form action="{{ route('admin.reviews.moderate', $review) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="text-brand-700 hover:underline">Approve</button>
                                    </form>
                                @endif
                                @if($review->status->value !== 'rejected')
                                    <form action="{{ route('admin.reviews.moderate', $review) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="text-red-600 hover:underline">Reject</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $reviews->links() }}</div>
</x-layouts.admin>
