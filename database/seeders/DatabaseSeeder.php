<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Starter content everywhere; demo staff logins on your own PC only.
     * On the live site the first owner is created with `php artisan app:create-owner`.
     */
    public function run(): void
    {
        // Starter website content: safe everywhere, never overwrites edits.
        $this->call(InitialContentSeeder::class);

        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('Demo staff accounts are only created on your own PC.');

            return;
        }

        $staff = [
            ['Demo Owner', 'owner@senyum.test', Role::Owner],
            ['Demo Front Desk', 'frontdesk@senyum.test', Role::FrontDesk],
            ['Demo Content Editor', 'editor@senyum.test', Role::ContentEditor],
            ['Dr. Demo Dentist', 'dentist@senyum.test', Role::Dentist],
        ];

        foreach ($staff as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password', 'role' => $role, 'is_active' => true, 'email_verified_at' => now()],
            );
        }

        $this->call(DemoBookingsSeeder::class);
    }
}
