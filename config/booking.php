<?php

return [
    /*
     * Version of the privacy notice the patient agreed to when booking.
     * Change this whenever the privacy policy page changes in a meaningful way,
     * so each booking records which version was shown (PDPA consent record).
     */
    'privacy_notice_version' => '2026-11',

    /*
     * Spam protection: how many booking requests one visitor (IP address) may send.
     */
    'max_per_hour' => 5,
    'max_per_day' => 10,
];
