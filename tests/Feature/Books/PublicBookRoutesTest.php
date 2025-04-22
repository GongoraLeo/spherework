<?php
// filepath: tests\Feature\Books\PublicBookRoutesTest.php

namespace Tests\Feature\Books;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\Libros; // Modelo Libros para crear libros de prueba.
use App\Models\Autores; // Modelo Autores para asociar a libros.
use App\Models\Editoriales; // Modelo Editoriales para asociar a libros.
use PHPUnit\Framework\Attributes\Test; // Atributo para marcar métodos como tests (PHPUnit 10+).

/**
 * Class PublicBookRoutesTest
 *
 * Suite de pruebas de Feature para verificar el funcionamiento de las rutas públicas
 * relacionadas con los libros. Comprueba que la página de índice (catálogo) y la
 * página de detalles de un libro se carguen correctamente y muestren la información
 * esperada para cualquier visitante (no autenticado). También verifica el
 * comportamiento cuando se intenta acceder a un libro con un ID inválido.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba.
 *
 * @package Tests\Feature\Books
 */
class PublicBookRoutesTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase,
     * asegurando que los tests no interfieran entre sí.
     */
    use RefreshDatabase;

    /**
     * Prueba que la página de índice de libros se carga y muestra los libros.
     *
     * Crea un autor, una editorial y dos libros de prueba, asociando el primero
     * al autor y editorial creados.
     * Simula una petición GET a la ruta 'libros.index' (pública).
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que los títulos de ambos libros sean visibles en la respuesta.
     * Verifica que los nombres del autor y la editorial asociados al primer libro
     * sean visibles (confirmando la carga de relaciones).
     * Verifica que la vista reciba la variable 'libros'.
     *
     * @return void
     */
    #[Test]
    public function book_index_page_loads_and_shows_books(): void
    {
        // Arrange: Crear datos de prueba (autor, editorial, libros).
        $author = Autores::factory()->create(['nombre' => 'Autor Prueba']);
        $publisher = Editoriales::factory()->create(['nombre' => 'Editorial Prueba']);
        $book1 = Libros::factory()->create([
            'titulo' => 'Libro de Prueba Uno',
            'autor_id' => $author->id,
            'editorial_id' => $publisher->id,
        ]);
        $book2 = Libros::factory()->create(['titulo' => 'Libro de Prueba Dos']);

        // Act: Realizar la petición GET a la ruta de índice de libros.
        $response = $this->get(route('libros.index'));

        // Assert: Verificar la respuesta y el contenido.
        $response->assertStatus(200);
        $response->assertSee('Libro de Prueba Uno');
        $response->assertSee('Libro de Prueba Dos');
        $response->assertSee('Autor Prueba'); // Verifica carga de relación autor.
        $response->assertSee('Editorial Prueba'); // Verifica carga de relación editorial.
        $response->assertViewHas('libros'); // Verifica que la variable se pasa a la vista.
    }

    /**
     * Prueba que la página de detalles de un libro se carga y muestra la información correcta.
     *
     * Crea un autor, una editorial y un libro específico con detalles conocidos.
     * Simula una petición GET a la ruta 'libros.show' pasando la instancia del libro creado.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que el título, ISBN, año de publicación, precio (formateado),
     * nombre del autor y nombre de la editorial sean visibles en la respuesta.
     * Verifica que la vista reciba la variable 'libros' con la instancia del libro correcto.
     *
     * @return void
     */
    #[Test]
    public function book_show_page_loads_and_shows_book_details(): void
    {
        // Arrange: Crear datos de prueba específicos.
        $author = Autores::factory()->create(['nombre' => 'Autor Detalle']);
        $publisher = Editoriales::factory()->create(['nombre' => 'Editorial Detalle']);
        $libro = Libros::factory()->create([
            'titulo' => 'Libro de Prueba Detalle',
            'isbn' => '9876543210987',
            'anio_publicacion' => 2022,
            'precio' => 25.50,
            'autor_id' => $author->id,
            'editorial_id' => $publisher->id,
        ]);

        // Act: Realizar la petición GET a la ruta de detalles del libro.
        $response = $this->get(route('libros.show', $libro)); // Usa el objeto libro.

        // Assert: Verificar la respuesta y el contenido.
        $response->assertStatus(200);
        $response->assertSee('Libro de Prueba Detalle');
        $response->assertSee('9876543210987');
        $response->assertSee('2022');
        $response->assertSee('25,50 €'); // Verifica el precio formateado.
        $response->assertSee('Autor Detalle');
        $response->assertSee('Editorial Detalle');
        $response->assertViewHas('libros', $libro); // Verifica que se pasa el libro correcto a la vista.
    }

    /**
     * Prueba que la ruta de detalles de libro devuelve un 404 si se usa un ID inválido.
     *
     * Simula una petición GET a la ruta 'libros.show' utilizando un ID numérico (9999)
     * que se asume no existe en la base de datos.
     * Verifica que la respuesta HTTP tenga estado 404 (Not Found).
     *
     * @return void
     */
    #[Test]
    public function book_show_route_returns_404_for_invalid_id(): void
    {
        // Act: Realizar la petición GET con un ID inexistente.
        $response = $this->get(route('libros.show', 9999)); // ID que no existe.
        // Assert: Verificar que la respuesta sea 404.
        $response->assertStatus(404);
    }
}
