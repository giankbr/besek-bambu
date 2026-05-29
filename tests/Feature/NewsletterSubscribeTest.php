<?php

namespace Tests\Feature;

use App\Mail\NewsletterWelcome;
use App\Models\Coupon;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsletterSubscribeTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribe_creates_coupon_and_sends_welcome_email(): void
    {
        Mail::fake();

        $response = $this->post(route('newsletter.subscribe'), [
            'email' => 'pelanggan@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('newsletter_status');

        $subscriber = NewsletterSubscriber::where('email', 'pelanggan@example.com')->first();
        $this->assertNotNull($subscriber);
        $this->assertNotNull($subscriber->welcome_sent_at);
        $this->assertNotNull($subscriber->coupon_id);

        $coupon = Coupon::find($subscriber->coupon_id);
        $this->assertSame('percent', $coupon->type);
        $this->assertEquals(10, (float) $coupon->value);
        $this->assertSame(1, $coupon->usage_limit);

        Mail::assertSent(NewsletterWelcome::class, function (NewsletterWelcome $mail) {
            return $mail->hasTo('pelanggan@example.com');
        });
    }

    public function test_duplicate_subscribe_does_not_send_second_email(): void
    {
        Mail::fake();

        $this->post(route('newsletter.subscribe'), ['email' => 'dupe@example.com']);
        $this->post(route('newsletter.subscribe'), ['email' => 'dupe@example.com']);

        Mail::assertSent(NewsletterWelcome::class, 1);
    }
}
