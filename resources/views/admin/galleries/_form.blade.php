<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.galleries.index')"
    lede="Homepage gallery slides share one fixed frame. Prepare every photo at the size below so crops stay consistent."
    enctype="multipart/form-data"
>
    <x-admin.form-section title="Image" note="Recommended size: 1200 × 750 px (landscape 16:10). JPG, PNG, or WEBP up to 4 MB." tone="teal">
        <x-form.input name="title" label="Title" :value="$item->title" required />
        <x-form.input name="album" label="Album" :value="$item->album" />
        <div class="form-span-2 doctor-photo-field" data-gallery-image>
            <div
                class="doctor-photo-preview @if (! filled(old('image', $item->image))) is-empty @endif"
                style="width: 100%; max-width: 22.5rem; height: 14rem; border-radius: 1rem;"
            >
                <img
                    data-gallery-image-preview
                    src="{{ old('image', $item->image) ?: '' }}"
                    alt="Gallery preview"
                    @if (! filled(old('image', $item->image))) hidden @endif
                >
                <span data-gallery-image-empty class="doctor-photo-preview__empty" @if (filled(old('image', $item->image))) hidden @endif>No image yet</span>
            </div>
            <x-form.input
                name="image_upload"
                type="file"
                label="Upload gallery image"
                accept="image/jpeg,image/png,image/webp,image/gif"
                hint="Use 1200 × 750 px (or the same 16:10 ratio). Wider or taller photos are cropped to fit the homepage frame."
                data-gallery-image-input
            />
            <x-form.input
                name="image"
                label="Image URL"
                :value="$item->image"
                hint="Optional if you upload a file."
                data-gallery-image-url
            />
        </div>
        <div class="form-span-2">
            <x-form.textarea name="caption" label="Caption" :value="$item->caption" />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Visibility">
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$item->sort_order ?? 0" />
        <x-form.checkbox name="is_active" label="Visible in the gallery" :checked="$item->is_active" />
    </x-admin.form-section>
</x-admin.form-page>

<script>
    (() => {
        const root = document.querySelector('[data-gallery-image]');
        if (! root) {
            return;
        }

        const preview = root.querySelector('[data-gallery-image-preview]');
        const empty = root.querySelector('[data-gallery-image-empty]');
        const box = root.querySelector('.doctor-photo-preview');
        const fileInput = root.querySelector('[data-gallery-image-input]');
        const urlInput = root.querySelector('[data-gallery-image-url]');

        const show = (src) => {
            if (! src) {
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

            if (! file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = () => show(String(reader.result || ''));
            reader.readAsDataURL(file);
        });
        urlInput?.addEventListener('input', () => {
            if (fileInput?.files?.length) {
                return;
            }

            show(urlInput.value.trim());
        });
    })();
</script>
