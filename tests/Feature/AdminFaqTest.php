<?php

namespace Tests\Feature;

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_content_permission_cannot_manage_faqs(): void
    {
        $user = $this->createStaffUser(['doctors.manage'], 'hospital_admin');

        $response = $this->actingAs($user)->get(route('admin.faqs.index'));

        $response->assertForbidden();
    }

    public function test_empty_faq_payload_is_rejected(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $response = $this->actingAs($user)
            ->from(route('admin.faqs.create'))
            ->post(route('admin.faqs.store'), []);

        $response->assertRedirect(route('admin.faqs.create'));
        $response->assertSessionHasErrors(['question', 'answer']);
        $this->assertSame(0, Faq::query()->count());
    }

    public function test_authorized_staff_can_create_an_faq(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $response = $this->actingAs($user)->post(route('admin.faqs.store'), [
            'question' => 'Do I need an appointment?',
            'answer' => 'Book clinics ahead. The emergency desk stays open.',
            'is_active' => '1',
            'sort_order' => '1',
        ]);

        $response->assertRedirect(route('admin.faqs.index'));
        $this->assertDatabaseHas('faqs', [
            'question' => 'Do I need an appointment?',
            'is_active' => 1,
        ]);
    }
}
