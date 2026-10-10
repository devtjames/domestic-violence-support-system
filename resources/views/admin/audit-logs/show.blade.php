<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Audit log detail</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ str($log->action)->headline() }}</h2>
            </div>
            <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">All Audit Logs</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                <dl class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Timestamp</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $log->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Actor</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $log->actor?->name ?? 'System' }}{{ $log->actor?->role ? ' - '.str($log->actor->role)->headline() : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Module</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ str($log->module)->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Action</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $log->action }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-semibold text-gray-900">Description</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $log->description }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">IP Address</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $log->ip_address ?: 'Not recorded' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Auditable Record</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : 'Not linked' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-semibold text-gray-900">User Agent</dt>
                        <dd class="mt-1 break-words text-sm text-gray-600">{{ $log->user_agent ?: 'Not recorded' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Metadata</h3>
                @if ($log->metadata)
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        @foreach ($log->metadata as $key => $value)
                            <div class="rounded-md bg-gray-50 p-3">
                                <dt class="font-semibold text-gray-900">{{ str($key)->headline() }}</dt>
                                <dd class="mt-1 break-words text-gray-600">{{ is_scalar($value) ? $value : json_encode($value) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="mt-3 rounded-md bg-gray-50 p-4 text-sm text-gray-600">No extra metadata was recorded.</p>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
