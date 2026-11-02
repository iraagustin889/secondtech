<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name'     => 'Nama Baru',
            'email'    => 'baru@example.test',
            'phone'    => '081234567890',
            'address'  => 'Jl. Contoh No. 1',
            'location' => 'Banyuwangi',
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->put(route('profile.update'), $this->validData())->assertRedirect(route('login'));
    }

    public function test_user_can_open_edit_page_with_current_data(): void
    {
        $user = User::factory()->create(['is_active' => true, 'name' => 'Nama Lama']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Nama Lama')
            ->assertSee($user->email);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)
            ->put(route('profile.update'), $this->validData())
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@example.test', $user->email);
        $this->assertSame('081234567890', $user->phone);
        $this->assertSame('Jl. Contoh No. 1', $user->address);
        $this->assertSame('Banyuwangi', $user->location);
    }

    public function test_validation_rejects_bad_input(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)
            ->put(route('profile.update'), $this->validData([
                'name'  => '',
                'email' => 'bukan-email',
                'phone' => 'abc',
            ]))
            ->assertSessionHasErrors(['name', 'email', 'phone']);
    }

    public function test_email_must_be_unique_but_own_email_is_allowed(): void
    {
        $other = User::factory()->create(['is_active' => true, 'email' => 'dipakai@example.test']);
        $user = User::factory()->create(['is_active' => true, 'email' => 'saya@example.test']);

        $this->actingAs($user)
            ->put(route('profile.update'), $this->validData(['email' => 'dipakai@example.test']))
            ->assertSessionHasErrors('email');

        $this->actingAs($user)
            ->put(route('profile.update'), $this->validData(['email' => 'saya@example.test']))
            ->assertSessionHasNoErrors();
    }

    public function test_role_and_active_status_cannot_be_changed_from_profile(): void
    {
        $user = User::factory()->create(['is_active' => true, 'role' => 'buyer']);

        $this->actingAs($user)
            ->put(route('profile.update'), $this->validData(['role' => 'admin', 'is_active' => false]));

        $user->refresh();
        $this->assertSame('buyer', $user->role);
        $this->assertTrue((bool) $user->is_active);
    }
}
