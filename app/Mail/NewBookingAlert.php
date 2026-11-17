<?php

namespace App\Mail;

class NewBookingAlert extends BookingMail
{
    protected function subjectLine(): string
    {
        return 'New appointment request '.$this->booking->reference;
    }

    protected function template(): string
    {
        return 'new-alert';
    }
}
