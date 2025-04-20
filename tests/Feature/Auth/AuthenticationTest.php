<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    #[Test]
    public function users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'), // Usar una contraseña conocida
            'rol' => 'cliente', // Asignar un rol para la redirección
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123', // Usar la contraseña correcta
        ]);

        // CORREGIDO: Esperar la redirección inicial a /dashboard
        // $response->assertRedirect(route('profile.entry')); // ANTES
        $response->assertRedirect('/dashboard'); // DESPUÉS (O RouteServiceProvider::HOME)
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    #[Test]
    public function registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    #[Test]
    public function new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        // CORREGIDO: Esperar la redirección inicial a /dashboard
        // $response->assertRedirect(route('profile.entry')); // ANTES
        $response->assertRedirect('/dashboard'); // DESPUÉS (O RouteServiceProvider::HOME)
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'rol' => 'cliente']);
    }

     #[Test]
    public function registration_fails_with_invalid_data(): void
    {
        $response = $this->post('/register', [
            'name' => '', // Inválido
            'email' => 'not-an-email', // Inválido
            'password' => 'short', // Inválido
            'password_confirmation' => 'different', // Inválido
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }
}
