<?php

namespace App\Models;

use Database\Factories\DoctorTalkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'doctor_name',
    'youtube_url',
    'thumbnail',
    'is_active',
    'sort_order',
])]
class DoctorTalk extends Model
{
    /** @use HasFactory<DoctorTalkFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<DoctorTalk>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function youtubeId(): ?string
    {
        $url = trim((string) $this->youtube_url);

        if ($url === '') {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/', $url, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/^[A-Za-z0-9_-]{6,}$/', $url) === 1) {
            return $url;
        }

        return null;
    }

    public function embedUrl(): ?string
    {
        $id = $this->youtubeId();

        if ($id === null) {
            return null;
        }

        return 'https://www.youtube.com/embed/'.$id.'?autoplay=1&rel=0';
    }

    public function displayThumbnail(): string
    {
        $custom = trim((string) $this->thumbnail);

        if ($custom !== '') {
            return $custom;
        }

        $id = $this->youtubeId();

        if ($id !== null) {
            return 'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg';
        }

        return '';
    }
}
