<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_redirects_to_admin_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_login_page_renders_for_guests(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSee('Sign in');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => 'nobody@example.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_non_staff_account_cannot_access_the_admin_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'patient@example.test',
            'password' => 'password',
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_staff_user_can_sign_in_and_reach_the_dashboard(): void
    {
        $user = $this->createStaffUser([], 'super_admin');

        $response = $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_staff_user_can_sign_out(): void
    {
        $user = $this->createStaffUser([], 'super_admin');

        $response = $this->actingAs($user)->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
