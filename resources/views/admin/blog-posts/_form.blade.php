<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.blog-posts.index')"
    lede="Prepare the article first, then set when and how it should appear on the website."
>
    <x-admin.form-section title="Article" note="Category, title, and short description for the blog list.">
        <x-form.select name="blog_category_id" label="Category" required>
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </x-form.select>
        <x-form.input name="title" label="Title" :value="$post->title" required />
        <x-form.input name="slug" label="Slug" :value="$post->slug" />
        <div class="form-span-2">
            <x-form.textarea name="excerpt" label="Short description" :value="$post->excerpt" />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Body" note="The full article patients will read." :columns="false">
        <x-form.textarea name="body" label="Article body" :value="$post->body" :rows="10" required />
    </x-admin.form-section>

    <x-admin.form-section title="Publishing">
        <x-form.input name="cover_image" label="Cover image URL" :value="$post->cover_image" />
        <x-form.input name="published_at" label="Publish date" type="datetime-local" :value="optional($post->published_at)->format('Y-m-d\TH:i')" />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility" :columns="false">
        <x-form.checkbox name="is_published" label="Published on the website" :checked="$post->is_published" />
    </x-admin.form-section>
</x-admin.form-page>
