<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
    }

    /** @test */
    public function profile_page_is_displayed()
    {
        $response = $this->actingAs($this->user)->get(route('profile.show'));
        $response->assertStatus(200);
        $response->assertViewIs('profile.show');
        $response->assertViewHas('user', $this->user);
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
    }

    /** @test */
    public function profile_edit_page_is_displayed()
    {
        $response = $this->actingAs($this->user)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
        $response->assertViewHas('user', $this->user);
    }

    /** @test */
    public function profile_information_can_be_updated()
    {
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => 'Test User Updated',
                'email' => 'test.updated@example.com',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'profile-updated');

        $this->user->refresh(); // Recargar datos del usuario desde la BD

        $this->assertSame('Test User Updated', $this->user->name);
        $this->assertSame('test.updated@example.com', $this->user->email);
        $this->assertNull($this->user->email_verified_at); // Email cambió, debe desverificarse
    }

    /** @test */
    public function email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => 'Test User Updated',
                'email' => $this->user->email, // Mismo email
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));

        $this->assertNotNull($this->user->refresh()->email_verified_at); // Debe seguir verificado
    }

    /** @test */
    public function user_can_delete_their_account()
    {
        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'), [
                'password' => 'old-password', // Contraseña correcta
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/'); // Redirige a la raíz
        $this->assertGuest(); // Verifica que se deslogueó
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]); // Verifica que se borró
    }

    /** @test */
    public function correct_password_must_be_provided_to_delete_account()
    {
        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'), [
                'password' => 'wrong-password', // Contraseña incorrecta
            ]);

        // Verifica que el error está en el bag 'userDeletion' y es para 'password'
        $response->assertSessionHasErrorsIn('userDeletion', 'password');
        $response->assertRedirect(); // Debería redirigir de vuelta
        $this->assertNotNull($this->user->fresh()); // Verifica que el usuario NO se borró
    }
}
