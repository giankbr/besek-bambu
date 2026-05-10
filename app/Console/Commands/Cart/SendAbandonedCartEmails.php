<?php

namespace App\Console\Commands\Cart;

use App\Mail\AbandonedCart;
use App\Models\CartSnapshot;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartEmails extends Command
{
    protected $signature = 'cart:send-abandoned-emails {--hours=24 : Min hours since last activity} {--max-hours=168 : Skip carts older than this}';

    protected $description = 'Email reminders to authenticated users who left items in their cart.';

    public function handle(): int
    {
        $minHours = (int) $this->option('hours');
        $maxHours = (int) $this->option('max-hours');

        $window = CartSnapshot::query()
            ->whereNull('recovery_sent_at')
            ->where('last_seen_at', '<=', now()->subHours($minHours))
            ->where('last_seen_at', '>=', now()->subHours($maxHours))
            ->with('user')
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($window as $snapshot) {
            if (! $snapshot->user || ! $snapshot->user->email) {
                $skipped++;

                continue;
            }

            // Skip if the user has placed any order since the cart
            // was last touched. They likely already converted.
            $hasRecentOrder = Order::query()
                ->where('user_id', $snapshot->user_id)
                ->where('created_at', '>=', $snapshot->last_seen_at)
                ->exists();

            if ($hasRecentOrder) {
                $snapshot->update(['recovery_sent_at' => now()]);
                $skipped++;

                continue;
            }

            try {
                Mail::to($snapshot->user->email)->send(new AbandonedCart($snapshot));
                $snapshot->update(['recovery_sent_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('Abandoned cart email failed', [
                    'user_id' => $snapshot->user_id,
                    'error' => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        $this->info("Abandoned cart emails: {$sent} sent, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
