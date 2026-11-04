<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.board-members.index')"
    lede="Leaders appear on About us and on a dedicated page for their role (Founder, Chairman, and so on). Keep titles accurate and photos clear."
    enctype="multipart/form-data"
>
    <x-admin.form-section title="Person" note="Name and role visitors will read on the website." tone="teal">
        <x-form.input name="name" label="Full name" :value="$boardMember->name" required />
        <x-form.input name="slug" label="URL slug" :value="$boardMember->slug" hint="Optional. Used in /about/people/{slug}. Leave blank to generate from the name." />
        <x-form.input name="title" label="Display title" :value="$boardMember->title" required hint="Example: Founder & Chief Patron, Chairman of the Board" />
        <x-form.select name="role" label="Leadership role" required>
            @foreach (\App\Models\BoardMember::ROLES as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $boardMember->role ?: 'director') === $value)>{{ $label }}</option>
            @endforeach
        </x-form.select>
        <x-form.input name="role_label" label="Board label" :value="$boardMember->role_label" hint="Optional short group label. Example: Board of Directors" />
        <div class="form-span-2">
            <x-form.textarea name="bio" label="Short bio" :value="$boardMember->bio" rows="4" />
        </div>
        <div class="form-span-2">
            <x-form.textarea
                name="achievements_text"
                label="Personal achievements"
                :value="old('achievements_text', $boardMember->achievementsText())"
                rows="5"
                hint="One achievement per line. Shown on the person’s public page and About us cards."
            />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Photo" note="Upload a portrait or paste an image URL.">
        <div class="form-span-2 doctor-photo-field" data-board-photo>
            <div class="doctor-photo-preview @if (! filled(old('photo', $boardMember->photo))) is-empty @endif">
                <img
                    data-board-photo-preview
                    src="{{ old('photo', $boardMember->photo) ?: '' }}"
                    alt="Board member photo"
                    @if (! filled(old('photo', $boardMember->photo))) hidden @endif
                >
                <span data-board-photo-empty class="doctor-photo-preview__empty" @if (filled(old('photo', $boardMember->photo))) hidden @endif>No photo yet</span>
            </div>
            <x-form.input
                name="photo_upload"
                type="file"
                label="Upload photo"
                accept="image/jpeg,image/png,image/webp,image/gif"
                hint="JPG, PNG, or WEBP up to 4 MB."
                data-board-photo-input
            />
            <x-form.input
                name="photo"
                label="Photo URL"
                :value="$boardMember->photo"
                hint="Optional if you upload a file."
                data-board-photo-url
            />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Contact">
        <x-form.input name="email" label="Email" type="email" :value="$boardMember->email" />
        <x-form.input name="phone" label="Phone" :value="$boardMember->phone" />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility">
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$boardMember->sort_order ?? 0" />
        <x-form.checkbox name="is_active" label="Published on the website" :checked="$boardMember->is_active" />
    </x-admin.form-section>
</x-admin.form-page>

<script>
    (() => {
        const root = document.querySelector('[data-board-photo]');
        if (!root) return;
        const preview = root.querySelector('[data-board-photo-preview]');
        const empty = root.querySelector('[data-board-photo-empty]');
        const box = root.querySelector('.doctor-photo-preview');
        const fileInput = root.querySelector('[data-board-photo-input]');
        const urlInput = root.querySelector('[data-board-photo-url]');

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
