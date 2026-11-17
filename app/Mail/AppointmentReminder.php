<?php

namespace App\Mail;

class AppointmentReminder extends BookingMail
{
    protected function subjectLine(): string
    {
        return 'Reminder: your appointment tomorrow';
    }

    protected function template(): string
    {
        return 'reminder';
    }
}
