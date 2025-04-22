<?php

namespace Tests\Feature\Auth;

use App\Models\User; // Modelo User para crear usuarios de prueba.
use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use Illuminate\Support\Facades\Hash; // Fachada Hash para hashear contraseñas.
use PHPUnit\Framework\Attributes\Test; // Atributo para marcar métodos como tests (PHPUnit 10+).

/**
 * Class AuthenticationTest
 *
 * Suite de pruebas de Feature para verificar las funcionalidades de autenticación
 * y registro de usuarios. Incluye pruebas para la renderización de las pantallas
 * de login y registro, el proceso de autenticación con credenciales válidas e inválidas,
 * y el proceso de registro de nuevos usuarios con datos válidos e inválidos.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba.
 *
 * @package Tests\Feature\Auth
 */
class AuthenticationTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase,
     * asegurando que los tests no interfieran entre sí.
     */
    use RefreshDatabase;

    /**
     * Prueba que la pantalla de inicio de sesión se puede renderizar correctamente.
     *
     * Simula una petición GET a la ruta '/login'.
     * Verifica que la respuesta HTTP tenga estado 200 (OK).
     *
     * @return void
     */
    #[Test]
    public function login_screen_can_be_rendered(): void
    {
        // Act: Realizar la petición GET a la ruta de login.
        $response = $this->get('/login');
        // Assert: Verificar que la respuesta sea exitosa.
        $response->assertStatus(200);
    }

    /**
     * Prueba que los usuarios pueden autenticarse usando la pantalla de inicio de sesión.
     *
     * Crea un usuario de prueba con un email, contraseña hasheada y rol específicos.
     * Simula una petición POST a la ruta '/login' con el email y la contraseña correcta.
     * Verifica que la respuesta sea una redirección a '/dashboard'.
     * Verifica que el usuario creado esté autenticado después de la petición.
     *
     * @return void
     */
    #[Test]
    public function users_can_authenticate_using_the_login_screen(): void
    {
        // Arrange: Crear un usuario de prueba.
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'), // Usa una contraseña conocida.
            'rol' => 'cliente', // Asigna un rol.
        ]);

        // Act: Realizar la petición POST a la ruta de login con credenciales correctas.
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123', // Usa la contraseña correcta.
        ]);

        // Assert: Verificar la redirección y el estado de autenticación.
        $response->assertRedirect('/dashboard'); // Verifica la redirección a /dashboard.
        $this->assertAuthenticatedAs($user); // Verifica que el usuario esté autenticado.
    }

    /**
     * Prueba que los usuarios no pueden autenticarse con una contraseña inválida.
     *
     * Crea un usuario de prueba.
     * Simula una petición POST a la ruta '/login' con el email correcto pero una contraseña incorrecta.
     * Verifica que el usuario no esté autenticado (siga siendo invitado) después de la petición.
     *
     * @return void
     */
    #[Test]
    public function users_can_not_authenticate_with_invalid_password(): void
    {
        // Arrange: Crear un usuario de prueba.
        $user = User::factory()->create();

        // Act: Realizar la petición POST con contraseña incorrecta.
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        // Assert: Verificar que el usuario no está autenticado.
        $this->assertGuest();
    }

    /**
     * Prueba que la pantalla de registro se puede renderizar correctamente.
     *
     * Simula una petición GET a la ruta '/register'.
     * Verifica que la respuesta HTTP tenga estado 200 (OK).
     *
     * @return void
     */
    #[Test]
    public function registration_screen_can_be_rendered(): void
    {
        // Act: Realizar la petición GET a la ruta de registro.
        $response = $this->get('/register');
        // Assert: Verificar que la respuesta sea exitosa.
        $response->assertStatus(200);
    }

    /**
     * Prueba que nuevos usuarios pueden registrarse.
     *
     * Simula una petición POST a la ruta '/register' con datos válidos para un nuevo usuario.
     * Verifica que el usuario esté autenticado después del registro.
     * Verifica que la respuesta sea una redirección a '/dashboard'.
     * Verifica que el usuario exista en la base de datos con el email proporcionado y el rol 'cliente'.
     *
     * @return void
     */
    #[Test]
    public function new_users_can_register(): void
    {
        // Act: Realizar la petición POST a la ruta de registro con datos válidos.
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Assert: Verificar estado de autenticación, redirección y estado de la BD.
        $this->assertAuthenticated(); // Verifica que el usuario esté autenticado.
        $response->assertRedirect('/dashboard'); // Verifica la redirección a /dashboard.
        // Verifica que el usuario se creó en la BD con el rol 'cliente'.
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'rol' => 'cliente']);
    }

     /**
      * Prueba que el registro falla si se proporcionan datos inválidos.
      *
      * Simula una petición POST a la ruta '/register' con datos inválidos
      * (nombre vacío, email no válido, contraseña corta, confirmación diferente).
      * Verifica que la sesión contenga errores de validación para los campos
      * 'name', 'email' y 'password'.
      * Verifica que el usuario no esté autenticado (siga siendo invitado) después del intento.
      *
      * @return void
      */
     #[Test]
    public function registration_fails_with_invalid_data(): void
    {
        // Act: Realizar la petición POST con datos inválidos.
        $response = $this->post('/register', [
            'name' => '', // Inválido
            'email' => 'not-an-email', // Inválido
            'password' => 'short', // Inválido
            'password_confirmation' => 'different', // Inválido
        ]);

        // Assert: Verificar errores de validación y estado de autenticación.
        $response->assertSessionHasErrors(['name', 'email', 'password']); // Verifica errores específicos.
        $this->assertGuest(); // Verifica que el usuario no está autenticado.
    }
}
