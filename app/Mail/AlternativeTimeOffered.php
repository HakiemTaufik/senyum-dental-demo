<?php

namespace App\Mail;

class AlternativeTimeOffered extends BookingMail
{
    protected function subjectLine(): string
    {
        return 'Another time for your appointment';
    }

    protected function template(): string
    {
        return 'alternative';
    }
}
