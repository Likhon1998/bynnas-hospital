<?php

namespace Database\Factories;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'blog_category_id' => BlogCategory::query()->value('id') ?? BlogCategory::query()->create([
                'name' => 'General Health',
                'slug' => 'general-health-demo-'.fake()->unique()->numerify('###'),
                'is_active' => true,
                'sort_order' => 99,
            ])->id,
            'user_id' => User::factory(),
            'title' => rtrim($title, '.').' (Demo)',
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'excerpt' => 'Demo article for website development. This is general information, not medical advice.',
            'body' => '<p>This fictional demo article is for local website development only.</p><p>It is not clinical guidance. Please consult a qualified healthcare professional for personal medical advice.</p>',
            'cover_image' => 'https://placehold.co/1200x700/0F766E/FFFFFF?text=Demo+Article',
            'is_published' => true,
            'published_at' => now()->subDays(fake()->numberBetween(1, 20)),
        ];
    }
}
