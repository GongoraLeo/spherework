<?php

namespace Tests\Feature\Books;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Libros;
use App\Models\Autores;
use App\Models\Editoriales;
use PHPUnit\Framework\Attributes\Test;

class PublicBookRoutesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function book_index_page_loads_and_shows_books(): void
    {
        // Crear algunos libros para mostrar
        $author = Autores::factory()->create(['nombre' => 'Autor Prueba']);
        $publisher = Editoriales::factory()->create(['nombre' => 'Editorial Prueba']);
        $book1 = Libros::factory()->create([
            'titulo' => 'Libro de Prueba Uno',
            'autor_id' => $author->id,
            'editorial_id' => $publisher->id,
        ]);
        $book2 = Libros::factory()->create(['titulo' => 'Libro de Prueba Dos']);

        $response = $this->get(route('libros.index'));

        $response->assertStatus(200);
        $response->assertSee('Libro de Prueba Uno');
        $response->assertSee('Libro de Prueba Dos');
        $response->assertSee('Autor Prueba'); // Verifica que carga relación autor
        $response->assertSee('Editorial Prueba'); // Verifica que carga relación editorial
        $response->assertViewHas('libros'); // Verifica que la variable se pasa a la vista
    }

    #[Test]
    public function book_show_page_loads_and_shows_book_details(): void
    {
        // Crear un libro específico con detalles conocidos
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

        $response = $this->get(route('libros.show', $libro)); // Usa el objeto libro

        $response->assertStatus(200);
        $response->assertSee('Libro de Prueba Detalle');
        $response->assertSee('9876543210987');
        $response->assertSee('2022');
        // CORREGIDO: Ajustar al formato de la vista (coma y símbolo €)
        // $response->assertSee('25.50'); // ANTES
        $response->assertSee('25,50 €'); // DESPUÉS
        $response->assertSee('Autor Detalle');
        $response->assertSee('Editorial Detalle');
        $response->assertViewHas('libros', $libro);
    }

    #[Test]
    public function book_show_route_returns_404_for_invalid_id(): void
    {
        $response = $this->get(route('libros.show', 9999)); // ID que no existe
        $response->assertStatus(404);
    }
}
