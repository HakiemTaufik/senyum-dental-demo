<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Creates the first Owner account on a fresh site (e.g. the live server).
 * After that, the owner adds other staff from the admin panel.
 */
class CreateOwner extends Command
{
    protected $signature = 'app:create-owner';

    protected $description = 'Create an Owner (admin) account for the admin panel';

    public function handle(): int
    {
        $name = text('Name', required: true);
        $email = text('Email', required: true, validate: fn (string $value) => Validator::make(
            ['email' => $value],
            ['email' => ['email', 'unique:users,email']],
        )->errors()->first('email') ?: null);
        $password = password('Password (min. 12 characters, upper & lower case, a number)', required: true, validate: fn (string $value) => Validator::make(
            ['password' => $value],
            ['password' => [Password::min(12)->mixedCase()->numbers()]],
        )->errors()->first('password') ?: null);

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => Role::Owner,
            'is_active' => true,
        ]);

        $this->info("Owner account created for {$email}. Sign in at ".url('/admin').' and set up two-factor login.');

        return self::SUCCESS;
    }
}
