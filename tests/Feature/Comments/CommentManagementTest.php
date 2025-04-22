<?php
// filepath: tests\Feature\Comments\CommentManagementTest.php

namespace Tests\Feature\Comments;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\User; // Modelo User para crear usuarios de prueba.
use App\Models\Libros; // Modelo Libros para asociar comentarios.
use App\Models\Comentarios; // Modelo Comentarios para crear y verificar comentarios.
use PHPUnit\Framework\Attributes\Test; // Atributo para marcar métodos como tests (PHPUnit 10+).

/**
 * Class CommentManagementTest
 *
 * Suite de pruebas de Feature para verificar la funcionalidad de gestión de comentarios.
 * Comprueba que los usuarios autenticados puedan publicar comentarios, que los propietarios
 * y administradores puedan editar, actualizar y eliminar comentarios, y que se apliquen
 * las restricciones de acceso correctas para invitados y otros usuarios.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba.
 *
 * @package Tests\Feature\Comments
 */
class CommentManagementTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase,
     * asegurando que los tests no interfieran entre sí.
     */
    use RefreshDatabase;

    /**
     * @var User Instancia del usuario principal utilizada en las pruebas (propietario del comentario).
     */
    private User $user;
    /**
     * @var User Instancia de otro usuario cliente utilizada para pruebas de autorización.
     */
    private User $otherUser;
    /**
     * @var User Instancia del usuario administrador utilizada en las pruebas.
     */
    private User $admin;
    /**
     * @var Libros Instancia de un libro utilizada para asociar comentarios.
     */
    private Libros $libro;

    /**
     * Configura el entorno de prueba antes de cada test.
     *
     * Crea un usuario principal, otro usuario cliente, un usuario administrador
     * y un libro utilizando factories. Estas instancias se almacenan en propiedades
     * de la clase para ser utilizadas en los diferentes métodos de prueba.
     * Llama al método `setUp` de la clase padre.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp(); // Llama al método setUp de la clase padre.
        // Crea el usuario principal.
        $this->user = User::factory()->create();
        // Crea otro usuario cliente.
        $this->otherUser = User::factory()->create();
        // Crea un usuario administrador.
        $this->admin = User::factory()->create(['rol' => 'administrador']);
        // Crea un libro de prueba.
        $this->libro = Libros::factory()->create();
    }

    /**
     * Prueba que un usuario autenticado puede publicar un comentario.
     *
     * Define los datos para un nuevo comentario (ID del libro, texto, puntuación).
     * Simula una petición POST a la ruta 'comentarios.store' actuando como el usuario `$user`.
     * Verifica que la respuesta sea una redirección a la página de detalles del libro.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el comentario exista en la base de datos con los datos proporcionados
     * y asociado al usuario y libro correctos.
     *
     * @return void
     */
    #[Test]
    public function authenticated_user_can_post_a_comment(): void
    {
        // Arrange: Definir datos del comentario.
        $commentData = [
            'libro_id' => $this->libro->id,
            'texto' => 'Este es un comentario de prueba.',
            'puntuacion' => 4,
        ];

        // Act: Realizar la petición POST como usuario autenticado.
        $response = $this->actingAs($this->user)->post(route('comentarios.store'), $commentData);

        // Assert: Verificar redirección, mensaje de sesión y estado de la BD.
        $response->assertRedirect(route('libros.show', $this->libro->id));
        $response->assertSessionHas('success', 'Comentario añadido correctamente.');
        $this->assertDatabaseHas('comentarios', [
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
            'comentario' => 'Este es un comentario de prueba.',
            'puntuacion' => 4,
        ]);
    }

    /**
     * Prueba que un usuario no autenticado (invitado) no puede publicar un comentario.
     *
     * Define los datos para un comentario.
     * Simula una petición POST a la ruta 'comentarios.store' sin autenticar usuario.
     * Verifica que la respuesta sea una redirección a la ruta nombrada 'login'.
     * Verifica que no se haya creado ningún comentario en la base de datos.
     *
     * @return void
     */
    #[Test]
    public function guest_cannot_post_a_comment(): void
    {
        // Arrange: Definir datos del comentario.
        $commentData = [
            'libro_id' => $this->libro->id,
            'texto' => 'Comentario de invitado.',
            'puntuacion' => 5,
        ];

        // Act: Realizar la petición POST como invitado.
        $response = $this->post(route('comentarios.store'), $commentData);
        // Assert: Verificar redirección y estado de la BD.
        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('comentarios', 0);
    }

    /**
     * Prueba que la creación de un comentario falla si los datos son inválidos.
     *
     * Simula una petición POST a la ruta 'comentarios.store' actuando como usuario,
     * enviando datos inválidos (ID de libro no existente, texto vacío, puntuación fuera de rango).
     * Verifica que la sesión contenga errores de validación para los campos
     * 'libro_id', 'texto' y 'puntuacion'.
     *
     * @return void
     */
    #[Test]
    public function comment_store_fails_with_invalid_data(): void
    {
        // Act: Realizar la petición POST con datos inválidos.
        $response = $this->actingAs($this->user)->post(route('comentarios.store'), [
            'libro_id' => 999, // No existe
            'texto' => '', // Vacío
            'puntuacion' => 6, // Inválido
        ]);
        // Assert: Verificar errores de validación en la sesión.
        $response->assertSessionHasErrors(['libro_id', 'texto', 'puntuacion']);
    }

    /**
     * Prueba que el propietario de un comentario puede ver el formulario de edición.
     *
     * Crea un comentario asociado al usuario `$user`.
     * Simula una petición GET a la ruta 'comentarios.edit' para ese comentario, actuando como `$user`.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'comentarios.edit'.
     * Verifica que la vista reciba la variable 'comentarios' con la instancia del comentario correcto.
     * Verifica que el texto del comentario sea visible dentro del textarea en la respuesta HTML.
     *
     * @return void
     */
    #[Test]
    public function comment_owner_can_view_edit_comment_form(): void
    {
        // Arrange: Crear un comentario para el usuario.
        $commentText = 'Texto simple para prueba de edicion.';
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
            'comentario' => $commentText,
        ]);

        // Act: Realizar la petición GET como propietario del comentario.
        $response = $this->actingAs($this->user)->get(route('comentarios.edit', $comentario));

        // Assert: Verificar la respuesta, vista, datos y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('comentarios.edit');
        $response->assertViewHas('comentarios', $comentario);
        // Verifica que el texto del comentario esté dentro del textarea.
        $response->assertSee('>'.$commentText.'</textarea>', false);
    }

    /**
     * Prueba que un administrador puede ver el formulario de edición del comentario de cualquier usuario.
     *
     * Crea un comentario asociado al usuario `$user`.
     * Simula una petición GET a la ruta 'comentarios.edit' para ese comentario, actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que el texto del comentario sea visible dentro del textarea en la respuesta HTML.
     *
     * @return void
     */
    #[Test]
    public function admin_can_view_edit_comment_form_of_any_user(): void
    {
        // Arrange: Crear un comentario para otro usuario.
        $commentText = 'Otro texto simple para admin.';
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id, // Comentario de $user.
            'libro_id' => $this->libro->id,
            'comentario' => $commentText,
        ]);

        // Act: Realizar la petición GET como administrador.
        $response = $this->actingAs($this->admin)->get(route('comentarios.edit', $comentario));

        // Assert: Verificar la respuesta y el contenido.
        $response->assertStatus(200);
        // Verifica que el texto del comentario esté dentro del textarea.
        $response->assertSee('>'.$commentText.'</textarea>', false);
    }

    /**
     * Prueba que un usuario no puede ver el formulario de edición del comentario de otro usuario.
     *
     * Crea un comentario asociado al usuario `$user`.
     * Simula una petición GET a la ruta 'comentarios.edit' para ese comentario, actuando como `$otherUser`.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.show'.
     * Verifica que la sesión contenga un mensaje de error específico.
     *
     * @return void
     */
    #[Test]
    public function other_user_cannot_view_edit_comment_form(): void
    {
        // Arrange: Crear un comentario para $user.
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);

        // Act: Realizar la petición GET como otro usuario.
        $response = $this->actingAs($this->otherUser)->get(route('comentarios.edit', $comentario));
        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error', 'No tienes permiso para editar este comentario.');
    }

    /**
     * Prueba que el propietario de un comentario puede actualizarlo.
     *
     * Crea un comentario asociado al usuario `$user`.
     * Define los nuevos datos para la actualización (texto y puntuación).
     * Simula una petición PUT a la ruta 'comentarios.update' para ese comentario, actuando como `$user`.
     * Verifica que la respuesta sea una redirección a la página de detalles del libro.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el comentario exista en la base de datos con los datos actualizados.
     *
     * @return void
     */
    #[Test]
    public function comment_owner_can_update_comment(): void
    {
        // Arrange: Crear un comentario y definir datos de actualización.
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);
        $updateData = ['texto' => 'Comentario actualizado', 'puntuacion' => 3];

        // Act: Realizar la petición PUT como propietario.
        $response = $this->actingAs($this->user)->put(route('comentarios.update', $comentario), $updateData);

        // Assert: Verificar redirección, mensaje y estado de la BD.
        $response->assertRedirect(route('libros.show', $this->libro->id));
        $response->assertSessionHas('success', 'Comentario actualizado correctamente.');
        $this->assertDatabaseHas('comentarios', ['id' => $comentario->id, 'comentario' => 'Comentario actualizado', 'puntuacion' => 3]);
    }

    /**
     * Prueba que un administrador puede actualizar el comentario de cualquier usuario.
     *
     * Crea un comentario asociado al usuario `$user`.
     * Define los nuevos datos para la actualización.
     * Simula una petición PUT a la ruta 'comentarios.update' para ese comentario, actuando como administrador.
     * Verifica que la respuesta sea una redirección a la página de detalles del libro.
     * Verifica que el comentario exista en la base de datos con el texto actualizado.
     *
     * @return void
     */
    #[Test]
    public function admin_can_update_comment_of_any_user(): void
    {
        // Arrange: Crear un comentario para $user y definir datos de actualización.
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);
        $updateData = ['texto' => 'Actualizado por admin', 'puntuacion' => 1];

        // Act: Realizar la petición PUT como administrador.
        $response = $this->actingAs($this->admin)->put(route('comentarios.update', $comentario), $updateData);
        // Assert: Verificar redirección y estado de la BD.
        $response->assertRedirect(route('libros.show', $this->libro->id));
        $this->assertDatabaseHas('comentarios', ['id' => $comentario->id, 'comentario' => 'Actualizado por admin']);
    }

    /**
     * Prueba que un usuario no puede actualizar el comentario de otro usuario.
     *
     * Crea un comentario asociado al usuario `$user` con texto 'Original'.
     * Define datos de actualización.
     * Simula una petición PUT a la ruta 'comentarios.update' para ese comentario, actuando como `$otherUser`.
     * Verifica que la respuesta HTTP tenga estado 403 (Forbidden).
     * Verifica que el comentario en la base de datos conserve su texto original.
     *
     * @return void
     */
    #[Test]
    public function other_user_cannot_update_comment(): void
    {
        // Arrange: Crear un comentario para $user y definir datos de actualización.
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
            'comentario' => 'Original'
        ]);
        $updateData = ['texto' => 'Intento fallido', 'puntuacion' => 2];

        // Act: Realizar la petición PUT como otro usuario.
        $response = $this->actingAs($this->otherUser)->put(route('comentarios.update', $comentario), $updateData);
        // Assert: Verificar estado 403 y estado de la BD.
        $response->assertStatus(403);
        $this->assertDatabaseHas('comentarios', ['id' => $comentario->id, 'comentario' => 'Original']);
    }

    /**
     * Prueba que el propietario de un comentario puede eliminarlo.
     *
     * Crea un comentario asociado al usuario `$user`.
     * Simula una petición DELETE a la ruta 'comentarios.destroy' para ese comentario, actuando como `$user`.
     * Verifica que la respuesta sea una redirección a la página de detalles del libro.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el comentario ya no exista en la base de datos.
     *
     * @return void
     */
    #[Test]
    public function comment_owner_can_delete_comment(): void
    {
        // Arrange: Crear un comentario para $user.
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);

        // Act: Realizar la petición DELETE como propietario.
        $response = $this->actingAs($this->user)->delete(route('comentarios.destroy', $comentario));

        // Assert: Verificar redirección, mensaje y estado de la BD.
        $response->assertRedirect(route('libros.show', $this->libro->id));
        $response->assertSessionHas('success', 'Comentario eliminado correctamente.');
        $this->assertDatabaseMissing('comentarios', ['id' => $comentario->id]);
    }

     /**
      * Prueba que un administrador puede eliminar el comentario de cualquier usuario.
      *
      * Crea un comentario asociado al usuario `$user`.
      * Simula una petición DELETE a la ruta 'comentarios.destroy' para ese comentario, actuando como administrador.
      * Verifica que la respuesta sea una redirección a la página de detalles del libro.
      * Verifica que el comentario ya no exista en la base de datos.
      *
      * @return void
      */
     #[Test]
    public function admin_can_delete_comment_of_any_user(): void
    {
        // Arrange: Crear un comentario para $user.
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);

        // Act: Realizar la petición DELETE como administrador.
        $response = $this->actingAs($this->admin)->delete(route('comentarios.destroy', $comentario));
        // Assert: Verificar redirección y estado de la BD.
        $response->assertRedirect(route('libros.show', $this->libro->id));
        $this->assertDatabaseMissing('comentarios', ['id' => $comentario->id]);
    }

    /**
     * Prueba que un usuario no puede eliminar el comentario de otro usuario.
     *
     * Crea un comentario asociado al usuario `$user`.
     * Simula una petición DELETE a la ruta 'comentarios.destroy' para ese comentario, actuando como `$otherUser`.
     * Verifica que la respuesta HTTP tenga estado 403 (Forbidden).
     * Verifica que el comentario todavía exista en la base de datos.
     *
     * @return void
     */
    #[Test]
    public function other_user_cannot_delete_comment(): void
    {
        // Arrange: Crear un comentario para $user.
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);

        // Act: Realizar la petición DELETE como otro usuario.
        $response = $this->actingAs($this->otherUser)->delete(route('comentarios.destroy', $comentario));
        // Assert: Verificar estado 403 y estado de la BD.
        $response->assertStatus(403);
        $this->assertDatabaseHas('comentarios', ['id' => $comentario->id]);
    }
}
