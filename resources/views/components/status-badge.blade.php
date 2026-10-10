@props(['status'])

@php
    $classes = match ($status) {
        'published', 'active', 'resolved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'draft', 'submitted', 'closed', 'read' => 'bg-gray-100 text-gray-700 ring-gray-200',
        'archived', 'inactive', 'disabled' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'new', 'under_review', 'assigned', 'in_progress', 'awaiting_user' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'responded', 'referred' => 'bg-amber-50 text-amber-700 ring-amber-200',
        default => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex whitespace-nowrap rounded-md px-2.5 py-1 text-xs font-semibold capitalize ring-1 {$classes}"]) }}>
    {{ str_replace('_', ' ', $status) }}
</span>
