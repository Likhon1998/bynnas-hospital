<?php

namespace Tests\Feature;

use App\Models\Acknowledgement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcknowledgementTickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_ticker_shows_active_bangla_acknowledgements_not_notices(): void
    {
        Acknowledgement::factory()->create([
            'message' => 'আমাদের হাসপাতালে আপনাকে স্বাগতম।',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Acknowledgement::factory()->inactive()->create([
            'message' => 'এই বার্তা দেখা যাবে না।',
        ]);

        $home = $this->get(route('home'));

        $home->assertOk();
        $home->assertSee('ch-ticker', false);
        $home->assertSee('স্বীকৃতি', false);
        $home->assertSee('আমাদের হাসপাতালে আপনাকে স্বাগতম।', false);
        $home->assertDontSee('এই বার্তা দেখা যাবে না।', false);
        $home->assertDontSee('Important notices', false);
    }

    public function test_staff_can_publish_an_acknowledgement_to_the_ticker(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $this->actingAs($user)
            ->post(route('admin.acknowledgements.store'), [
                'message' => 'রোগীদের সহযোগিতার জন্য ধন্যবাদ।',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.acknowledgements.index'));

        $this->assertDatabaseHas('acknowledgements', [
            'message' => 'রোগীদের সহযোগিতার জন্য ধন্যবাদ।',
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('রোগীদের সহযোগিতার জন্য ধন্যবাদ।', false)
            ->assertSee('স্বীকৃতি', false);
    }

    public function test_header_hides_ticker_when_no_acknowledgements_are_active(): void
    {
        Acknowledgement::factory()->inactive()->create([
            'message' => 'লুকানো বার্তা',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('ch-ticker', false)
            ->assertDontSee('স্বীকৃতি', false)
            ->assertDontSee('লুকানো বার্তা', false);
    }
}
