<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_index_lists_published_articles(): void
    {
        $post = BlogPost::factory()->create([
            'title' => 'Heart clinic hours for Eid',
            'excerpt' => 'The cardiology desk will run a short list on Sunday.',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
        BlogPost::factory()->create([
            'title' => 'Unpublished memo',
            'is_published' => false,
            'published_at' => null,
        ]);

        $response = $this->get(route('news'));

        $response->assertOk();
        $response->assertSee('Heart clinic hours for Eid', false);
        $response->assertSee(route('news.show', $post), false);
        $response->assertDontSee('Unpublished memo', false);
    }

    public function test_published_article_page_renders(): void
    {
        $post = BlogPost::factory()->create([
            'title' => 'How to prepare for an ultrasound',
            'excerpt' => 'Drink water unless the desk tells you otherwise.',
            'body' => '<p>Bring prior films if you have them.</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('news.show', $post));

        $response->assertOk();
        $response->assertSee('How to prepare for an ultrasound', false);
        $response->assertSee('Bring prior films if you have them.', false);
    }

    public function test_unpublished_article_returns_404(): void
    {
        $post = BlogPost::factory()->create([
            'title' => 'Internal draft',
            'slug' => 'internal-draft',
            'is_published' => false,
            'published_at' => null,
        ]);

        $response = $this->get(route('news.show', $post));

        $response->assertNotFound();
    }
}
