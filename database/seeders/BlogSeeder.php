<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Demo health articles. Safe to re-run by slug.
     */
    public function run(): void
    {
        $author = User::query()->where('email', 'content@demo.hospital')->firstOrFail();

        $categories = [
            'Heart Health',
            'Diabetes',
            'Child Health',
            "Women's Health",
            'Nutrition',
            'General Health',
        ];

        foreach ($categories as $index => $name) {
            BlogCategory::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        $posts = [
            [
                'category' => 'heart-health',
                'title' => 'Understanding High Blood Pressure',
                'excerpt' => 'A plain-language overview of blood pressure numbers for the demo blog.',
                'body' => 'High blood pressure is often silent. This demo article explains why routine checks matter and why personal targets should come from a licensed clinician, not a website.',
            ],
            [
                'category' => 'heart-health',
                'title' => 'Heart Healthy Lifestyle',
                'excerpt' => 'General lifestyle ideas for heart health education pages.',
                'body' => 'Walking, sleep, and reducing tobacco exposure are commonly discussed heart-health habits. This fictional article is for layout testing. Individual plans belong with a qualified doctor.',
            ],
            [
                'category' => 'child-health',
                'title' => 'Healthy Nutrition for Children',
                'excerpt' => 'Demo guidance on balanced plates for school-age children.',
                'body' => 'Children need regular meals with vegetables, protein, and water. Growth concerns should be reviewed in a real pediatric clinic, not decided from demo website copy.',
            ],
            [
                'category' => 'diabetes',
                'title' => 'Diabetes Prevention Tips',
                'excerpt' => 'General prevention talking points used on the demo diabetes category.',
                'body' => 'Staying active and keeping a healthy weight may lower diabetes risk for some people. Screening intervals are personal. Use this page only as website placeholder text.',
            ],
            [
                'category' => 'general-health',
                'title' => 'Importance of Regular Health Checkups',
                'excerpt' => 'Why the demo hospital promotes checkup packages on the homepage.',
                'body' => 'Periodic checkups can catch issues early. Package contents on this demo site are fictional. Ask a licensed provider which tests are appropriate for you.',
            ],
            [
                'category' => 'womens-health',
                'title' => 'Antenatal Visit Basics',
                'excerpt' => 'A short demo article linked from Gynecology pages.',
                'body' => 'Antenatal visits track mother and baby wellbeing. Timing and tests vary. This article is not an antenatal protocol and must not replace obstetric care.',
            ],
            [
                'category' => 'nutrition',
                'title' => 'Simple Meal Ideas for Busy Families',
                'excerpt' => 'Nutrition category filler with a strong demo disclaimer.',
                'body' => 'Home-cooked meals with less sugary drinks can support general wellness. Nutrition needs differ in pregnancy, diabetes, and childhood. Speak with a professional for tailored advice.',
            ],
            [
                'category' => 'diabetes',
                'title' => 'Living With Diabetes: Daily Routines',
                'excerpt' => 'Demo education piece about daily glucose habits.',
                'body' => 'People living with diabetes often combine medication, meals, and activity. Never change prescribed treatment because of a hospital demo blog post.',
            ],
            [
                'category' => 'child-health',
                'title' => 'When a Child Has Fever: What Parents Often Ask',
                'excerpt' => 'General fever questions for the demo pediatrics blog.',
                'body' => 'Fever is common. Warning signs and medicines depend on age and history. If a child is unwell, contact a real emergency or pediatric service rather than relying on this demo text.',
            ],
            [
                'category' => 'heart-health',
                'title' => 'Recognizing Chest Discomfort: A Public Awareness Note',
                'excerpt' => 'Emergency-awareness copy for the demo cardiology blog.',
                'body' => 'Sudden chest pain, shortness of breath, or weakness on one side needs urgent in-person care. This demo article cannot triage symptoms. Call local emergency services.',
            ],
        ];

        foreach ($posts as $index => $post) {
            $category = BlogCategory::query()->where('slug', $post['category'])->firstOrFail();

            BlogPost::query()->updateOrCreate(
                ['slug' => Str::slug($post['title'])],
                [
                    'blog_category_id' => $category->id,
                    'user_id' => $author->id,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => DemoContent::articleBody($post['body']),
                    'cover_image' => DemoContent::image($post['title'], 1200, 700),
                    'is_published' => true,
                    'published_at' => now()->subDays(10 - $index)->setTime(10, 0),
                ],
            );
        }
    }
}
