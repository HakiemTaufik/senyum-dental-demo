<?php

namespace App\Mail;

class BookingReceived extends BookingMail
{
    protected function subjectLine(): string
    {
        return 'We received your appointment request ('.$this->booking->reference.')';
    }

    protected function template(): string
    {
        return 'received';
    }
}
