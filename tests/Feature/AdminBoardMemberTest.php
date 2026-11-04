<?php

namespace Tests\Feature;

use App\Models\BoardMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBoardMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_content_permission_cannot_manage_board_members(): void
    {
        $user = $this->createStaffUser(['doctors.manage'], 'hospital_admin');

        $this->actingAs($user)
            ->get(route('admin.board-members.index'))
            ->assertForbidden();
    }

    public function test_empty_board_member_payload_is_rejected(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $this->actingAs($user)
            ->from(route('admin.board-members.create'))
            ->post(route('admin.board-members.store'), [])
            ->assertRedirect(route('admin.board-members.create'))
            ->assertSessionHasErrors(['name', 'title']);

        $this->assertSame(0, BoardMember::query()->count());
    }

    public function test_authorized_staff_can_create_a_board_member_with_photo_upload(): void
    {
        Storage::fake('public');

        $user = $this->createStaffUser(['content.manage']);
        $upload = UploadedFile::fake()->image('chairman.jpg', 480, 640);

        $this->actingAs($user)
            ->post(route('admin.board-members.store'), [
                'name' => 'Prof. Dr. Nazmul Haque',
                'title' => 'Chairman',
                'role' => 'chairman',
                'role_label' => 'Board of Directors',
                'bio' => 'Leads hospital governance.',
                'achievements_text' => "Expanded specialty clinics\nStrengthened governance",
                'photo_upload' => $upload,
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.board-members.index'));

        $member = BoardMember::query()->firstOrFail();

        $this->assertSame('Prof. Dr. Nazmul Haque', $member->name);
        $this->assertSame('Chairman', $member->title);
        $this->assertSame('chairman', $member->role);
        $this->assertSame('prof-dr-nazmul-haque', $member->slug);
        $this->assertSame(['Expanded specialty clinics', 'Strengthened governance'], $member->achievementList());
        $this->assertTrue($member->is_active);
        $this->assertStringStartsWith('/storage/board/', (string) $member->photo);
        Storage::disk('public')->assertExists(ltrim(str_replace('/storage/', '', (string) $member->photo), '/'));

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('People who guide the campus', false)
            ->assertSee('Prof. Dr. Nazmul Haque', false)
            ->assertSee('Chairman', false)
            ->assertSee('Leads hospital governance.', false)
            ->assertSee($member->photo, false);

        $this->get(route('about.leadership.role', 'chairman'))
            ->assertOk()
            ->assertSee('Prof. Dr. Nazmul Haque', false);

        $this->get(route('about.leadership.role', 'chairman'))
            ->assertOk()
            ->assertSee('Expanded specialty clinics', false);
    }

    public function test_inactive_board_members_stay_off_the_about_page(): void
    {
        BoardMember::factory()->create([
            'name' => 'Hidden Director',
            'title' => 'Director',
            'is_active' => false,
        ]);
        BoardMember::factory()->create([
            'name' => 'Visible Chairman',
            'title' => 'Chairman',
            'is_active' => true,
        ]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Visible Chairman', false)
            ->assertDontSee('Hidden Director', false);
    }
}
