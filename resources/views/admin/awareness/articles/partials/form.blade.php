<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="title" value="Title" />
            <x-text-input id="title" name="title" :value="old('title', $article->title)" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="slug" value="Slug" />
            <x-text-input id="slug" name="slug" :value="old('slug', $article->slug)" class="mt-1 block w-full" placeholder="Generated from title if blank" />
            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <div>
            <x-input-label for="awareness_category_id" value="Category" />
            <select id="awareness_category_id" name="awareness_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Choose category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('awareness_category_id', $article->awareness_category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('awareness_category_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $article->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="published_at" value="Published Date" />
            <x-text-input id="published_at" name="published_at" type="datetime-local" :value="old('published_at', $article->published_at?->format('Y-m-d\\TH:i'))" class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('published_at')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="featured_image" value="Featured Image" />
        <input id="featured_image" name="featured_image" type="file" accept="image/png,image/jpeg,image/jpg,image/webp" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200">
        <x-input-error :messages="$errors->get('featured_image')" class="mt-2" />
        @if ($article->featured_image)
            <div class="mt-3 flex items-center gap-4">
                <img src="{{ Storage::url($article->featured_image) }}" alt="" class="h-20 w-28 rounded-md object-cover">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="remove_featured_image" value="1" class="rounded border-gray-300 text-rose-700 shadow-sm focus:ring-rose-700">
                    Remove current image
                </label>
            </div>
        @endif
    </div>

    <div>
        <x-input-label for="short_description" value="Short Description" />
        <textarea id="short_description" name="short_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('short_description', $article->short_description) }}</textarea>
        <x-input-error :messages="$errors->get('short_description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="content" value="Full Content" />
        <textarea id="content" name="content" rows="12" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('content', $article->content) }}</textarea>
        <x-input-error :messages="$errors->get('content')" class="mt-2" />
    </div>

    <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-6">
        <a href="{{ route('admin.awareness.articles.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
        <x-primary-button>{{ $buttonText }}</x-primary-button>
    </div>
</form>
