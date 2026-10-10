<form method="POST" action="{{ $action }}" class="space-y-6 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="faq_category_id" value="Category" />
            <select id="faq_category_id" name="faq_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Choose category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('faq_category_id', $faq->faq_category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('faq_category_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $faq->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="question" value="Question" />
        <x-text-input id="question" name="question" :value="old('question', $faq->question)" class="mt-1 block w-full" required />
        <x-input-error :messages="$errors->get('question')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="answer" value="Answer" />
        <textarea id="answer" name="answer" rows="7" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('answer', $faq->answer) }}</textarea>
        <x-input-error :messages="$errors->get('answer')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="sort_order" value="Sort Order" />
        <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="9999" :value="old('sort_order', $faq->sort_order)" class="mt-1 block w-full md:w-48" required />
        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
    </div>

    <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-6">
        <a href="{{ route('admin.faqs.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
        <x-primary-button>{{ $buttonText }}</x-primary-button>
    </div>
</form>
