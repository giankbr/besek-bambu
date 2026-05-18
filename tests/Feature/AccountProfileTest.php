<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_profile_page_uses_storefront_layout(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->get(route('account.profile'))
            ->assertOk()
            ->assertSee('Perbarui data akun', false)
            ->assertSee('account-profile-form', false)
            ->assertSee('storefront-body', false);
    }

    public function test_unverified_user_sees_email_verification_alert_on_profile(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('account.profile'))
            ->assertOk()
            ->assertSee('Belum aktif', false)
            ->assertSee('Verifikasi email Anda', false)
            ->assertSee('Kirim ulang email verifikasi', false);
    }

    public function test_verified_user_does_not_see_email_verification_alert_on_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('account.profile'))
            ->assertOk()
            ->assertDontSee('Belum aktif', false);
    }

    public function test_account_profile_can_be_updated(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHas('status');

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }
}
