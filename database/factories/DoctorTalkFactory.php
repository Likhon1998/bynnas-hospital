<?php

namespace Database\Factories;

use App\Models\DoctorTalk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorTalk>
 */
class DoctorTalkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $videoId = fake()->regexify('[A-Za-z0-9_-]{11}');

        return [
            'title' => fake()->sentence(4),
            'doctor_name' => 'Dr. '.fake()->lastName(),
            'youtube_url' => 'https://www.youtube.com/watch?v='.$videoId,
            'thumbnail' => 'https://img.youtube.com/vi/'.$videoId.'/hqdefault.jpg',
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
