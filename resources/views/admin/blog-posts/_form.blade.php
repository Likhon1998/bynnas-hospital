<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div>
        <label for="blog_category_id" class="text-sm font-medium text-navy-900">Category</label>
        <select id="blog_category_id" name="blog_category_id" required class="mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm">
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('blog_category_id') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <x-form.input name="title" label="Title" :value="$post->title" required />
    <x-form.input name="slug" label="Slug" :value="$post->slug" />
    <x-form.textarea name="excerpt" label="Short description" :value="$post->excerpt" />
    <x-form.textarea name="body" label="Article body" :value="$post->body" :rows="10" required />
    <x-form.input name="cover_image" label="Cover image URL" :value="$post->cover_image" />
    <x-form.input name="published_at" label="Publish date" type="datetime-local" :value="optional($post->published_at)->format('Y-m-d\TH:i')" />
    <x-form.checkbox name="is_published" label="Published" :checked="$post->is_published" />
    <div class="flex gap-3">
        <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
        <a href="{{ route('admin.blog-posts.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
    </div>
</form>
