<form method="POST" action="{{ $action }}" class="space-y-6 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="name" value="Full Name" />
            <x-text-input id="name" name="name" :value="old('name', $officer->name)" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $officer->email)" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <div>
            <x-input-label for="staff_id" value="Staff ID" />
            <x-text-input id="staff_id" name="staff_id" :value="old('staff_id', $officer->staff_id)" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('staff_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" value="Phone" />
            <x-text-input id="phone" name="phone" :value="old('phone', $officer->phone)" class="mt-1 block w-full" placeholder="Optional" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="gender" value="Gender" />
            <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Not recorded</option>
                @foreach ($genders as $gender)
                    <option value="{{ $gender }}" @selected(old('gender', $officer->gender) === $gender)>{{ str($gender)->headline() }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <div>
            <x-input-label for="specialization" value="Specialization" />
            <select id="specialization" name="specialization" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Choose specialization</option>
                @foreach ($specializations as $specialization)
                    <option value="{{ $specialization }}" @selected(old('specialization', $officer->specialization) === $specialization)>{{ $specialization }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('specialization')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="account_status" value="Status" />
            <select id="account_status" name="account_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @foreach ($accountStatuses as $status)
                    <option value="{{ $status }}" @selected(old('account_status', $officer->account_status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('account_status')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="date_joined" value="Date Joined" />
            <x-text-input id="date_joined" name="date_joined" type="date" :value="old('date_joined', $officer->date_joined?->format('Y-m-d') ?? $officer->date_joined)" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('date_joined')" class="mt-2" />
        </div>
    </div>

    @if ($method === 'POST')
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-input-label for="password" value="Temporary Password" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Confirm Temporary Password" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>
    @endif

    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
        Deactivate officers instead of deleting staff records. Existing case assignments and activity history remain linked to the officer account.
    </div>

    <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-6">
        <a href="{{ route('admin.support-officers.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
        <x-primary-button>{{ $buttonText }}</x-primary-button>
    </div>
</form>
