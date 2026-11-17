<?php

namespace App\Filament\Auth;

use App\Support\AdminLoginLock;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as FilamentLogin;
use Filament\Support\Components\Attributes\Exposed;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Filament's sign-in page, plus an account lock: after too many wrong passwords for
 * one email address, that address can't sign in for 15 minutes (see AdminLoginLock).
 * Wrong passwords are counted by App\Listeners\LogAdminSignIns.
 */
class Login extends FilamentLogin
{
    #[Exposed]
    public function authenticate(): ?LoginResponse
    {
        $email = $this->data['email'] ?? null;

        if (AdminLoginLock::isLocked($email)) {
            Log::channel('security')->warning('Admin sign-in refused: account locked', [
                'ip' => request()->ip(),
            ]);

            throw ValidationException::withMessages([
                'data.email' => trans_choice(
                    'Too many failed sign-in attempts. Please try again in :minutes minute.|Too many failed sign-in attempts. Please try again in :minutes minutes.',
                    $minutes = AdminLoginLock::minutesLeft($email),
                    ['minutes' => $minutes],
                ),
            ]);
        }

        return parent::authenticate();
    }
}
