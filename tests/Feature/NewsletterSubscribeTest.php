<?php

namespace Tests\Feature;

use App\Mail\NewsletterWelcome;
use App\Models\Coupon;
use App\Models\NewsletterEmailLog;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsletterSubscribeTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribe_sends_welcome_email_without_coupon(): void
    {
        Mail::fake();

        $response = $this->post(route('newsletter.subscribe'), [
            'name' => 'Gian',
            'email' => 'pelanggan@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('newsletter_status');

        $subscriber = NewsletterSubscriber::where('email', 'pelanggan@example.com')->first();
        $this->assertNotNull($subscriber);
        $this->assertSame('Gian', $subscriber->name);
        $this->assertNotNull($subscriber->welcome_sent_at);
        $this->assertSame(1, NewsletterEmailLog::where('newsletter_subscriber_id', $subscriber->id)->count());
        $this->assertNull($subscriber->coupon_id);
        $this->assertSame(0, Coupon::count());

        Mail::assertSent(NewsletterWelcome::class, function (NewsletterWelcome $mail) {
            return $mail->hasTo('pelanggan@example.com');
        });
    }

    public function test_duplicate_subscribe_does_not_send_second_email(): void
    {
        Mail::fake();

        $this->post(route('newsletter.subscribe'), ['name' => 'Gian', 'email' => 'dupe@example.com']);
        $this->post(route('newsletter.subscribe'), ['name' => 'Gian', 'email' => 'dupe@example.com']);

        Mail::assertSent(NewsletterWelcome::class, 1);
    }
}
