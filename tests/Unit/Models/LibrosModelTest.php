<?php

// tests/Unit/Models/LibrosModelTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Libros;
use App\Models\Autores;
use App\Models\Editoriales;
use App\Models\Comentarios;
use App\Models\Detallespedidos;
use App\Models\User; // Necesario para Comentarios
use App\Models\Pedidos; // Necesario para Detallespedidos
use Illuminate\Foundation\Testing\RefreshDatabase;

class LibrosModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function libro_belongs_to_autor()
    {
        $autor = Autores::factory()->create();
        $libro = Libros::factory()->create(['autor_id' => $autor->id]);

        $this->assertInstanceOf(Autores::class, $libro->autor);
        $this->assertEquals($autor->id, $libro->autor->id);
    }

    /** @test */
    public function libro_belongs_to_editorial()
    {
        $editorial = Editoriales::factory()->create();
        $libro = Libros::factory()->create(['editorial_id' => $editorial->id]);

        $this->assertInstanceOf(Editoriales::class, $libro->editorial);
        $this->assertEquals($editorial->id, $libro->editorial->id);
    }

    /** @test */
    public function libro_has_many_comentarios()
    {
        $libro = Libros::factory()->create();
        $user = User::factory()->create(); // Usuario necesario para el comentario
        Comentarios::factory()->count(3)->create([
            'libro_id' => $libro->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $libro->comentarios);
        $this->assertCount(3, $libro->comentarios);
        $this->assertInstanceOf(Comentarios::class, $libro->comentarios->first());
    }

    /** @test */
    public function libro_has_many_detallespedidos()
    {
        $libro = Libros::factory()->create();
        $pedido = Pedidos::factory()->create(); // Pedido necesario para el detalle
        Detallespedidos::factory()->count(2)->create([
            'libro_id' => $libro->id,
            'pedido_id' => $pedido->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $libro->detallespedidos);
        $this->assertCount(2, $libro->detallespedidos);
        $this->assertInstanceOf(Detallespedidos::class, $libro->detallespedidos->first());
    }

    /** @test */
    public function libro_fillable_attributes_are_correct()
    {
        $fillable = (new Libros())->getFillable();
        $expected = ['titulo', 'isbn', 'anio_publicacion', 'autor_id', 'editorial_id', 'precio'];
        $this->assertEquals($expected, $fillable);
    }
}
