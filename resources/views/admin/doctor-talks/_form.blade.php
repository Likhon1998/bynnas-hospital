<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.doctor-talks.index')"
    lede="Homepage Doctor's Talk shows four videos in one row. Prefer Bangla health-awareness talks (diabetes, heart, fever, blood pressure) so patients understand the tip clearly."
    enctype="multipart/form-data"
>
    <x-admin.form-section title="Talk" note="Use a Bangla title about a health issue. Caption on hover shows title and doctor name." tone="teal">
        <x-form.input name="title" label="Title" :value="$talk->title" required hint="Example: ডায়াবেটিসের লক্ষণ ও করণীয়" />
        <x-form.input name="doctor_name" label="Doctor name" :value="$talk->doctor_name" hint="Optional. Example: ডা. হাসান" />
        <div class="form-span-2">
            <x-form.input
                name="youtube_url"
                label="YouTube link"
                :value="$talk->youtube_url"
                required
                hint="Paste a Bangla health talk link. Example: https://www.youtube.com/watch?v=VIDEO_ID"
            />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Thumbnail" note="Recommended size: 640 × 360 px (16:9). JPG, PNG, or WEBP up to 4 MB. If empty, the YouTube thumbnail is used.">
        <div class="form-span-2 doctor-photo-field" data-doctor-talk-image>
            <div
                class="doctor-photo-preview @if (! filled(old('thumbnail', $talk->thumbnail ?: $talk->displayThumbnail()))) is-empty @endif"
                style="width: 100%; max-width: 22.5rem; height: 12.5rem; border-radius: 1rem;"
            >
                <img
                    data-doctor-talk-preview
                    src="{{ old('thumbnail', $talk->thumbnail ?: $talk->displayThumbnail()) ?: '' }}"
                    alt="Doctor talk thumbnail preview"
                    @if (! filled(old('thumbnail', $talk->thumbnail ?: $talk->displayThumbnail()))) hidden @endif
                >
                <span data-doctor-talk-empty class="doctor-photo-preview__empty" @if (filled(old('thumbnail', $talk->thumbnail ?: $talk->displayThumbnail()))) hidden @endif>No thumbnail yet</span>
            </div>
            <x-form.input
                name="thumbnail_upload"
                type="file"
                label="Upload thumbnail"
                accept="image/jpeg,image/png,image/webp,image/gif"
                hint="Use 640 × 360 px for a clean one-row layout."
                data-doctor-talk-input
            />
            <x-form.input
                name="thumbnail"
                label="Thumbnail URL"
                :value="$talk->thumbnail"
                hint="Optional if you upload a file. Leave blank to use the YouTube thumbnail."
                data-doctor-talk-url
            />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Visibility">
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$talk->sort_order ?? 0" />
        <x-form.checkbox name="is_active" label="Show on homepage" :checked="$talk->is_active" />
    </x-admin.form-section>
</x-admin.form-page>

<script>
    (() => {
        const root = document.querySelector('[data-doctor-talk-image]');
        if (! root) {
            return;
        }

        const preview = root.querySelector('[data-doctor-talk-preview]');
        const empty = root.querySelector('[data-doctor-talk-empty]');
        const box = root.querySelector('.doctor-photo-preview');
        const fileInput = root.querySelector('[data-doctor-talk-input]');
        const urlInput = root.querySelector('[data-doctor-talk-url]');

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
