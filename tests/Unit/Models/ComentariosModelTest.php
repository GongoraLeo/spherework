<?php

// tests/Unit/Models/ComentariosModelTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Comentarios;
use App\Models\User;
use App\Models\Libros;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComentariosModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function comentario_belongs_to_user()
    {
        $user = User::factory()->create();
        $comentario = Comentarios::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $comentario->user);
        $this->assertEquals($user->id, $comentario->user->id);
    }

    /** @test */
    public function comentario_belongs_to_libro()
    {
        $libro = Libros::factory()->create();
        $comentario = Comentarios::factory()->create(['libro_id' => $libro->id]);

        $this->assertInstanceOf(Libros::class, $comentario->libro);
        $this->assertEquals($libro->id, $comentario->libro->id);
    }

    /** @test */
    public function comentario_fillable_attributes_are_correct()
    {
        $fillable = (new Comentarios())->getFillable();
        $expected = ['user_id', 'libro_id', 'comentario', 'puntuacion'];
        $this->assertEquals($expected, $fillable);
    }
}
