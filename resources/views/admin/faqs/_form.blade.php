<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.faqs.index')"
    lede="These questions appear on the public homepage. Write them the way reception would answer."
>
    <x-admin.form-section title="Question" :columns="false">
        <x-form.input name="question" label="Question" :value="$faq->question" required />
        <x-form.textarea name="answer" label="Answer" :value="$faq->answer" required />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility">
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$faq->sort_order ?? 0" />
        <x-form.checkbox name="is_active" label="Published on the website" :checked="$faq->is_active" />
    </x-admin.form-section>
</x-admin.form-page>
