<?php
// filepath: tests\Feature\Auth\PasswordUpdateTest.php

use App\Models\User; // Modelo User para crear usuarios de prueba.
use Illuminate\Support\Facades\Hash; // Fachada Hash para verificar contraseñas hasheadas.
use Illuminate\Foundation\Testing\RefreshDatabase; // Importar RefreshDatabase si no está en TestCase global.

// Asumiendo que RefreshDatabase se usa globalmente o en TestCase, si no, añadir:
// uses(RefreshDatabase::class);

/**
 * Suite de pruebas de Feature para la funcionalidad de actualización de contraseña.
 *
 * Estas pruebas verifican que un usuario autenticado pueda actualizar su contraseña
 * proporcionando la contraseña actual correcta, y que la actualización falle si
 * la contraseña actual proporcionada es incorrecta.
 * Utiliza el formato de pruebas de Pest.
 */

/**
 * Prueba que la contraseña puede ser actualizada correctamente.
 *
 * Crea un usuario de prueba (la contraseña por defecto de la factory es 'password').
 * Simula una petición PUT a la ruta '/password' (asumiendo que esta es la ruta de actualización)
 * actuando como ese usuario, originándose desde '/profile'. Envía la contraseña actual correcta
 * ('password'), la nueva contraseña ('new-password') y su confirmación.
 * Verifica que la respuesta no contenga errores de sesión.
 * Verifica que la respuesta sea una redirección de vuelta a '/profile'.
 * Verifica que la nueva contraseña ('new-password') coincida con la contraseña hasheada
 * almacenada en la base de datos para el usuario (refrescado).
 */
test('password can be updated', function () {
    // Arrange: Crear un usuario.
    $user = User::factory()->create();

    // Act: Realizar la petición PUT para actualizar la contraseña con datos válidos.
    $response = $this
        ->actingAs($user) // Actuar como el usuario creado.
        ->from('/profile') // Simular que la petición viene de la página de perfil.
        ->put('/password', [ // Enviar petición PUT a la ruta de actualización.
            'current_password' => 'password', // Contraseña actual correcta (por defecto de factory).
            'password' => 'new-password', // Nueva contraseña.
            'password_confirmation' => 'new-password', // Confirmación de la nueva contraseña.
        ]);

    // Assert: Verificar la ausencia de errores, la redirección y el cambio de contraseña.
    $response
        ->assertSessionHasNoErrors() // No debe haber errores de validación.
        ->assertRedirect('/profile'); // Debe redirigir de vuelta al perfil.

    // Verifica que la nueva contraseña hasheada coincida con la almacenada.
    $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
});

/**
 * Prueba que se debe proporcionar la contraseña correcta para actualizarla.
 *
 * Crea un usuario de prueba.
 * Simula una petición PUT a la ruta '/password' actuando como ese usuario,
 * originándose desde '/profile'. Envía una contraseña actual incorrecta ('wrong-password'),
 * junto con la nueva contraseña y su confirmación.
 * Verifica que la sesión contenga errores específicamente para el campo 'current_password'
 * dentro del error bag 'updatePassword'.
 * Verifica que la respuesta sea una redirección de vuelta a '/profile'.
 */
test('correct password must be provided to update password', function () {
    // Arrange: Crear un usuario.
    $user = User::factory()->create();

    // Act: Realizar la petición PUT con una contraseña actual incorrecta.
    $response = $this
        ->actingAs($user) // Actuar como el usuario creado.
        ->from('/profile') // Simular que la petición viene de la página de perfil.
        ->put('/password', [ // Enviar petición PUT a la ruta de actualización.
            'current_password' => 'wrong-password', // Contraseña actual incorrecta.
            'password' => 'new-password', // Nueva contraseña.
            'password_confirmation' => 'new-password', // Confirmación de la nueva contraseña.
        ]);

    // Assert: Verificar la presencia de errores específicos y la redirección.
    $response
        // Verifica que haya un error para 'current_password' en el error bag 'updatePassword'.
        ->assertSessionHasErrorsIn('updatePassword', 'current_password')
        ->assertRedirect('/profile'); // Debe redirigir de vuelta al perfil.
});
