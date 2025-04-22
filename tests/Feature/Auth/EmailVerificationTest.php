<?php

use App\Models\User; // Modelo User para crear usuarios de prueba.
use Illuminate\Auth\Events\Verified; // Evento que se dispara cuando un email es verificado.
use Illuminate\Support\Facades\Event; // Fachada para interactuar con el sistema de eventos (para simular).
use Illuminate\Support\Facades\URL; // Fachada para generar URLs firmadas.
use Illuminate\Foundation\Testing\RefreshDatabase; // Importar RefreshDatabase si no está en TestCase global.

// Asumiendo que RefreshDatabase se usa globalmente o en TestCase, si no, añadir:
// uses(RefreshDatabase::class);

/**
 * Suite de pruebas de Feature para la funcionalidad de verificación de correo electrónico.
 *
 * Estas pruebas verifican que la pantalla de verificación se pueda renderizar,
 * que un email pueda ser verificado correctamente usando una URL firmada válida,
 * y que la verificación falle si se utiliza un hash inválido en la URL.
 * Utiliza el formato de pruebas de Pest.
 */

/**
 * Prueba que la pantalla de notificación de verificación de email se puede renderizar.
 *
 * Crea un usuario no verificado usando la factory.
 * Simula una petición GET a la ruta '/verify-email' actuando como ese usuario.
 * Verifica que la respuesta HTTP tenga estado 200 (OK).
 */
test('email verification screen can be rendered', function () {
    // Arrange: Crear un usuario no verificado.
    $user = User::factory()->unverified()->create();

    // Act: Realizar la petición GET a la pantalla de verificación.
    $response = $this->actingAs($user)->get('/verify-email');

    // Assert: Verificar que la respuesta sea exitosa.
    $response->assertStatus(200);
});

/**
 * Prueba que el email de un usuario puede ser verificado correctamente.
 *
 * Crea un usuario no verificado.
 * Simula el sistema de eventos de Laravel para poder verificar si se dispara el evento 'Verified'.
 * Genera una URL de verificación temporalmente firmada para la ruta 'verification.verify',
 * incluyendo el ID del usuario y un hash SHA1 de su email.
 * Simula una petición GET a esta URL de verificación actuando como el usuario.
 * Verifica que el evento 'Verified' haya sido disparado.
 * Verifica que el usuario (refrescado desde la BD) ahora tenga su email marcado como verificado.
 * Verifica que la respuesta sea una redirección a la ruta 'dashboard' con el parámetro '?verified=1'.
 */
test('email can be verified', function () {
    // Arrange: Crear un usuario no verificado y simular eventos.
    $user = User::factory()->unverified()->create();
    Event::fake();

    // Arrange: Generar la URL de verificación firmada.
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify', // Nombre de la ruta.
        now()->addMinutes(60), // Tiempo de expiración de la URL.
        ['id' => $user->id, 'hash' => sha1($user->email)] // Parámetros requeridos por la ruta.
    );

    // Act: Realizar la petición GET a la URL de verificación.
    $response = $this->actingAs($user)->get($verificationUrl);

    // Assert: Verificar que el evento fue disparado, el estado del usuario y la redirección.
    Event::assertDispatched(Verified::class); // Verifica que el evento 'Verified' se disparó.
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue(); // Verifica que el email del usuario esté verificado.
    // Verifica la redirección a la ruta 'dashboard' con el parámetro 'verified=1'.
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

/**
 * Prueba que el email no se verifica si se proporciona un hash inválido.
 *
 * Crea un usuario no verificado.
 * Genera una URL de verificación temporalmente firmada, pero utiliza un hash incorrecto
 * (basado en 'wrong-email' en lugar del email real del usuario).
 * Simula una petición GET a esta URL inválida actuando como el usuario.
 * Verifica que el usuario (refrescado desde la BD) todavía tenga su email marcado como no verificado.
 */
test('email is not verified with invalid hash', function () {
    // Arrange: Crear un usuario no verificado.
    $user = User::factory()->unverified()->create();

    // Arrange: Generar una URL de verificación con un hash inválido.
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')] // Hash incorrecto.
    );

    // Act: Realizar la petición GET a la URL inválida.
    $this->actingAs($user)->get($verificationUrl);

    // Assert: Verificar que el email del usuario sigue sin estar verificado.
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
