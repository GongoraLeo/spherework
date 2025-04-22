<?php
// filepath: tests\Feature\Profile\UserProfileTest.php

namespace Tests\Feature\Profile;

use App\Models\User; // Modelo User para crear usuarios de prueba.
use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use Illuminate\Support\Facades\Hash; // Fachada Hash para hashear contraseñas.

/**
 * Class UserProfileTest
 *
 * Suite de pruebas de Feature para verificar la funcionalidad del perfil de usuario.
 * Comprueba que un usuario autenticado pueda ver su perfil, ver el formulario de edición,
 * actualizar su información (nombre, email) y eliminar su cuenta, incluyendo la
 * validación de la contraseña actual para la eliminación y el manejo de la
 * verificación de email al cambiar la dirección de correo.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba.
 *
 * @package Tests\Feature\Profile
 */
class UserProfileTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase,
     * asegurando que los tests no interfieran entre sí.
     */
    use RefreshDatabase;

    /**
     * @var User Instancia del usuario utilizada en las pruebas.
     */
    private User $user;

    /**
     * Configura el entorno de prueba antes de cada test.
     *
     * Crea un usuario de prueba utilizando la factory, estableciendo una
     * contraseña conocida ('old-password') hasheada. Esta instancia se almacena
     * en la propiedad `$user` para ser utilizada en los métodos de prueba.
     * Llama al método `setUp` de la clase padre.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp(); // Llama al método setUp de la clase padre.
        // Crea un usuario con una contraseña específica hasheada.
        $this->user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
    }

    /**
     * Prueba que la página de visualización del perfil se muestra correctamente.
     *
     * Simula una petición GET a la ruta 'profile.show' actuando como el usuario `$user`.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'profile.show'.
     * Verifica que la vista reciba la variable 'user' con la instancia del usuario correcto.
     * Verifica que el nombre y el email del usuario sean visibles en la respuesta.
     *
     * @test
     * @return void
     */
    public function profile_page_is_displayed(): void
    {
        // Act: Realizar la petición GET al perfil.
        $response = $this->actingAs($this->user)->get(route('profile.show'));
        // Assert: Verificar la respuesta, vista, datos y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('profile.show');
        $response->assertViewHas('user', $this->user);
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
    }

    /**
     * Prueba que la página de edición del perfil se muestra correctamente.
     *
     * Simula una petición GET a la ruta 'profile.edit' actuando como el usuario `$user`.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'profile.edit'.
     * Verifica que la vista reciba la variable 'user' con la instancia del usuario correcto.
     *
     * @test
     * @return void
     */
    public function profile_edit_page_is_displayed(): void
    {
        // Act: Realizar la petición GET al formulario de edición.
        $response = $this->actingAs($this->user)->get(route('profile.edit'));
        // Assert: Verificar la respuesta, vista y datos.
        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
        $response->assertViewHas('user', $this->user);
    }

    /**
     * Prueba que la información del perfil (nombre y email) puede ser actualizada.
     *
     * Simula una petición PATCH a la ruta 'profile.update' actuando como el usuario `$user`,
     * enviando un nuevo nombre y un nuevo email.
     * Verifica que la sesión no contenga errores de validación.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.edit'.
     * Verifica que la sesión contenga el estado 'profile-updated'.
     * Refresca los datos del usuario desde la base de datos.
     * Verifica que el nombre y el email del usuario se hayan actualizado correctamente.
     * Verifica que el campo `email_verified_at` sea `null` (porque el email cambió).
     *
     * @test
     * @return void
     */
    public function profile_information_can_be_updated(): void
    {
        // Act: Realizar la petición PATCH para actualizar el perfil.
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => 'Test User Updated',
                'email' => 'test.updated@example.com',
            ]);

        // Assert: Verificar la ausencia de errores, redirección y estado de sesión.
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'profile-updated');

        // Assert: Verificar los datos actualizados en la BD.
        $this->user->refresh(); // Recarga los datos del usuario.

        $this->assertSame('Test User Updated', $this->user->name); // Verifica nuevo nombre.
        $this->assertSame('test.updated@example.com', $this->user->email); // Verifica nuevo email.
        $this->assertNull($this->user->email_verified_at); // Verifica que el email se desverificó.
    }

    /**
     * Prueba que el estado de verificación del email no cambia si el email no se modifica.
     *
     * Simula una petición PATCH a la ruta 'profile.update' actuando como el usuario `$user`,
     * enviando un nuevo nombre pero manteniendo el mismo email.
     * Verifica que la sesión no contenga errores de validación.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.edit'.
     * Refresca los datos del usuario y verifica que el campo `email_verified_at`
     * no sea `null` (asumiendo que estaba verificado previamente).
     *
     * @test
     * @return void
     */
    public function email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        // Act: Realizar la petición PATCH sin cambiar el email.
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => 'Test User Updated',
                'email' => $this->user->email, // Mismo email.
            ]);

        // Assert: Verificar la ausencia de errores y la redirección.
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));

        // Assert: Verificar que el estado de verificación no cambió.
        // Asume que el usuario creado por la factory está verificado por defecto o se verifica antes.
        $this->assertNotNull($this->user->refresh()->email_verified_at); // Debe seguir verificado.
    }

    /**
     * Prueba que un usuario puede eliminar su propia cuenta.
     *
     * Simula una petición DELETE a la ruta 'profile.destroy' actuando como el usuario `$user`,
     * enviando la contraseña correcta ('old-password').
     * Verifica que la sesión no contenga errores de validación.
     * Verifica que la respuesta sea una redirección a la ruta raíz ('/').
     * Verifica que el usuario ya no esté autenticado (sea invitado).
     * Verifica que el registro del usuario haya sido eliminado de la base de datos.
     *
     * @test
     * @return void
     */
    public function user_can_delete_their_account(): void
    {
        // Act: Realizar la petición DELETE para eliminar la cuenta con contraseña correcta.
        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'), [
                'password' => 'old-password', // Contraseña correcta.
            ]);

        // Assert: Verificar ausencia de errores, redirección, estado de autenticación y BD.
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/'); // Redirige a la raíz.
        $this->assertGuest(); // Verifica que se deslogueó.
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]); // Verifica que se borró.
    }

    /**
     * Prueba que se debe proporcionar la contraseña correcta para eliminar la cuenta.
     *
     * Simula una petición DELETE a la ruta 'profile.destroy' actuando como el usuario `$user`,
     * enviando una contraseña incorrecta ('wrong-password').
     * Verifica que la sesión contenga errores para el campo 'password' dentro del
     * error bag 'userDeletion'.
     * Verifica que la respuesta sea una redirección (de vuelta al formulario).
     * Refresca los datos del usuario y verifica que el usuario todavía exista (no fue eliminado).
     *
     * @test
     * @return void
     */
    public function correct_password_must_be_provided_to_delete_account(): void
    {
        // Act: Realizar la petición DELETE con contraseña incorrecta.
        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'), [
                'password' => 'wrong-password', // Contraseña incorrecta.
            ]);

        // Assert: Verificar errores de sesión, redirección y estado de la BD.
        // Verifica error específico para 'password' en el bag 'userDeletion'.
        $response->assertSessionHasErrorsIn('userDeletion', 'password');
        $response->assertRedirect(); // Debería redirigir de vuelta.
        $this->assertNotNull($this->user->fresh()); // Verifica que el usuario NO se borró.
    }
}
