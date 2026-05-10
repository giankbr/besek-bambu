<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_when_visiting_admin(): void
    {
        $this->get('/admin/products')->assertRedirect(route('login'));
    }

    public function test_customers_cannot_access_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'email_verified_at' => now()]);

        $this->actingAs($user)
            ->get('/admin/products')
            ->assertRedirect(route('account.index'));
    }

    public function test_admins_reach_admin_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);

        $this->actingAs($admin)
            ->get('/admin/products')
            ->assertOk();
    }
}
