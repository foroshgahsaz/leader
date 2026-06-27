<?php

namespace Tests\Feature\Auth;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
    }

    public function test_new_users_can_register_with_company(): void
    {
        Volt::test('pages.auth.register')
            ->set('first_name', 'Sarah')
            ->set('last_name', 'Kaya')
            ->set('email', 'sarah@acme-exports.test')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('company_name', 'Acme Exports Ltd')
            ->set('country_code', 'TR')
            ->set('website', 'https://acme-exports.test')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();

        $user = User::query()->where('email', 'sarah@acme-exports.test')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->current_organization_id);

        setPermissionsTeamId($user->current_organization_id);
        $this->assertTrue($user->hasRole('admin'));

        $organization = Organization::query()->find($user->current_organization_id);

        $this->assertNotNull($organization);
        $this->assertSame('Acme Exports Ltd', $organization->name);
    }
}
