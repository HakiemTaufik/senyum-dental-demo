<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_login_page_loads(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Senyum Dental Admin');
    }

    public function test_staff_without_two_factor_must_set_it_up_first(): void
    {
        $user = User::factory()->owner()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect(route('filament.admin.auth.multi-factor-authentication.set-up-required'));
    }

    public function test_inactive_staff_cannot_open_the_admin_panel(): void
    {
        $user = User::factory()->owner()->withTwoFactor()->inactive()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_owner_with_two_factor_can_open_the_dashboard_and_staff_screen(): void
    {
        $owner = User::factory()->owner()->withTwoFactor()->create();

        $this->actingAs($owner)->get('/admin')->assertOk();
        $this->actingAs($owner)->get('/admin/users')->assertOk();
    }

    public static function nonOwnerRoles(): array
    {
        return [
            'front desk' => [Role::FrontDesk],
            'content editor' => [Role::ContentEditor],
            'dentist' => [Role::Dentist],
        ];
    }

    #[DataProvider('nonOwnerRoles')]
    public function test_only_the_owner_can_manage_staff_accounts(Role $role): void
    {
        $user = User::factory()->role($role)->withTwoFactor()->create();

        $this->actingAs($user)->get('/admin')->assertOk();
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/users/create')->assertForbidden();
    }

    public function test_owner_can_add_a_staff_member(): void
    {
        $owner = User::factory()->owner()->withTwoFactor()->create();
        $this->actingAs($owner);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Aisyah',
                'email' => 'aisyah@example.com',
                'password' => 'Str0ngPassword1',
                'role' => Role::FrontDesk->value,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['email' => 'aisyah@example.com', 'role' => 'front_desk']);
    }

    public function test_weak_passwords_are_rejected(): void
    {
        $owner = User::factory()->owner()->withTwoFactor()->create();
        $this->actingAs($owner);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Weak',
                'email' => 'weak@example.com',
                'password' => 'password',
                'role' => Role::FrontDesk->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['password']);
    }

    public function test_owner_cannot_delete_their_own_account(): void
    {
        $owner = User::factory()->owner()->create();
        $other = User::factory()->create();

        $this->assertFalse($owner->can('delete', $owner));
        $this->assertTrue($owner->can('delete', $other));
    }

    public function test_staff_list_shows_accounts(): void
    {
        $owner = User::factory()->owner()->withTwoFactor()->create();
        $staff = User::factory()->count(2)->create();
        $this->actingAs($owner);

        Livewire::test(ListUsers::class)->assertCanSeeTableRecords($staff);
    }

    public function test_sign_in_time_is_recorded(): void
    {
        $user = User::factory()->create();

        event(new Login('web', $user, false));

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_create_owner_command_creates_an_owner(): void
    {
        $this->artisan('app:create-owner')
            ->expectsQuestion('Name', 'Clinic Owner')
            ->expectsQuestion('Email', 'owner@example.com')
            ->expectsQuestion('Password (min. 12 characters, upper & lower case, a number)', 'Str0ngPassword1')
            ->assertSuccessful();

        $this->assertSame(Role::Owner, User::firstWhere('email', 'owner@example.com')->role);
    }

    public function test_two_factor_secret_is_stored_encrypted(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $raw = \DB::table('users')->where('id', $user->id)->value('app_authentication_secret');

        $this->assertNotSame('JBSWY3DPEHPK3PXP', $raw);
        $this->assertSame('JBSWY3DPEHPK3PXP', $user->fresh()->getAppAuthenticationSecret());
    }
}
