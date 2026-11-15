<?php

namespace Tests\Feature;

use App\Models\DoctorTalk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DoctorTalkTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_doctor_talks_after_departments_in_one_row(): void
    {
        DoctorTalk::factory()->create([
            'title' => 'হৃদরোগ সচেতনতায় করণীয়',
            'doctor_name' => 'ডা. করিম',
            'youtube_url' => 'https://www.youtube.com/watch?v=VRyVWW_w-5A',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        DoctorTalk::factory()->inactive()->create([
            'title' => 'Hidden talk',
            'youtube_url' => 'https://www.youtube.com/watch?v=oqVSypbTn6I',
        ]);

        $home = $this->get(route('home'));

        $home->assertOk();
        $home->assertSee('id="doctor-talks"', false);
        $home->assertSee("Doctor's Talk", false);
        $home->assertSee('হৃদরোগ সচেতনতায় করণীয়', false);
        $home->assertSee('ডা. করিম', false);
        $home->assertSee('ch-talks-sec__title', false);
        $home->assertSee('data-youtube-open', false);
        $home->assertSee('data-talks-prev', false);
        $home->assertSee('youtube.com/embed/VRyVWW_w-5A', false);
        $home->assertSee('data-talk-slots="4"', false);
        $home->assertDontSee('Hidden talk', false);

        $content = $home->getContent();
        $departmentsPos = strpos($content, 'id="departments"');
        $talksPos = strpos($content, 'id="doctor-talks"');
        $locationsPos = strpos($content, 'id="locations"');

        $this->assertNotFalse($departmentsPos);
        $this->assertNotFalse($talksPos);
        $this->assertNotFalse($locationsPos);
        $this->assertLessThan($talksPos, $departmentsPos);
        $this->assertLessThan($locationsPos, $talksPos);
    }

    public function test_home_shows_at_most_eight_doctor_talks_four_per_row(): void
    {
        foreach (range(1, 9) as $number) {
            DoctorTalk::factory()->create([
                'title' => "Talk number {$number}",
                'youtube_url' => 'https://www.youtube.com/watch?v=abcdefghij'.$number,
                'is_active' => true,
                'sort_order' => $number,
            ]);
        }

        $home = $this->get(route('home'));

        $home->assertOk();
        $home->assertSee('data-talk-slots="4"', false);
        $home->assertSee('Talk number 1', false);
        $home->assertSee('Talk number 8', false);
        $home->assertDontSee('Talk number 9', false);
    }

    public function test_staff_can_publish_a_doctor_talk_from_the_desk(): void
    {
        Storage::fake('public');

        $user = $this->createStaffUser(['content.manage']);
        $upload = UploadedFile::fake()->image('talk.jpg', 640, 360);

        $this->actingAs($user)
            ->post(route('admin.doctor-talks.store'), [
                'title' => 'Preparing for surgery',
                'doctor_name' => 'Dr. Hasan',
                'youtube_url' => 'https://youtu.be/YQHsXMglC9A',
                'is_active' => '1',
                'sort_order' => '1',
                'thumbnail_upload' => $upload,
            ])
            ->assertRedirect(route('admin.doctor-talks.index'));

        $talk = DoctorTalk::query()->firstOrFail();

        $this->assertSame('Preparing for surgery', $talk->title);
        $this->assertSame('YQHsXMglC9A', $talk->youtubeId());
        $this->assertStringStartsWith('/storage/doctor-talks/', $talk->thumbnail);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Preparing for surgery', false)
            ->assertSee('youtube.com/embed/YQHsXMglC9A', false)
            ->assertSee($talk->thumbnail, false);
    }

    public function test_home_hides_doctor_talks_when_none_are_active(): void
    {
        DoctorTalk::factory()->inactive()->create([
            'title' => 'Draft talk',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="doctor-talks"', false)
            ->assertDontSee('Draft talk', false);
    }

    public function test_authorized_staff_can_open_doctor_talk_desk(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $this->actingAs($user)
            ->get(route('admin.doctor-talks.index'))
            ->assertOk()
            ->assertSee("Doctor's Talk", false);

        $this->actingAs($user)
            ->get(route('admin.doctor-talks.create'))
            ->assertOk()
            ->assertSee('640 × 360', false)
            ->assertSee('YouTube link', false);
    }
}
