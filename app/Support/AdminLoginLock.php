<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Locks an admin email address for a while after too many wrong passwords,
 * whatever IP addresses the attempts come from. (Filament already limits each
 * IP address to 5 tries a minute; this stops slow guessing spread over many addresses.)
 *
 * The lock is keyed on the typed address, so it behaves the same whether or not
 * an account exists — it never reveals which addresses are real.
 */
class AdminLoginLock
{
    public const MAX_FAILURES = 10;

    public const LOCK_SECONDS = 15 * 60;

    public static function recordFailure(?string $email): void
    {
        RateLimiter::hit(self::key($email), self::LOCK_SECONDS);
    }

    public static function isLocked(?string $email): bool
    {
        return RateLimiter::tooManyAttempts(self::key($email), self::MAX_FAILURES);
    }

    public static function minutesLeft(?string $email): int
    {
        return max(1, (int) ceil(RateLimiter::availableIn(self::key($email)) / 60));
    }

    public static function clear(?string $email): void
    {
        RateLimiter::clear(self::key($email));
    }

    private static function key(?string $email): string
    {
        return 'admin-login:'.hash('sha256', mb_strtolower(trim((string) $email)));
    }
}
