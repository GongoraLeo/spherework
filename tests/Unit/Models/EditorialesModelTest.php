<?php

// tests/Unit/Models/EditorialesModelTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Editoriales;
use App\Models\Libros;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EditorialesModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function editorial_has_many_libros()
    {
        $editorial = Editoriales::factory()->create();
        Libros::factory()->count(2)->create(['editorial_id' => $editorial->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $editorial->libros);
        $this->assertCount(2, $editorial->libros);
        $this->assertInstanceOf(Libros::class, $editorial->libros->first());
    }

    /** @test */
    public function editorial_fillable_attributes_are_correct()
    {
        $fillable = (new Editoriales())->getFillable();
        $expected = ['nombre', 'pais'];
        $this->assertEquals($expected, $fillable);
    }
}
