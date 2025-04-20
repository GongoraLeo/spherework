<?php

// tests/Unit/Models/AutoresModelTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Autores;
use App\Models\Libros;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AutoresModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function autor_has_many_libros()
    {
        $autor = Autores::factory()->create();
        Libros::factory()->count(3)->create(['autor_id' => $autor->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $autor->libros);
        $this->assertCount(3, $autor->libros);
        $this->assertInstanceOf(Libros::class, $autor->libros->first());
    }

    /** @test */
    public function autor_fillable_attributes_are_correct()
    {
        $fillable = (new Autores())->getFillable();
        $expected = ['nombre', 'pais'];
        $this->assertEquals($expected, $fillable);
    }
}
