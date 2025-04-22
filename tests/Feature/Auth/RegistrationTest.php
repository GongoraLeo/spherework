<?php
// filepath: tests\Feature\Auth\RegistrationTest.php

use Illuminate\Foundation\Testing\RefreshDatabase; // Importar RefreshDatabase si no está en TestCase global.
use App\Providers\RouteServiceProvider; // Para la ruta de redirección HOME (aunque se usa '/dashboard' directamente).

// Asumiendo que RefreshDatabase se usa globalmente o en TestCase, si no, añadir:
// uses(RefreshDatabase::class);

/**
 * Suite de pruebas de Feature para la funcionalidad de registro de usuarios.
 *
 * Estas pruebas verifican que la pantalla de registro se pueda renderizar
 * y que un nuevo usuario pueda registrarse correctamente.
 * Utiliza el formato de pruebas de Pest.
 */

/**
 * Prueba que la pantalla de registro se puede renderizar correctamente.
 *
 * Simula una petición GET a la ruta '/register'.
 * Verifica que la respuesta HTTP tenga estado 200 (OK).
 */
test('registration screen can be rendered', function () {
    // Act: Realizar la petición GET a la ruta de registro.
    $response = $this->get('/register');

    // Assert: Verificar que la respuesta sea exitosa.
    $response->assertStatus(200);
});

/**
 * Prueba que nuevos usuarios pueden registrarse correctamente.
 *
 * Simula una petición POST a la ruta '/register' con datos válidos
 * (nombre, email, contraseña y confirmación).
 * Verifica que el usuario esté autenticado después de la petición.
 * Verifica que la respuesta sea una redirección a la ruta 'dashboard'.
 */
test('new users can register', function () {
    // Act: Realizar la petición POST para registrar un nuevo usuario.
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password', // Contraseña válida.
        'password_confirmation' => 'password', // Confirmación coincidente.
    ]);

    // Assert: Verificar el estado de autenticación y la redirección.
    $this->assertAuthenticated(); // Verifica que el usuario esté autenticado.
    // Verifica la redirección a la ruta 'dashboard'.
    $response->assertRedirect(route('dashboard', absolute: false));
});
