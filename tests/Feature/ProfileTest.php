<?php
// filepath: tests\Feature\ProfileTest.php

namespace Tests\Feature;

use App\Models\User; // Modelo User para crear usuarios de prueba.
use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use Illuminate\Support\Facades\Hash; // Fachada Hash para hashear contraseñas.
use PHPUnit\Framework\Attributes\Test; // Atributo para marcar métodos como tests (PHPUnit 10+).

/**
 * Class ProfileTest
 *
 * Suite de pruebas de Feature para verificar la funcionalidad del perfil de usuario,
 * probablemente asociada con `ProfileController`.
 * Cubre la visualización del perfil, la actualización de la información del perfil
 * (nombre, email), el manejo del estado de verificación del email y la eliminación
 * de la cuenta del usuario, incluyendo la validación de contraseña.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba.
 *
 * @package Tests\Feature
 */
class ProfileTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase,
     * asegurando que los tests no interfieran entre sí.
     */
    use RefreshDatabase;

    /**
     * Prueba que la página de visualización del perfil se muestra correctamente
     * para un usuario autenticado.
     *
     * Crea un usuario de prueba.
     * Simula una petición GET a la ruta '/profile' (asumiendo que es `profile.show`)
     * actuando como ese usuario.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que el nombre y el email del usuario sean visibles en la respuesta.
     *
     * @return void
     */
    #[Test]
    public function profile_page_is_displayed(): void
    {
        // Arrange: Crear un usuario.
        $user = User::factory()->create();

        // Act: Realizar la petición GET al perfil.
        $response = $this
            ->actingAs($user)
            ->get('/profile'); // Asume que es la ruta para profile.show.

        // Assert: Verificar la respuesta y el contenido.
        $response->assertOk(); // Verifica estado 200.
        $response->assertSee($user->name); // Verifica que se vea el nombre.
        $response->assertSee($user->email); // Verifica que se vea el email.
    }

    /**
     * Prueba que la información del perfil (nombre y email) puede ser actualizada.
     *
     * Crea un usuario de prueba.
     * Simula una petición PATCH a la ruta '/profile' (asumiendo que es `profile.update`)
     * actuando como ese usuario, enviando un nuevo nombre y email.
     * Verifica que la sesión no contenga errores de validación.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.edit'.
     * Refresca los datos del usuario desde la base de datos.
     * Verifica que el nombre y el email se hayan actualizado correctamente.
     * Verifica que el campo `email_verified_at` sea `null` (porque el email cambió).
     *
     * @return void
     */
    #[Test]
    public function profile_information_can_be_updated(): void
    {
        // Arrange: Crear un usuario.
        $user = User::factory()->create();

        // Act: Realizar la petición PATCH para actualizar el perfil.
        $response = $this
            ->actingAs($user)
            ->patch('/profile', [ // Asume que es la ruta para profile.update.
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        // Assert: Verificar ausencia de errores y redirección.
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit')); // Verifica redirección a profile.edit.

        // Assert: Verificar los datos actualizados en la BD.
        $user->refresh(); // Recarga los datos del usuario.

        $this->assertSame('Test User', $user->name); // Verifica nuevo nombre.
        $this->assertSame('test@example.com', $user->email); // Verifica nuevo email.
        $this->assertNull($user->email_verified_at); // Verifica que el email se desverificó.
    }

    /**
     * Prueba que el estado de verificación del email no cambia si el email no se modifica.
     *
     * Crea un usuario de prueba.
     * Simula una petición PATCH a la ruta '/profile' (asumiendo que es `profile.update`)
     * actuando como ese usuario, enviando un nuevo nombre pero manteniendo el mismo email.
     * Verifica que la sesión no contenga errores de validación.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.edit'.
     * Refresca los datos del usuario y verifica que el campo `email_verified_at`
     * no sea `null` (asumiendo que estaba verificado previamente).
     *
     * @return void
     */
    #[Test]
    public function email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        // Arrange: Crear un usuario.
        $user = User::factory()->create();

        // Act: Realizar la petición PATCH sin cambiar el email.
        $response = $this
            ->actingAs($user)
            ->patch('/profile', [ // Asume que es la ruta para profile.update.
                'name' => 'Test User',
                'email' => $user->email, // Mismo email.
            ]);

        // Assert: Verificar ausencia de errores y redirección.
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit')); // Verifica redirección a profile.edit.

        // Assert: Verificar que el estado de verificación no cambió.
        $this->assertNotNull($user->refresh()->email_verified_at); // Debe seguir verificado.
    }

    /**
     * Prueba que un usuario puede eliminar su propia cuenta.
     *
     * Crea un usuario de prueba con una contraseña conocida ('password').
     * Simula una petición DELETE a la ruta '/profile' (asumiendo que es `profile.destroy`)
     * actuando como ese usuario, enviando la contraseña correcta.
     * Verifica que la sesión no contenga errores de validación.
     * Verifica que la respuesta sea una redirección a la ruta raíz ('/').
     * Verifica que el usuario ya no esté autenticado (sea invitado).
     * Verifica que el registro del usuario haya sido eliminado de la base de datos.
     *
     * @return void
     */
    #[Test]
    public function user_can_delete_their_account(): void
    {
        // Arrange: Crear un usuario con contraseña conocida.
        $user = User::factory()->create([
             'password' => Hash::make('password'),
        ]);

        // Act: Realizar la petición DELETE para eliminar la cuenta con contraseña correcta.
        $response = $this
            ->actingAs($user)
            ->delete('/profile', [ // Asume que es la ruta para profile.destroy.
                'password' => 'password', // Contraseña correcta.
            ]);

        // Assert: Verificar ausencia de errores, redirección, estado de autenticación y BD.
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/'); // Redirige a la raíz.

        $this->assertGuest(); // Verifica que se deslogueó.
        $this->assertDatabaseMissing('users', ['id' => $user->id]); // Verifica que se borró.
    }

    /**
     * Prueba que se debe proporcionar la contraseña correcta para eliminar la cuenta.
     *
     * Crea un usuario de prueba con una contraseña conocida ('password').
     * Simula una petición DELETE a la ruta '/profile' (asumiendo que es `profile.destroy`)
     * actuando como ese usuario, enviando una contraseña incorrecta.
     * Verifica que la sesión contenga errores para el campo 'password' dentro del
     * error bag 'userDeletion'.
     * Verifica que la respuesta sea una redirección (de vuelta al formulario).
     * Refresca los datos del usuario y verifica que el usuario todavía exista (no fue eliminado).
     *
     * @return void
     */
    #[Test]
    public function correct_password_must_be_provided_to_delete_account(): void
    {
        // Arrange: Crear un usuario con contraseña conocida.
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        // Act: Realizar la petición DELETE con contraseña incorrecta.
        $response = $this
            ->actingAs($user)
            ->delete('/profile', [ // Asume que es la ruta para profile.destroy.
                'password' => 'wrong-password', // Contraseña incorrecta.
            ]);

        // Assert: Verificar errores de sesión, redirección y estado de la BD.
        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password') // Verifica error específico.
            ->assertRedirect(); // Verifica que hubo una redirección.

        $this->assertNotNull($user->fresh()); // Verifica que el usuario NO se borró.
    }
}
