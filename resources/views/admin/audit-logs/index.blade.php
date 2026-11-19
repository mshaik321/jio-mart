<x-layouts.admin title="Audit Logs">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Audit Logs</h2>
    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Date</th><th class="px-4 py-2">User</th><th class="px-4 py-2">Action</th><th class="px-4 py-2">Changes</th></tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 text-gray-500">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-2">{{ $log->user->name ?? 'System' }}</td>
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $log->action }}</td>
                        <td class="px-4 py-2 text-gray-500 text-xs">
                            @if($log->old_values)
                                <span>From: {{ json_encode($log->old_values) }}</span><br>
                            @endif
                            @if($log->new_values)
                                <span>To: {{ json_encode($log->new_values) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</x-layouts.admin>
