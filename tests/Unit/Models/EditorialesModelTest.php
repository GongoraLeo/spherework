<?php
// filepath: tests\Unit\Models\EditorialesModelTest.php

namespace Tests\Unit\Models; // Define el namespace para las pruebas unitarias de modelos.

use Tests\TestCase; // Importa la clase base para todas las pruebas en Laravel.
use App\Models\Editoriales; // Importa el modelo Editoriales que se va a probar.
use App\Models\Libros; // Importa el modelo Libros para probar la relación.
use Illuminate\Foundation\Testing\RefreshDatabase; // Importa el trait para resetear la BD entre pruebas.

/**
 * Class EditorialesModelTest
 *
 * Suite de pruebas unitarias para el modelo `Editoriales`.
 * Verifica las relaciones Eloquent (`hasMany` con Libros) y las propiedades
 * configuradas en el modelo, como los atributos `$fillable`.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba, facilitando el uso de factories.
 *
 * @package Tests\Unit\Models
 */
class EditorialesModelTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase.
     * Esto es útil al usar factories para crear instancias de modelos
     * y probar relaciones que podrían requerir registros en la BD.
     */
    use RefreshDatabase;

    /**
     * Prueba la relación "hasMany" entre Editorial y Libros.
     *
     * Verifica que una editorial puede tener muchos libros asociados.
     * 1. Crea una instancia de `Editoriales` usando su factory.
     * 2. Crea 2 instancias de `Libros` asociadas a esa editorial (estableciendo `editorial_id`).
     * 3. Comprueba que al acceder a la relación `$editorial->libros`, se obtiene una instancia de `Illuminate\Database\Eloquent\Collection`.
     * 4. Comprueba que la colección contiene exactamente 2 elementos.
     * 5. Comprueba que el primer elemento de la colección es una instancia de la clase `Libros`.
     *
     * @test
     * @return void
     */
    public function editorial_has_many_libros(): void
    {
        // Arrange: Crear una editorial y libros asociados.
        $editorial = Editoriales::factory()->create();
        Libros::factory()->count(2)->create(['editorial_id' => $editorial->id]);

        // Assert: Verificar la relación 'libros'.
        // Verifica que la relación devuelve una colección Eloquent.
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $editorial->libros);
        // Verifica que la colección contiene el número esperado de libros.
        $this->assertCount(2, $editorial->libros);
        // Verifica que los elementos de la colección son instancias del modelo Libros.
        $this->assertInstanceOf(Libros::class, $editorial->libros->first());
    }

    /**
     * Prueba que los atributos `$fillable` del modelo Editorial son correctos.
     *
     * Verifica que la propiedad `$fillable` del modelo `Editoriales` contenga
     * exactamente los campos 'nombre' y 'pais'. Esto es importante para
     * la asignación masiva segura.
     * 1. Obtiene el array `$fillable` de una nueva instancia del modelo `Editoriales`.
     * 2. Define el array esperado de atributos fillable.
     * 3. Compara el array obtenido con el esperado usando `assertEquals`.
     *
     * @test
     * @return void
     */
    public function editorial_fillable_attributes_are_correct(): void
    {
        // Arrange: Obtener los atributos fillable del modelo.
        $fillable = (new Editoriales())->getFillable();
        // Arrange: Definir los atributos fillable esperados.
        $expected = ['nombre', 'pais'];
        // Assert: Comparar los atributos fillable actuales con los esperados.
        $this->assertEquals($expected, $fillable);
    }
}
