<?php

namespace App\Enums;

enum NewsletterWelcomeStatus
{
    case Sent;
    case AlreadySent;
    case Failed;
}
