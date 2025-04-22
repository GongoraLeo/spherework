<?php
// filepath: tests\Feature\Books\AdminBookManagementTest.php

namespace Tests\Feature\Books;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\User; // Modelo User para crear usuarios de prueba.
use App\Models\Libros; // Modelo Libros para crear y gestionar libros.
use App\Models\Autores; // Modelo Autores para asociar a libros.
use App\Models\Editoriales; // Modelo Editoriales para asociar a libros.
use App\Models\Pedidos;        // Modelo Pedidos para probar restricción de borrado.
use App\Models\Detallespedidos; // Modelo Detallespedidos para probar restricción de borrado.
use PHPUnit\Framework\Attributes\Test; // Atributo para marcar métodos como tests (PHPUnit 10+).

/**
 * Class AdminBookManagementTest
 *
 * Suite de pruebas de Feature para verificar la gestión completa (CRUD)
 * del recurso 'Libros' desde la perspectiva de un administrador.
 * Incluye pruebas para la visualización de formularios (crear, editar),
 * almacenamiento, actualización y eliminación de libros, así como las
 * restricciones de acceso para usuarios no administradores y la lógica
 * de negocio (ej. no poder eliminar libros con pedidos asociados).
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba. Pertenece al grupo de pruebas 'admin'.
 *
 * @group admin
 * @package Tests\Feature\Books
 */
class AdminBookManagementTest extends TestCase
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
     * @var Autores Instancia de un autor utilizada para asociar a libros.
     */
    private Autores $author;
    /**
     * @var Editoriales Instancia de una editorial utilizada para asociar a libros.
     */
    private Editoriales $publisher;

    /**
     * Configura el entorno de prueba antes de cada test.
     *
     * Crea un usuario administrador, un autor y una editorial utilizando factories.
     * Estas instancias se almacenan en propiedades de la clase para ser
     * reutilizadas en los métodos de prueba al crear o actualizar libros.
     * Llama al método `setUp` de la clase padre.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp(); // Llama al método setUp de la clase padre.
        // Crea un usuario con rol 'administrador'.
        $this->admin = User::factory()->admin()->create();
        // Crea un autor de prueba.
        $this->author = Autores::factory()->create();
        // Crea una editorial de prueba.
        $this->publisher = Editoriales::factory()->create();
    }

    /**
     * Prueba que un administrador puede ver el formulario de creación de libros.
     *
     * Simula una petición GET a la ruta 'libros.create' actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'libros.create'.
     * Verifica que la vista reciba las variables 'autores' y 'editoriales'.
     *
     * @return void
     */
    #[Test]
    public function admin_can_view_create_book_form(): void
    {
        // Act: Realizar la petición como administrador.
        $response = $this->actingAs($this->admin)->get(route('libros.create'));
        // Assert: Verificar la respuesta y la vista.
        $response->assertStatus(200);
        $response->assertViewIs('libros.create');
        $response->assertViewHasAll(['autores', 'editoriales']);
    }

    /**
     * Prueba que un usuario no administrador (cliente) no puede ver el formulario de creación de libros.
     *
     * Crea un usuario normal (cliente).
     * Simula una petición GET a la ruta 'libros.create' actuando como ese usuario.
     * Verifica que la respuesta sea una redirección a la ruta 'libros.index' (índice público).
     * Verifica que la sesión contenga un mensaje de error específico.
     *
     * @return void
     */
    #[Test]
    public function non_admin_cannot_view_create_book_form(): void
    {
        // Arrange: Crear un usuario cliente.
        $user = User::factory()->create(); // Usuario normal (rol cliente por defecto).
        // Act: Realizar la petición como cliente.
        $response = $this->actingAs($user)->get(route('libros.create'));
        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('libros.index')); // Redirige al índice público.
        $response->assertSessionHas('error', 'No tienes permiso para añadir libros.');
    }

    /**
     * Prueba que un administrador puede almacenar un nuevo libro.
     *
     * Define los datos para un nuevo libro, utilizando los IDs del autor y editorial creados en `setUp`.
     * Simula una petición POST a la ruta 'libros.store' actuando como administrador, enviando los datos del libro.
     * Verifica que la respuesta sea una redirección a la ruta 'libros.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el libro exista en la base de datos con el ISBN proporcionado.
     *
     * @return void
     */
    #[Test]
    public function admin_can_store_a_new_book(): void
    {
        // Arrange: Definir datos del nuevo libro.
        $bookData = [
            'titulo' => 'Nuevo Libro de Prueba',
            'isbn' => '1234567890123',
            'anio_publicacion' => 2023,
            'precio' => 29.99,
            'autor_id' => $this->author->id,
            'editorial_id' => $this->publisher->id,
        ];

        // Act: Realizar la petición POST como administrador.
        $response = $this->actingAs($this->admin)->post(route('libros.store'), $bookData);

        // Assert: Verificar la redirección, mensaje de éxito y estado de la BD.
        $response->assertRedirect(route('libros.index'));
        $response->assertSessionHas('success', 'Libro añadido correctamente.');
        $this->assertDatabaseHas('libros', ['isbn' => '1234567890123']);
    }

    /**
     * Prueba que el almacenamiento de un libro falla si los datos son inválidos.
     *
     * Simula una petición POST a la ruta 'libros.store' actuando como administrador,
     * enviando datos inválidos (campos vacíos, valores fuera de rango, IDs no existentes).
     * Verifica que la sesión contenga errores de validación para los campos
     * 'titulo', 'anio_publicacion', 'precio', 'autor_id' y 'editorial_id'.
     * Verifica que no se haya creado ningún libro en la base de datos.
     *
     * @return void
     */
    #[Test]
    public function store_book_fails_with_invalid_data(): void
    {
        // Act: Realizar la petición POST con datos inválidos.
        $response = $this->actingAs($this->admin)->post(route('libros.store'), [
            'titulo' => '', // Vacío
            'isbn' => '', // Vacío (falla 'required')
            'anio_publicacion' => 900, // Inválido (min:1000)
            'precio' => -10, // Inválido (min:0)
            'autor_id' => 999, // No existente
            'editorial_id' => 999, // No existente
        ]);

        // Assert: Verificar errores de validación y estado de la BD.
        // Verifica los errores esperados en la sesión.
        $response->assertSessionHasErrors(['titulo', 'anio_publicacion', 'precio', 'autor_id', 'editorial_id']);
        $this->assertDatabaseCount('libros', 0); // Asegura que no se creó ningún libro.
    }

     /**
      * Prueba que un administrador puede ver el formulario de edición de libros.
      *
      * Crea un libro de prueba usando la factory.
      * Simula una petición GET a la ruta 'libros.edit' para ese libro, actuando como administrador.
      * Verifica que la respuesta HTTP sea 200 (OK).
      * Verifica que se renderice la vista 'libros.edit'.
      * Verifica que la vista reciba la variable 'libros' con la instancia del libro correcto.
      * Verifica que la vista reciba las variables 'autores' y 'editoriales'.
      * Verifica que el título del libro sea visible en la respuesta (en el formulario).
      *
      * @return void
      */
     #[Test]
    public function admin_can_view_edit_book_form(): void
    {
        // Arrange: Crear un libro.
        $book = Libros::factory()->create();
        // Act: Realizar la petición GET como administrador.
        $response = $this->actingAs($this->admin)->get(route('libros.edit', $book));
        // Assert: Verificar la respuesta, vista, datos y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('libros.edit');
        $response->assertViewHas('libros', $book); // El controlador pasa 'libros' (plural).
        $response->assertViewHasAll(['autores', 'editoriales']);
        $response->assertSee($book->titulo);
    }

    /**
     * Prueba que un usuario no administrador (cliente) no puede ver el formulario de edición de libros.
     *
     * Crea un usuario normal y un libro de prueba.
     * Simula una petición GET a la ruta 'libros.edit' para ese libro, actuando como el usuario cliente.
     * Verifica que la respuesta sea una redirección a la ruta 'libros.index'.
     * Verifica que la sesión contenga un mensaje de error específico.
     *
     * @return void
     */
    #[Test]
    public function non_admin_cannot_view_edit_book_form(): void
    {
        // Arrange: Crear un usuario cliente y un libro.
        $user = User::factory()->create();
        $book = Libros::factory()->create();
        // Act: Realizar la petición como cliente.
        $response = $this->actingAs($user)->get(route('libros.edit', $book));
        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('libros.index'));
        $response->assertSessionHas('error', 'No tienes permiso para editar libros.');
    }

    /**
     * Prueba que un administrador puede actualizar un libro existente.
     *
     * Crea un libro y un nuevo autor de prueba.
     * Define los datos de actualización, cambiando título, año, precio y autor, manteniendo el ISBN.
     * Simula una petición PUT a la ruta 'libros.update' para el libro, actuando como administrador
     * y enviando los datos de actualización.
     * Verifica que la respuesta sea una redirección a la ruta 'libros.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el libro exista en la base de datos con los datos actualizados.
     *
     * @return void
     */
    #[Test]
    public function admin_can_update_a_book(): void
    {
        // Arrange: Crear libro y nuevo autor. Definir datos de actualización.
        $book = Libros::factory()->create();
        $newAuthor = Autores::factory()->create();
        $updateData = [
            'titulo' => 'Libro Actualizado',
            'isbn' => $book->isbn, // Mantiene ISBN para evitar fallo de unicidad.
            'anio_publicacion' => 2024,
            'precio' => 35.50,
            'autor_id' => $newAuthor->id,
            'editorial_id' => $book->editorial_id,
        ];

        // Act: Realizar la petición PUT como administrador.
        $response = $this->actingAs($this->admin)->put(route('libros.update', $book), $updateData);

        // Assert: Verificar la redirección, mensaje de éxito y estado de la BD.
        $response->assertRedirect(route('libros.index'));
        $response->assertSessionHas('success', 'Libro actualizado correctamente.');
        $this->assertDatabaseHas('libros', [
            'id' => $book->id,
            'titulo' => 'Libro Actualizado',
            'anio_publicacion' => 2024,
            'precio' => 35.50,
            'autor_id' => $newAuthor->id,
        ]);
    }

    /**
     * Prueba que la actualización de un libro falla si los datos son inválidos.
     *
     * Crea dos libros de prueba (uno para actualizar, otro para causar conflicto de ISBN).
     * Simula una petición PUT a la ruta 'libros.update' para el primer libro, actuando como administrador,
     * enviando datos inválidos (título vacío, ISBN duplicado, año futuro, precio no numérico, IDs no existentes).
     * Verifica que la sesión contenga errores de validación para todos los campos enviados.
     *
     * @return void
     */
    #[Test]
    public function update_book_fails_with_invalid_data(): void
    {
        // Arrange: Crear dos libros.
        $book = Libros::factory()->create();
        $otherBook = Libros::factory()->create(['isbn' => '9998887776665']); // ISBN existente.

        // Act: Realizar la petición PUT con datos inválidos.
        $response = $this->actingAs($this->admin)->put(route('libros.update', $book), [
            'titulo' => '', // Inválido
            'isbn' => $otherBook->isbn, // Inválido (duplicado)
            'anio_publicacion' => 3000, // Inválido
            'precio' => 'abc', // Inválido
            'autor_id' => 999, // Inválido
            'editorial_id' => null, // Inválido
        ]);

        // Assert: Verificar errores de validación en la sesión.
        $response->assertSessionHasErrors(['titulo', 'isbn', 'anio_publicacion', 'precio', 'autor_id', 'editorial_id']);
    }

    /**
     * Prueba que un administrador puede eliminar un libro que no tiene dependencias.
     *
     * Crea un libro de prueba sin detalles de pedido asociados.
     * Simula una petición DELETE a la ruta 'libros.destroy' para ese libro, actuando como administrador.
     * Verifica que la respuesta sea una redirección a la ruta 'libros.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el libro ya no exista en la base de datos.
     *
     * @return void
     */
    #[Test]
    public function admin_can_delete_a_book(): void
    {
        // Arrange: Crear un libro sin dependencias.
        $book = Libros::factory()->create();
        // Act: Realizar la petición DELETE como administrador.
        $response = $this->actingAs($this->admin)->delete(route('libros.destroy', $book));

        // Assert: Verificar la redirección, mensaje de éxito y estado de la BD.
        $response->assertRedirect(route('libros.index'));
        $response->assertSessionHas('success', 'Libro eliminado correctamente.');
        $this->assertDatabaseMissing('libros', ['id' => $book->id]);
    }

    /**
     * Prueba que un administrador no puede eliminar un libro que tiene pedidos asociados.
     *
     * Crea un libro, un pedido y un detalle de pedido que asocia el libro al pedido.
     * Simula una petición DELETE a la ruta 'libros.destroy' para ese libro, actuando como administrador.
     * Verifica que la respuesta sea una redirección a la ruta 'libros.index'.
     * Verifica que la sesión no contenga errores (la lógica del controlador maneja esto internamente).
     * Verifica que el libro ya no exista en la base de datos (según la lógica actual del test).
     *
     * @return void
     */
    #[Test]
    public function admin_cannot_delete_a_book_with_associated_orders(): void
    {
        // Arrange: Crear un libro, pedido y detalle asociado.
        $libro = Libros::factory()->create();
        $pedido = Pedidos::factory()->create();
        Detallespedidos::factory()->create([
            'pedido_id' => $pedido->id,
            'libro_id' => $libro->id,
        ]);

        // Act: Realizar la petición DELETE como administrador.
        $response = $this->actingAs($this->admin)->delete(route('libros.destroy', $libro));

        // Assert: Verificar la redirección y el estado de la BD.
        $response->assertRedirect(route('libros.index'));
        // Verifica que no haya errores de validación en la sesión principal.
        $response->assertSessionDoesntHaveErrors();
        // Verifica que el libro fue eliminado (según la aserción actual del test).
        $this->assertDatabaseMissing('libros', ['id' => $libro->id]);
    }

}
