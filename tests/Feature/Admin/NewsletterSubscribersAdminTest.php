<?php

namespace Tests\Feature\Admin;

use App\Mail\NewsletterCustom;
use App\Models\Coupon;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class NewsletterSubscribersAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    }

    private function pendingSubscriber(string $email = 'pending@example.com'): NewsletterSubscriber
    {
        return NewsletterSubscriber::create([
            'email' => $email,
            'welcome_sent_at' => null,
            'coupon_id' => null,
        ]);
    }

    public function test_admin_can_send_custom_email_to_pending_subscriber(): void
    {
        Mail::fake();

        $subscriber = $this->pendingSubscriber();

        Livewire::actingAs($this->admin())
            ->test('pages::admin.newsletter-subscribers.index')
            ->call('openComposeModal', $subscriber->id)
            ->set('composeSubject', 'Promo khusus')
            ->set('composeBody', 'Halo, ini pesan custom dari admin.')
            ->call('sendComposedEmail');

        Mail::assertSent(NewsletterCustom::class, function (NewsletterCustom $mail) {
            return $mail->hasTo('pending@example.com')
                && $mail->mailSubject === 'Promo khusus'
                && str_contains($mail->body, 'pesan custom');
        });
    }

    public function test_welcome_template_fills_coupon_placeholder_on_send(): void
    {
        Mail::fake();

        $subscriber = $this->pendingSubscriber();

        Livewire::actingAs($this->admin())
            ->test('pages::admin.newsletter-subscribers.index')
            ->call('openComposeModal', $subscriber->id)
            ->call('applyWelcomeTemplate')
            ->call('sendComposedEmail');

        $subscriber->refresh();
        $this->assertNotNull($subscriber->coupon_id);
        $this->assertNotNull($subscriber->welcome_sent_at);

        Mail::assertSent(NewsletterCustom::class, function (NewsletterCustom $mail) use ($subscriber) {
            $subscriber->load('coupon');

            return $mail->hasTo('pending@example.com')
                && str_contains($mail->body, $subscriber->coupon->code)
                && ! str_contains($mail->body, '{KODE_KUPON}');
        });

        $this->assertSame(1, Coupon::count());
    }
}
