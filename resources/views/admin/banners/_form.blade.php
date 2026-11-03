<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.banners.index')"
    lede="Homepage hero, emergency top bar, and footer promo banners are all managed here. Upload an image or paste a URL."
    enctype="multipart/form-data"
>
    <x-admin.form-section title="Banner" note="What visitors see first." tone="teal">
        <x-form.input name="title" label="Title" :value="$banner->title" required />
        <x-form.input name="subtitle" label="Subtitle" :value="$banner->subtitle" />
        <div class="form-span-2 doctor-photo-field" data-banner-image>
            <div class="doctor-photo-preview @if (! filled(old('image', $banner->image))) is-empty @endif" style="width: 100%; max-width: 22rem; height: 10rem; border-radius: 1rem;">
                <img
                    data-banner-image-preview
                    src="{{ old('image', $banner->image) ?: '' }}"
                    alt="Banner preview"
                    @if (! filled(old('image', $banner->image))) hidden @endif
                >
                <span data-banner-image-empty class="doctor-photo-preview__empty" @if (filled(old('image', $banner->image))) hidden @endif>No image yet</span>
            </div>
            <x-form.input
                name="image_upload"
                type="file"
                label="Upload banner image"
                accept="image/jpeg,image/png,image/webp,image/gif"
                hint="Recommended wide image for hero banners. JPG, PNG, or WEBP up to 4 MB."
                data-banner-image-input
            />
            <x-form.input
                name="image"
                label="Image URL"
                :value="$banner->image"
                hint="Optional if you upload a file."
                data-banner-image-url
            />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Button">
        <x-form.input name="cta_label" label="Button label" :value="$banner->cta_label" />
        <x-form.input name="cta_url" label="Button URL" :value="$banner->cta_url" hint="Example: /doctors or /emergency" />
    </x-admin.form-section>

    <x-admin.form-section title="Schedule" note="Choose where this banner appears, then set the dates it should run.">
        <x-form.select name="placement" label="Placement" required hint="Hero is the homepage photo. Emergency sits in the top bar. Promo is the dark band above the footer.">
            @foreach ($placements as $value => $label)
                <option value="{{ $value }}" @selected(old('placement', $banner->placement) === $value)>{{ $label }}</option>
            @endforeach
        </x-form.select>
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$banner->sort_order ?? 0" />
        <x-form.input name="starts_at" label="Starts at" type="datetime-local" :value="optional($banner->starts_at)->format('Y-m-d\TH:i')" />
        <x-form.input name="ends_at" label="Ends at" type="datetime-local" :value="optional($banner->ends_at)->format('Y-m-d\TH:i')" />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility" :columns="false">
        <x-form.checkbox name="is_active" label="Active banner" :checked="$banner->is_active" />
    </x-admin.form-section>
</x-admin.form-page>

<script>
    (() => {
        const root = document.querySelector('[data-banner-image]');
        if (!root) return;
        const preview = root.querySelector('[data-banner-image-preview]');
        const empty = root.querySelector('[data-banner-image-empty]');
        const box = root.querySelector('.doctor-photo-preview');
        const fileInput = root.querySelector('[data-banner-image-input]');
        const urlInput = root.querySelector('[data-banner-image-url]');

        const show = (src) => {
            if (!src) {
                preview.hidden = true;
                preview.removeAttribute('src');
                empty.hidden = false;
                box.classList.add('is-empty');
                return;
            }
            preview.hidden = false;
            preview.src = src;
            empty.hidden = true;
            box.classList.remove('is-empty');
        };

        preview?.addEventListener('error', () => show(''));
        fileInput?.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => show(String(reader.result || ''));
            reader.readAsDataURL(file);
        });
        urlInput?.addEventListener('input', () => {
            if (fileInput?.files?.length) return;
            show(urlInput.value.trim());
        });
    })();
</script>
