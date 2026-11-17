<?php

namespace App\Listeners;

use App\Models\User;
use App\Support\AdminLoginLock;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;

/**
 * Writes staff sign-ins to storage/logs/security-*.log (kept 90 days) and counts
 * wrong passwords towards the account lock.
 *
 * Never logged: passwords, or the typed email address (it may be a typo, or even a
 * password typed into the wrong box). A known account is identified by its ID.
 */
class LogAdminSignIns
{
    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        AdminLoginLock::recordFailure($email);

        Log::channel('security')->warning('Admin sign-in failed', [
            'user_id' => $event->user instanceof User ? $event->user->id : null,
            'ip' => request()->ip(),
        ]);

        if (AdminLoginLock::isLocked($email)) {
            Log::channel('security')->alert('Admin account locked after repeated failures', [
                'user_id' => $event->user instanceof User ? $event->user->id : null,
                'ip' => request()->ip(),
                'minutes' => AdminLoginLock::LOCK_SECONDS / 60,
            ]);
        }
    }

    public function handleLogin(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        AdminLoginLock::clear($event->user->email);

        Log::channel('security')->info('Admin signed in', [
            'user_id' => $event->user->id,
            'ip' => request()->ip(),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user instanceof User) {
            Log::channel('security')->info('Admin signed out', ['user_id' => $event->user->id]);
        }
    }
}
