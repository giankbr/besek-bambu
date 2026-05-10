<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_users_are_redirected_to_account(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'email_verified_at' => now()]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('account.index'));
    }

    public function test_admin_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }
}
