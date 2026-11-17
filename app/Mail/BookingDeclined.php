<?php

namespace App\Mail;

class BookingDeclined extends BookingMail
{
    protected function subjectLine(): string
    {
        return 'About your appointment request';
    }

    protected function template(): string
    {
        return 'declined';
    }
}
