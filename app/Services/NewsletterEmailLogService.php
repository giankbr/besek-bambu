<?php

namespace App\Services;

use App\Models\NewsletterEmailLog;
use App\Models\NewsletterSubscriber;

class NewsletterEmailLogService
{
    public function record(NewsletterSubscriber $subscriber, string $subject, string $body): NewsletterEmailLog
    {
        return NewsletterEmailLog::create([
            'newsletter_subscriber_id' => $subscriber->id,
            'subject' => $subject,
            'body' => $body,
            'sent_at' => now(),
        ]);
    }
}
