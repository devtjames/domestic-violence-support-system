<form method="POST" action="{{ route('cases.store') }}" enctype="multipart/form-data" class="space-y-6 rounded-md border border-gray-200 bg-white p-6 shadow-sm" x-data="{ urgency: @js(old('urgency', $case->urgency)) }">
    @csrf

    <div>
        <x-input-label for="abuse_type_id" value="Type of Concern" />
        <select id="abuse_type_id" name="abuse_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">Choose the closest option</option>
            @foreach ($abuseTypes as $abuseType)
                <option value="{{ $abuseType->id }}" @selected((string) old('abuse_type_id') === (string) $abuseType->id)>{{ $abuseType->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('abuse_type_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="subject" value="Subject" />
        <x-text-input id="subject" name="subject" :value="old('subject', $case->subject)" class="mt-1 block w-full" required />
        <x-input-error :messages="$errors->get('subject')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('description', $case->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="urgency" value="Urgency" />
            <select id="urgency" name="urgency" x-model="urgency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @foreach ($urgencies as $urgency)
                    <option value="{{ $urgency }}" @selected(old('urgency', $case->urgency) === $urgency)>{{ \Illuminate\Support\Str::headline($urgency) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('urgency')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="preferred_contact_method" value="Preferred Contact Method" />
            <select id="preferred_contact_method" name="preferred_contact_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">No preference</option>
                @foreach ($contactMethods as $value => $label)
                    <option value="{{ $value }}" @selected(old('preferred_contact_method', $case->preferred_contact_method) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('preferred_contact_method')" class="mt-2" />
        </div>
    </div>

    <div x-show="urgency === 'critical'" x-cloak class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-6 text-rose-900">
        {{ $criticalCaseGuidance }}
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="safe_contact_time" value="Safe Contact Time" />
            <x-text-input id="safe_contact_time" name="safe_contact_time" :value="old('safe_contact_time', $case->safe_contact_time)" class="mt-1 block w-full" placeholder="Optional" />
            <x-input-error :messages="$errors->get('safe_contact_time')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="safe_contact_instruction" value="Safe Contact Instructions" />
            <textarea id="safe_contact_instruction" name="safe_contact_instruction" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional">{{ old('safe_contact_instruction', $case->safe_contact_instruction) }}</textarea>
            <x-input-error :messages="$errors->get('safe_contact_instruction')" class="mt-2" />
        </div>
    </div>

    <div class="rounded-md border border-teal-200 bg-teal-50 px-4 py-3 text-sm leading-6 text-teal-900">
        Submit only what is needed for support review. This platform does not dispatch emergency assistance.
    </div>

    <div>
        <x-input-label for="attachments" value="Optional Attachments" />
        <input id="attachments" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" class="mt-2 block w-full rounded-md border border-gray-300 text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200" />
        <p class="mt-2 text-xs leading-5 text-gray-500">JPG, JPEG, PNG, or PDF only. Maximum 3 files, 5 MB each. Files are stored privately and only available to authorized case users.</p>
        <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
        <x-input-error :messages="$errors->get('attachments.*')" class="mt-2" />
    </div>

    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('cases.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
        <x-primary-button>Submit Support Request</x-primary-button>
    </div>
</form>
