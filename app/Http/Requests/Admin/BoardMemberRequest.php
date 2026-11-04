<?php

namespace App\Http\Requests\Admin;

use App\Models\BoardMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BoardMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('content.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $boardMember = $this->route('board_member');
        $ignoreId = $boardMember instanceof BoardMember ? $boardMember->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('board_members', 'slug')->ignore($ignoreId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in(array_keys(BoardMember::ROLES))],
            'role_label' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'achievements_text' => ['nullable', 'string', 'max:10000'],
            'photo' => ['nullable', 'string', 'max:2048'],
            'photo_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->integer('sort_order'),
            'slug' => filled($this->input('slug')) ? trim((string) $this->input('slug')) : null,
            'role' => filled($this->input('role')) ? trim((string) $this->input('role')) : 'director',
            'role_label' => filled($this->input('role_label')) ? trim((string) $this->input('role_label')) : null,
            'bio' => filled($this->input('bio')) ? trim((string) $this->input('bio')) : null,
            'achievements_text' => filled($this->input('achievements_text')) ? (string) $this->input('achievements_text') : null,
            'email' => filled($this->input('email')) ? trim((string) $this->input('email')) : null,
            'phone' => filled($this->input('phone')) ? trim((string) $this->input('phone')) : null,
            'photo' => filled($this->input('photo')) ? trim((string) $this->input('photo')) : null,
        ]);
    }

    /**
     * @return list<string>
     */
    public function achievementLines(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->input('achievements_text', '')) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }
}
