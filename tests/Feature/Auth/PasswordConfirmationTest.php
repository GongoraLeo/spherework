<?php
// filepath: tests\Feature\Auth\PasswordConfirmationTest.php

use App\Models\User; // Modelo User para crear usuarios de prueba.
use Illuminate\Foundation\Testing\RefreshDatabase; // Importar RefreshDatabase si no está en TestCase global.

// Asumiendo que RefreshDatabase se usa globalmente o en TestCase, si no, añadir:
// uses(RefreshDatabase::class);

/**
 * Suite de pruebas de Feature para la funcionalidad de confirmación de contraseña.
 *
 * Estas pruebas verifican que la pantalla de confirmación de contraseña se pueda renderizar,
 * que la contraseña pueda ser confirmada correctamente proporcionando la contraseña válida,
 * y que la confirmación falle si se proporciona una contraseña inválida.
 * Utiliza el formato de pruebas de Pest.
 */

/**
 * Prueba que la pantalla de confirmación de contraseña se puede renderizar.
 *
 * Crea un usuario de prueba usando la factory.
 * Simula una petición GET a la ruta '/confirm-password' actuando como ese usuario.
 * Verifica que la respuesta HTTP tenga estado 200 (OK).
 */
test('confirm password screen can be rendered', function () {
    // Arrange: Crear un usuario.
    $user = User::factory()->create();

    // Act: Realizar la petición GET a la pantalla de confirmación.
    $response = $this->actingAs($user)->get('/confirm-password');

    // Assert: Verificar que la respuesta sea exitosa.
    $response->assertStatus(200);
});

/**
 * Prueba que la contraseña puede ser confirmada correctamente.
 *
 * Crea un usuario de prueba (la contraseña por defecto de la factory es 'password').
 * Simula una petición POST a la ruta '/confirm-password' actuando como ese usuario,
 * enviando la contraseña correcta ('password').
 * Verifica que la respuesta sea una redirección (indicando éxito).
 * Verifica que la sesión no contenga errores de validación.
 */
test('password can be confirmed', function () {
    // Arrange: Crear un usuario.
    $user = User::factory()->create();

    // Act: Realizar la petición POST con la contraseña correcta.
    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'password', // Contraseña por defecto de la factory.
    ]);

    // Assert: Verificar la redirección y la ausencia de errores.
    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

/**
 * Prueba que la contraseña no se confirma si se proporciona una contraseña inválida.
 *
 * Crea un usuario de prueba.
 * Simula una petición POST a la ruta '/confirm-password' actuando como ese usuario,
 * enviando una contraseña incorrecta ('wrong-password').
 * Verifica que la sesión contenga errores de validación (indicando que la contraseña no coincidió).
 */
test('password is not confirmed with invalid password', function () {
    // Arrange: Crear un usuario.
    $user = User::factory()->create();

    // Act: Realizar la petición POST con una contraseña incorrecta.
    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'wrong-password',
    ]);

    // Assert: Verificar que la sesión contenga errores.
    $response->assertSessionHasErrors();
});
