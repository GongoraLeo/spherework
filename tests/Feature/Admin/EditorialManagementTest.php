<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\User; // Modelo User para crear usuarios de prueba.
use App\Models\Editoriales; // Modelo Editoriales para crear y verificar editoriales.
use App\Models\Libros; // Modelo Libros para probar la restricción de borrado.

/**
 * Class EditorialManagementTest
 *
 * Suite de pruebas de Feature para verificar la gestión completa (CRUD)
 * del recurso 'Editoriales' desde la perspectiva de un administrador.
 * Incluye pruebas de visualización, creación, almacenamiento, edición,
 * actualización y eliminación de editoriales, así como las restricciones
 * de acceso para usuarios no administradores (clientes) y la lógica
 * de negocio (ej. no poder eliminar editoriales con libros asociados).
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba. Pertenece al grupo de pruebas 'admin'.
 *
 * @group admin
 * @package Tests\Feature\Admin
 */
class EditorialManagementTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase,
     * asegurando que los tests no interfieran entre sí.
     */
    use RefreshDatabase;

    /**
     * @var User Instancia del usuario administrador utilizada en las pruebas.
     */
    private User $admin;
    /**
     * @var User Instancia del usuario cliente utilizada para pruebas de autorización.
     */
    private User $client;

    /**
     * Configura el entorno de prueba antes de cada test.
     *
     * Crea un usuario administrador y un usuario cliente utilizando factories.
     * Estas instancias se almacenan en propiedades de la clase para ser
     * utilizadas en los diferentes métodos de prueba. Llama al método `setUp`
     * de la clase padre.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp(); // Llama al método setUp de la clase padre.
        // Crea un usuario con rol 'administrador' usando la factory y el estado 'admin'.
        $this->admin = User::factory()->admin()->create();
        // Crea un usuario con rol 'cliente'.
        $this->client = User::factory()->create(['rol' => 'cliente']);
    }

    /**
     * Prueba que un administrador puede ver la lista de editoriales.
     *
     * Crea 3 editoriales de prueba usando la factory.
     * Simula una petición GET a la ruta 'admin.editoriales.index' actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'admin.editoriales.index'.
     * Verifica que la vista reciba la variable 'editoriales'.
     * Verifica que el nombre de la primera editorial creada sea visible en la respuesta.
     *
     * @test
     * @return void
     */
    public function admin_can_view_editorial_list(): void
    {
        // Arrange: Crear editoriales de prueba.
        Editoriales::factory()->count(3)->create();
        // Act: Realizar la petición como administrador.
        $response = $this->actingAs($this->admin)->get(route('admin.editoriales.index'));
        // Assert: Verificar la respuesta y el contenido.
        $response->assertStatus(200);
        $response->assertViewIs('admin.editoriales.index');
        $response->assertViewHas('editoriales');
        $response->assertSee(Editoriales::first()->nombre);
    }

    /**
     * Prueba que un cliente no puede ver la lista de editoriales del admin.
     *
     * Simula una petición GET a la ruta 'admin.editoriales.index' actuando como cliente.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.entry'.
     * Verifica que la sesión contenga un mensaje de error específico de acceso no autorizado.
     *
     * @test
     * @return void
     */
    public function client_cannot_view_editorial_list(): void
    {
        // Act: Realizar la petición como cliente.
        $response = $this->actingAs($this->client)->get(route('admin.editoriales.index'));
        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('profile.entry')); // Redirección esperada según EditorialesController@index.
        $response->assertSessionHas('error', 'Acceso no autorizado.');
    }

    /**
     * Prueba que un administrador puede ver el formulario de creación de editoriales.
     *
     * Simula una petición GET a la ruta 'admin.editoriales.create' actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'admin.editoriales.create'.
     *
     * @test
     * @return void
     */
    public function admin_can_view_create_editorial_form(): void
    {
        // Act: Realizar la petición como administrador.
        $response = $this->actingAs($this->admin)->get(route('admin.editoriales.create'));
        // Assert: Verificar la respuesta y la vista.
        $response->assertStatus(200);
        $response->assertViewIs('admin.editoriales.create');
    }

    /**
     * Prueba que un administrador puede crear una nueva editorial.
     *
     * Define los datos para una nueva editorial.
     * Simula una petición POST a la ruta 'admin.editoriales.store' actuando como administrador,
     * enviando los datos de la nueva editorial.
     * Verifica que la respuesta sea una redirección a la ruta 'admin.editoriales.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que la editorial exista en la base de datos con los datos proporcionados.
     *
     * @test
     * @return void
     */
    public function admin_can_store_new_editorial(): void
    {
        // Arrange: Definir datos de la nueva editorial.
        $editorialData = ['nombre' => 'Editorial Nueva Test', 'pais' => 'País Editorial'];
        // Act: Realizar la petición POST como administrador.
        $response = $this->actingAs($this->admin)->post(route('admin.editoriales.store'), $editorialData);

        // Assert: Verificar la redirección, mensaje de éxito y estado de la BD.
        $response->assertRedirect(route('admin.editoriales.index'));
        $response->assertSessionHas('success', 'Editorial creada correctamente.');
        $this->assertDatabaseHas('editoriales', $editorialData);
    }

    /**
     * Prueba que la creación de una editorial falla si los datos son inválidos.
     *
     * Simula una petición POST a la ruta 'admin.editoriales.store' actuando como administrador,
     * enviando datos inválidos (nombre vacío).
     * Verifica que la sesión contenga errores de validación para los campos 'nombre' y 'pais'.
     *
     * @test
     * @return void
     */
    public function store_editorial_fails_with_invalid_data(): void
    {
        // Act: Realizar la petición POST con datos inválidos.
        $response = $this->actingAs($this->admin)->post(route('admin.editoriales.store'), ['nombre' => '']);
        // Assert: Verificar errores de validación en la sesión.
        $response->assertSessionHasErrors(['nombre', 'pais']);
    }

     /**
      * Prueba que la creación de una editorial falla si el nombre ya existe.
      *
      * Crea una editorial existente con un nombre específico.
      * Simula una petición POST a la ruta 'admin.editoriales.store' actuando como administrador,
      * intentando crear otra editorial con el mismo nombre.
      * Verifica que la sesión contenga un error de validación para el campo 'nombre'.
      *
      * @test
      * @return void
      */
    public function store_editorial_fails_with_duplicate_name(): void
    {
        // Arrange: Crear una editorial existente.
        $existing = Editoriales::factory()->create(['nombre' => 'Editorial Duplicada']);
        // Act: Intentar crear otra editorial con el mismo nombre.
        $response = $this->actingAs($this->admin)->post(route('admin.editoriales.store'), [
            'nombre' => 'Editorial Duplicada',
            'pais' => 'Otro Pais'
        ]);
        // Assert: Verificar error de validación para 'nombre'.
        $response->assertSessionHasErrors('nombre');
    }


    /**
     * Prueba que un administrador puede ver la página de detalles de una editorial.
     *
     * Crea una editorial de prueba usando la factory.
     * Simula una petición GET a la ruta 'admin.editoriales.show' para esa editorial, actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'admin.editoriales.show'.
     * Verifica que la vista reciba la variable 'editoriales' con la instancia de la editorial correcta.
     * Verifica que el nombre de la editorial sea visible en la respuesta.
     *
     * @test
     * @return void
     */
    public function admin_can_view_show_editorial_page(): void
    {
        // Arrange: Crear una editorial.
        $editorial = Editoriales::factory()->create();
        // Act: Realizar la petición GET como administrador.
        $response = $this->actingAs($this->admin)->get(route('admin.editoriales.show', $editorial));
        // Assert: Verificar la respuesta, vista, datos y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('admin.editoriales.show');
        $response->assertViewHas('editoriales', $editorial);
        $response->assertSee($editorial->nombre);
    }

    /**
     * Prueba que un administrador puede ver el formulario de edición de una editorial.
     *
     * Crea una editorial de prueba usando la factory.
     * Simula una petición GET a la ruta 'admin.editoriales.edit' para esa editorial, actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'admin.editoriales.edit'.
     * Verifica que la vista reciba la variable 'editoriales' con la instancia de la editorial correcta.
     * Verifica que el nombre de la editorial sea visible en la respuesta (en el formulario).
     *
     * @test
     * @return void
     */
    public function admin_can_view_edit_editorial_form(): void
    {
        // Arrange: Crear una editorial.
        $editorial = Editoriales::factory()->create();
        // Act: Realizar la petición GET como administrador.
        $response = $this->actingAs($this->admin)->get(route('admin.editoriales.edit', $editorial));
        // Assert: Verificar la respuesta, vista, datos y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('admin.editoriales.edit');
        $response->assertViewHas('editoriales', $editorial);
        $response->assertSee($editorial->nombre);
    }

    /**
     * Prueba que un administrador puede actualizar una editorial existente.
     *
     * Crea una editorial de prueba.
     * Define los nuevos datos para la actualización.
     * Simula una petición PUT a la ruta 'admin.editoriales.update' para esa editorial, actuando como administrador
     * y enviando los datos de actualización.
     * Verifica que la respuesta sea una redirección a la ruta 'admin.editoriales.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que la editorial exista en la base de datos con los datos actualizados.
     *
     * @test
     * @return void
     */
    public function admin_can_update_editorial(): void
    {
        // Arrange: Crear una editorial y definir datos de actualización.
        $editorial = Editoriales::factory()->create();
        $updateData = ['nombre' => 'Nombre Actualizado', 'pais' => 'Pais Actualizado'];
        // Act: Realizar la petición PUT como administrador.
        $response = $this->actingAs($this->admin)->put(route('admin.editoriales.update', $editorial), $updateData);

        // Assert: Verificar la redirección, mensaje de éxito y estado de la BD.
        $response->assertRedirect(route('admin.editoriales.index'));
        $response->assertSessionHas('success', 'Editorial actualizada correctamente.');
        $this->assertDatabaseHas('editoriales', ['id' => $editorial->id] + $updateData);
    }

    /**
     * Prueba que la actualización de una editorial falla si se intenta usar un nombre duplicado
     * (perteneciente a otra editorial), pero permite guardar con el nombre original.
     *
     * Crea dos editoriales ('editorial1' y 'editorial2').
     * Intenta actualizar 'editorial2' con el nombre de 'editorial1'. Verifica que la sesión
     * contenga un error de validación para el campo 'nombre'.
     * Intenta actualizar 'editorial2' con su propio nombre original y un país diferente.
     * Verifica que la sesión no contenga errores para 'nombre' y que la respuesta
     * sea una redirección al índice de editoriales.
     *
     * @test
     * @return void
     */
    public function update_editorial_fails_with_duplicate_name_ignoring_self(): void
    {
        // Arrange: Crear dos editoriales.
        $editorial1 = Editoriales::factory()->create(['nombre' => 'Nombre Uno']);
        $editorial2 = Editoriales::factory()->create(['nombre' => 'Nombre Dos']);

        // Act & Assert 1: Intentar actualizar editorial2 con el nombre de editorial1 (debe fallar).
        $response = $this->actingAs($this->admin)->put(route('admin.editoriales.update', $editorial2), [
            'nombre' => 'Nombre Uno',
            'pais' => $editorial2->pais,
        ]);
        $response->assertSessionHasErrors('nombre');

        // Act & Assert 2: Intentar actualizar editorial2 con su propio nombre (debe funcionar).
         $response = $this->actingAs($this->admin)->put(route('admin.editoriales.update', $editorial2), [
            'nombre' => 'Nombre Dos',
            'pais' => 'Pais Nuevo',
        ]);
        $response->assertSessionDoesntHaveErrors('nombre'); // No debe haber error de nombre.
        $response->assertRedirect(route('admin.editoriales.index')); // Debe redirigir si la actualización es válida.
    }


    /**
     * Prueba que un administrador puede eliminar una editorial que no tiene libros asociados.
     *
     * Crea una editorial de prueba sin libros asociados.
     * Simula una petición DELETE a la ruta 'admin.editoriales.destroy' para esa editorial, actuando como administrador.
     * Verifica que la respuesta sea una redirección a la ruta 'admin.editoriales.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que la editorial ya no exista en la base de datos.
     *
     * @test
     * @return void
     */
    public function admin_can_delete_editorial_without_books(): void
    {
        // Arrange: Crear una editorial sin libros.
        $editorial = Editoriales::factory()->create();
        // Act: Realizar la petición DELETE como administrador.
        $response = $this->actingAs($this->admin)->delete(route('admin.editoriales.destroy', $editorial));

        // Assert: Verificar la redirección, mensaje de éxito y estado de la BD.
        $response->assertRedirect(route('admin.editoriales.index'));
        $response->assertSessionHas('success', 'Editorial eliminada correctamente.');
        $this->assertDatabaseMissing('editoriales', ['id' => $editorial->id]);
    }

    /**
     * Prueba que un administrador no puede eliminar una editorial que tiene libros asociados.
     *
     * Crea una editorial y luego un libro asociado a esa editorial.
     * Simula una petición DELETE a la ruta 'admin.editoriales.destroy' para esa editorial, actuando como administrador.
     * Verifica que la respuesta sea una redirección a la ruta 'admin.editoriales.index'.
     * Verifica que la sesión contenga un mensaje de error específico indicando la restricción.
     * Verifica que la editorial todavía exista en la base de datos (no fue eliminada).
     *
     * @test
     * @return void
     */
    public function admin_cannot_delete_editorial_with_books(): void
    {
        // Arrange: Crear una editorial y un libro asociado.
        $editorial = Editoriales::factory()->create();
        Libros::factory()->create(['editorial_id' => $editorial->id]); // Libro asociado.

        // Act: Realizar la petición DELETE como administrador.
        $response = $this->actingAs($this->admin)->delete(route('admin.editoriales.destroy', $editorial));

        // Assert: Verificar la redirección, mensaje de error y estado de la BD.
        $response->assertRedirect(route('admin.editoriales.index'));
        $response->assertSessionHas('error', 'No se puede eliminar la editorial porque tiene libros asociados.');
        $this->assertDatabaseHas('editoriales', ['id' => $editorial->id]); // Verifica que la editorial no se borró.
    }
}
