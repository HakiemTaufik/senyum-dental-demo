<?php

namespace App\Mail;

class BookingCancelled extends BookingMail
{
    protected function subjectLine(): string
    {
        return 'Your appointment has been cancelled';
    }

    protected function template(): string
    {
        return 'cancelled';
    }
}
