<form method="POST" action="{{ $action }}" class="space-y-6 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" :value="old('name', $category->name)" class="mt-1 block w-full" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="slug" value="Slug" />
        <x-text-input id="slug" name="slug" :value="old('slug', $category->slug)" class="mt-1 block w-full" placeholder="Generated from name if blank" />
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $category->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $category->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="sort_order" value="Sort Order" />
            <x-text-input id="sort_order" name="sort_order" type="number" min="0" :value="old('sort_order', $category->sort_order ?? 0)" class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
        </div>
    </div>

    <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-6">
        <a href="{{ route('admin.awareness.categories.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
        <x-primary-button>{{ $buttonText }}</x-primary-button>
    </div>
</form>
