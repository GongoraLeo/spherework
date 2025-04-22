<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\User; // Modelo User para crear usuarios de prueba.
use App\Models\Autores; // Modelo Autores para crear y verificar autores.
use App\Models\Libros; // Modelo Libros para probar la restricción de borrado.

/**
 * Class AuthorManagementTest
 *
 * Suite de pruebas de Feature para verificar la gestión completa (CRUD)
 * del recurso 'Autores' desde la perspectiva de un administrador.
 * Incluye pruebas de visualización, creación, almacenamiento, edición,
 * actualización y eliminación de autores, así como las restricciones
 * de acceso para usuarios no administradores (clientes) y la lógica
 * de negocio (ej. no poder eliminar autores con libros asociados).
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba. Pertenece al grupo de pruebas 'admin'.
 *
 * @group admin
 * @package Tests\Feature\Admin
 */
class AuthorManagementTest extends TestCase
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
     * Prueba que un administrador puede ver la lista de autores.
     *
     * Crea 3 autores de prueba usando la factory.
     * Simula una petición GET a la ruta 'admin.autores.index' actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'admin.autores.index'.
     * Verifica que la vista reciba la variable 'autores'.
     * Verifica que el nombre del primer autor creado sea visible en la respuesta.
     *
     * @test
     * @return void
     */
    public function admin_can_view_author_list(): void
    {
        // Arrange: Crear autores de prueba.
        Autores::factory()->count(3)->create();
        // Act: Realizar la petición como administrador.
        $response = $this->actingAs($this->admin)->get(route('admin.autores.index'));
        // Assert: Verificar la respuesta y el contenido.
        $response->assertStatus(200);
        $response->assertViewIs('admin.autores.index');
        $response->assertViewHas('autores');
        $response->assertSee(Autores::first()->nombre);
    }

    /**
     * Prueba que un cliente no puede ver la lista de autores del admin.
     *
     * Simula una petición GET a la ruta 'admin.autores.index' actuando como cliente.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.entry'.
     * Verifica que la sesión contenga un mensaje de error específico de acceso no autorizado.
     *
     * @test
     * @return void
     */
    public function client_cannot_view_author_list(): void
    {
        // Act: Realizar la petición como cliente.
        $response = $this->actingAs($this->client)->get(route('admin.autores.index'));
        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('profile.entry')); // Redirección esperada según AutoresController@index.
        $response->assertSessionHas('error', 'Acceso no autorizado.');
    }

    /**
     * Prueba que un administrador puede ver el formulario de creación de autores.
     *
     * Simula una petición GET a la ruta 'admin.autores.create' actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'admin.autores.create'.
     *
     * @test
     * @return void
     */
    public function admin_can_view_create_author_form(): void
    {
        // Act: Realizar la petición como administrador.
        $response = $this->actingAs($this->admin)->get(route('admin.autores.create'));
        // Assert: Verificar la respuesta y la vista.
        $response->assertStatus(200);
        $response->assertViewIs('admin.autores.create');
    }

    /**
     * Prueba que un administrador puede crear un nuevo autor.
     *
     * Define los datos para un nuevo autor.
     * Simula una petición POST a la ruta 'admin.autores.store' actuando como administrador,
     * enviando los datos del nuevo autor.
     * Verifica que la respuesta sea una redirección a la ruta 'admin.autores.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el autor exista en la base de datos con los datos proporcionados.
     *
     * @test
     * @return void
     */
    public function admin_can_store_new_author(): void
    {
        // Arrange: Definir datos del nuevo autor.
        $authorData = ['nombre' => 'Autor Nuevo Test', 'pais' => 'País Inventado'];
        // Act: Realizar la petición POST como administrador.
        $response = $this->actingAs($this->admin)->post(route('admin.autores.store'), $authorData);

        // Assert: Verificar la redirección, mensaje de éxito y estado de la BD.
        $response->assertRedirect(route('admin.autores.index'));
        $response->assertSessionHas('success', 'Autor creado correctamente.');
        $this->assertDatabaseHas('autores', $authorData);
    }

    /**
     * Prueba que la creación de un autor falla si los datos son inválidos.
     *
     * Simula una petición POST a la ruta 'admin.autores.store' actuando como administrador,
     * enviando datos inválidos (nombre vacío).
     * Verifica que la sesión contenga errores de validación para los campos 'nombre' y 'pais'.
     *
     * @test
     * @return void
     */
    public function store_author_fails_with_invalid_data(): void
    {
        // Act: Realizar la petición POST con datos inválidos.
        $response = $this->actingAs($this->admin)->post(route('admin.autores.store'), ['nombre' => '']);
        // Assert: Verificar errores de validación en la sesión.
        $response->assertSessionHasErrors(['nombre', 'pais']);
    }

    /**
     * Prueba que la creación de un autor falla si el nombre ya existe.
     *
     * Crea un autor existente con un nombre específico.
     * Simula una petición POST a la ruta 'admin.autores.store' actuando como administrador,
     * intentando crear otro autor con el mismo nombre.
     * Verifica que la sesión contenga un error de validación para el campo 'nombre'.
     *
     * @test
     * @return void
     */
    public function store_author_fails_with_duplicate_name(): void
    {
        // Arrange: Crear un autor existente.
        $existing = Autores::factory()->create(['nombre' => 'Autor Duplicado']);
        // Act: Intentar crear otro autor con el mismo nombre.
        $response = $this->actingAs($this->admin)->post(route('admin.autores.store'), [
            'nombre' => 'Autor Duplicado',
            'pais' => 'Otro Pais'
        ]);
        // Assert: Verificar error de validación para 'nombre'.
        $response->assertSessionHasErrors('nombre');
    }

    /**
     * Prueba que un administrador puede ver la página de detalles de un autor.
     *
     * Crea un autor de prueba usando la factory.
     * Simula una petición GET a la ruta 'admin.autores.show' para ese autor, actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'admin.autores.show'.
     * Verifica que la vista reciba la variable 'autores' con la instancia del autor correcto.
     * Verifica que el nombre del autor sea visible en la respuesta.
     *
     * @test
     * @return void
     */
    public function admin_can_view_show_author_page(): void
    {
        // Arrange: Crear un autor.
        $autor = Autores::factory()->create();
        // Act: Realizar la petición GET como administrador.
        $response = $this->actingAs($this->admin)->get(route('admin.autores.show', $autor));
        // Assert: Verificar la respuesta, vista, datos y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('admin.autores.show');
        $response->assertViewHas('autores', $autor);
        $response->assertSee($autor->nombre);
    }

    /**
     * Prueba que un administrador puede ver el formulario de edición de un autor.
     *
     * Crea un autor de prueba usando la factory.
     * Simula una petición GET a la ruta 'admin.autores.edit' para ese autor, actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'admin.autores.edit'.
     * Verifica que la vista reciba la variable 'autores' con la instancia del autor correcto.
     * Verifica que el nombre del autor sea visible en la respuesta (en el formulario).
     *
     * @test
     * @return void
     */
    public function admin_can_view_edit_author_form(): void
    {
        // Arrange: Crear un autor.
        $autor = Autores::factory()->create();
        // Act: Realizar la petición GET como administrador.
        $response = $this->actingAs($this->admin)->get(route('admin.autores.edit', $autor));
        // Assert: Verificar la respuesta, vista, datos y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('admin.autores.edit');
        $response->assertViewHas('autores', $autor);
        $response->assertSee($autor->nombre);
    }

    /**
     * Prueba que un administrador puede actualizar un autor existente.
     *
     * Crea un autor de prueba.
     * Define los nuevos datos para la actualización.
     * Simula una petición PUT a la ruta 'admin.autores.update' para ese autor, actuando como administrador
     * y enviando los datos de actualización.
     * Verifica que la respuesta sea una redirección a la ruta 'admin.autores.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el autor exista en la base de datos con los datos actualizados.
     *
     * @test
     * @return void
     */
    public function admin_can_update_author(): void
    {
        // Arrange: Crear un autor y definir datos de actualización.
        $autor = Autores::factory()->create();
        $updateData = ['nombre' => 'Nombre Actualizado', 'pais' => 'Pais Actualizado'];
        // Act: Realizar la petición PUT como administrador.
        $response = $this->actingAs($this->admin)->put(route('admin.autores.update', $autor), $updateData);

        // Assert: Verificar la redirección, mensaje de éxito y estado de la BD.
        $response->assertRedirect(route('admin.autores.index'));
        $response->assertSessionHas('success', 'Autor actualizado correctamente.');
        $this->assertDatabaseHas('autores', ['id' => $autor->id] + $updateData);
    }

    /**
     * Prueba que la actualización de un autor falla si se intenta usar un nombre duplicado
     * (perteneciente a otro autor), pero permite guardar con el nombre original.
     *
     * Crea dos autores ('autor1' y 'autor2').
     * Intenta actualizar 'autor2' con el nombre de 'autor1'. Verifica que la sesión
     * contenga un error de validación para el campo 'nombre'.
     * Intenta actualizar 'autor2' con su propio nombre original y un país diferente.
     * Verifica que la sesión no contenga errores para 'nombre' y que la respuesta
     * sea una redirección al índice de autores.
     *
     * @test
     * @return void
     */
    public function update_author_fails_with_duplicate_name_ignoring_self(): void
    {
        // Arrange: Crear dos autores.
        $autor1 = Autores::factory()->create(['nombre' => 'Nombre Uno']);
        $autor2 = Autores::factory()->create(['nombre' => 'Nombre Dos']);

        // Act & Assert 1: Intentar actualizar autor2 con el nombre de autor1 (debe fallar).
        $response = $this->actingAs($this->admin)->put(route('admin.autores.update', $autor2), [
            'nombre' => 'Nombre Uno',
            'pais' => $autor2->pais,
        ]);
        $response->assertSessionHasErrors('nombre');

        // Act & Assert 2: Intentar actualizar autor2 con su propio nombre (debe funcionar).
         $response = $this->actingAs($this->admin)->put(route('admin.autores.update', $autor2), [
            'nombre' => 'Nombre Dos',
            'pais' => 'Pais Nuevo',
        ]);
        $response->assertSessionDoesntHaveErrors('nombre'); // No debe haber error de nombre.
        $response->assertRedirect(route('admin.autores.index')); // Debe redirigir si la actualización es válida.
    }

    /**
     * Prueba que un administrador puede eliminar un autor que no tiene libros asociados.
     *
     * Crea un autor de prueba sin libros asociados.
     * Simula una petición DELETE a la ruta 'admin.autores.destroy' para ese autor, actuando como administrador.
     * Verifica que la respuesta sea una redirección a la ruta 'admin.autores.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el autor ya no exista en la base de datos.
     *
     * @test
     * @return void
     */
    public function admin_can_delete_author_without_books(): void
    {
        // Arrange: Crear un autor sin libros.
        $autor = Autores::factory()->create();
        // Act: Realizar la petición DELETE como administrador.
        $response = $this->actingAs($this->admin)->delete(route('admin.autores.destroy', $autor));

        // Assert: Verificar la redirección, mensaje de éxito y estado de la BD.
        $response->assertRedirect(route('admin.autores.index'));
        $response->assertSessionHas('success', 'Autor eliminado correctamente.');
        $this->assertDatabaseMissing('autores', ['id' => $autor->id]);
    }

    /**
     * Prueba que un administrador no puede eliminar un autor que tiene libros asociados.
     *
     * Crea un autor y luego un libro asociado a ese autor.
     * Simula una petición DELETE a la ruta 'admin.autores.destroy' para ese autor, actuando como administrador.
     * Verifica que la respuesta sea una redirección a la ruta 'admin.autores.index'.
     * Verifica que la sesión contenga un mensaje de error específico indicando la restricción.
     * Verifica que el autor todavía exista en la base de datos (no fue eliminado).
     *
     * @test
     * @return void
     */
    public function admin_cannot_delete_author_with_books(): void
    {
        // Arrange: Crear un autor y un libro asociado.
        $autor = Autores::factory()->create();
        Libros::factory()->create(['autor_id' => $autor->id]); // Libro asociado.

        // Act: Realizar la petición DELETE como administrador.
        $response = $this->actingAs($this->admin)->delete(route('admin.autores.destroy', $autor));

        // Assert: Verificar la redirección, mensaje de error y estado de la BD.
        $response->assertRedirect(route('admin.autores.index'));
        $response->assertSessionHas('error', 'No se puede eliminar el autor porque tiene libros asociados.');
        $this->assertDatabaseHas('autores', ['id' => $autor->id]); // Verifica que el autor no se borró.
    }
}
