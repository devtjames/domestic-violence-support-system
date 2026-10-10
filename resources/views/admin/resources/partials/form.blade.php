<form method="POST" action="{{ $action }}" class="space-y-6 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="organization_name" value="Organization Name" />
            <x-text-input id="organization_name" name="organization_name" :value="old('organization_name', $resource->organization_name)" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('organization_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="resource_type" value="Resource Type" />
            <select id="resource_type" name="resource_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Choose type</option>
                @foreach ($resourceTypes as $type)
                    <option value="{{ $type }}" @selected(old('resource_type', $resource->resource_type) === $type)>{{ $type }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('resource_type')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('description', $resource->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <div>
            <x-input-label for="phone" value="Phone" />
            <x-text-input id="phone" name="phone" :value="old('phone', $resource->phone)" class="mt-1 block w-full" placeholder="Verified contact only" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $resource->email)" class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="website" value="Website" />
            <x-text-input id="website" name="website" type="url" :value="old('website', $resource->website)" class="mt-1 block w-full" placeholder="https://example.org" />
            <x-input-error :messages="$errors->get('website')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="address" value="Address" />
            <x-text-input id="address" name="address" :value="old('address', $resource->address)" class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="location" value="State or Location" />
            <x-text-input id="location" name="location" :value="old('location', $resource->location)" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('location')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="availability" value="Availability" />
            <x-text-input id="availability" name="availability" :value="old('availability', $resource->availability)" class="mt-1 block w-full" required placeholder="Days, times, or appointment guidance" />
            <x-input-error :messages="$errors->get('availability')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $resource->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
    </div>

    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
        Add only verified emergency contact information. Leave phone fields blank until the contact has been confirmed by an administrator.
    </div>

    <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-6">
        <a href="{{ route('admin.resources.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
        <x-primary-button>{{ $buttonText }}</x-primary-button>
    </div>
</form>
