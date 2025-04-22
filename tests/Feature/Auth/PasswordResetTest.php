<?php
// filepath: tests\Feature\Auth\PasswordResetTest.php

use App\Models\User; // Modelo User para crear usuarios de prueba.
use Illuminate\Auth\Notifications\ResetPassword; // Notificación enviada para el reseteo.
use Illuminate\Support\Facades\Notification; // Fachada para interactuar con el sistema de notificaciones (para simular).
use Illuminate\Foundation\Testing\RefreshDatabase; // Importar RefreshDatabase si no está en TestCase global.

// Asumiendo que RefreshDatabase se usa globalmente o en TestCase, si no, añadir:
// uses(RefreshDatabase::class);

/**
 * Suite de pruebas de Feature para la funcionalidad de reseteo de contraseña.
 *
 * Estas pruebas verifican que la pantalla de solicitud de enlace de reseteo se pueda renderizar,
 * que se envíe la notificación de reseteo al solicitarlo, que la pantalla de reseteo
 * con un token válido se pueda renderizar, y que la contraseña se pueda resetear
 * correctamente proporcionando un token válido y la nueva contraseña.
 * Utiliza el formato de pruebas de Pest.
 */

/**
 * Prueba que la pantalla de solicitud de enlace de reseteo de contraseña se puede renderizar.
 *
 * Simula una petición GET a la ruta '/forgot-password'.
 * Verifica que la respuesta HTTP tenga estado 200 (OK).
 */
test('reset password link screen can be rendered', function () {
    // Act: Realizar la petición GET a la pantalla de olvido de contraseña.
    $response = $this->get('/forgot-password');

    // Assert: Verificar que la respuesta sea exitosa.
    $response->assertStatus(200);
});

/**
 * Prueba que se puede solicitar el enlace de reseteo de contraseña.
 *
 * Simula el sistema de notificaciones de Laravel.
 * Crea un usuario de prueba.
 * Simula una petición POST a la ruta '/forgot-password' con el email del usuario.
 * Verifica que se haya enviado la notificación `ResetPassword` al usuario creado.
 */
test('reset password link can be requested', function () {
    // Arrange: Simular notificaciones y crear un usuario.
    Notification::fake();
    $user = User::factory()->create();

    // Act: Realizar la petición POST para solicitar el enlace de reseteo.
    $this->post('/forgot-password', ['email' => $user->email]);

    // Assert: Verificar que la notificación de reseteo fue enviada al usuario.
    Notification::assertSentTo($user, ResetPassword::class);
});

/**
 * Prueba que la pantalla de reseteo de contraseña se puede renderizar con un token válido.
 *
 * Simula el sistema de notificaciones.
 * Crea un usuario de prueba.
 * Simula la solicitud del enlace de reseteo (POST a '/forgot-password').
 * Utiliza `Notification::assertSentTo` con una función de callback para capturar
 * la notificación enviada y extraer el token de reseteo.
 * Dentro del callback, simula una petición GET a la ruta '/reset-password/{token}'
 * utilizando el token capturado.
 * Verifica que la respuesta a esta petición GET tenga estado 200 (OK).
 */
test('reset password screen can be rendered', function () {
    // Arrange: Simular notificaciones y crear un usuario.
    Notification::fake();
    $user = User::factory()->create();

    // Act 1: Solicitar el enlace de reseteo.
    $this->post('/forgot-password', ['email' => $user->email]);

    // Assert & Act 2: Verificar que se envió la notificación y probar la pantalla de reseteo.
    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        // Act 2.1: Realizar la petición GET a la pantalla de reseteo con el token.
        $response = $this->get('/reset-password/'.$notification->token);

        // Assert 2.1: Verificar que la pantalla se renderiza correctamente.
        $response->assertStatus(200);

        return true; // Indica que la aserción dentro del callback fue exitosa.
    });
});

/**
 * Prueba que la contraseña puede ser reseteada con un token válido.
 *
 * Simula el sistema de notificaciones.
 * Crea un usuario de prueba.
 * Simula la solicitud del enlace de reseteo (POST a '/forgot-password').
 * Utiliza `Notification::assertSentTo` con una función de callback para capturar
 * la notificación y el token.
 * Dentro del callback, simula una petición POST a la ruta '/reset-password' enviando
 * el token capturado, el email del usuario, la nueva contraseña y su confirmación.
 * Verifica que la respuesta a esta petición POST no tenga errores de sesión.
 * Verifica que la respuesta sea una redirección a la ruta 'login'.
 */
test('password can be reset with valid token', function () {
    // Arrange: Simular notificaciones y crear un usuario.
    Notification::fake();
    $user = User::factory()->create();

    // Act 1: Solicitar el enlace de reseteo.
    $this->post('/forgot-password', ['email' => $user->email]);

    // Assert & Act 2: Verificar que se envió la notificación y probar el reseteo.
    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        // Act 2.1: Realizar la petición POST para resetear la contraseña.
        $response = $this->post('/reset-password', [
            'token' => $notification->token, // Token válido.
            'email' => $user->email, // Email del usuario.
            'password' => 'password', // Nueva contraseña.
            'password_confirmation' => 'password', // Confirmación de la nueva contraseña.
        ]);

        // Assert 2.1: Verificar la ausencia de errores y la redirección.
        $response
            ->assertSessionHasNoErrors() // No debe haber errores de validación.
            ->assertRedirect(route('login')); // Debe redirigir al login.

        return true; // Indica que la aserción dentro del callback fue exitosa.
    });
});
