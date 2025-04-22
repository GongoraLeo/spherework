<?php
// filepath: tests\Unit\Models\AutoresModelTest.php

namespace Tests\Unit\Models; // Define el namespace para las pruebas unitarias de modelos.

use Tests\TestCase; // Importa la clase base para todas las pruebas en Laravel.
use App\Models\Autores; // Importa el modelo Autores que se va a probar.
use App\Models\Libros; // Importa el modelo Libros para probar la relación.
use Illuminate\Foundation\Testing\RefreshDatabase; // Importa el trait para resetear la BD entre pruebas.

/**
 * Class AutoresModelTest
 *
 * Suite de pruebas unitarias para el modelo `Autores`.
 * Verifica las relaciones Eloquent y las propiedades configuradas en el modelo.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba, aunque las pruebas unitarias de modelos no siempre
 * interactúan directamente con la BD de la misma forma que las Feature tests.
 *
 * @package Tests\Unit\Models
 */
class AutoresModelTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase.
     * Esto es útil cuando se usan factories para crear instancias de modelos
     * y probar relaciones que podrían requerir registros en la BD.
     */
    use RefreshDatabase;

    /**
     * Prueba la relación "hasMany" entre Autor y Libros.
     *
     * Verifica que un autor puede tener muchos libros asociados.
     * 1. Crea una instancia de `Autores` usando su factory.
     * 2. Crea 3 instancias de `Libros` asociadas a ese autor (estableciendo `autor_id`).
     * 3. Comprueba que al acceder a la relación `$autor->libros`, se obtiene una instancia de `Illuminate\Database\Eloquent\Collection`.
     * 4. Comprueba que la colección contiene exactamente 3 elementos.
     * 5. Comprueba que el primer elemento de la colección es una instancia de la clase `Libros`.
     *
     * @test
     * @return void
     */
    public function autor_has_many_libros(): void
    {
        // Arrange: Crear un autor y libros asociados.
        $autor = Autores::factory()->create();
        Libros::factory()->count(3)->create(['autor_id' => $autor->id]);

        // Assert: Verificar la relación 'libros'.
        // Verifica que la relación devuelve una colección Eloquent.
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $autor->libros);
        // Verifica que la colección contiene el número esperado de libros.
        $this->assertCount(3, $autor->libros);
        // Verifica que los elementos de la colección son instancias del modelo Libros.
        $this->assertInstanceOf(Libros::class, $autor->libros->first());
    }

    /**
     * Prueba que los atributos `$fillable` del modelo Autor son correctos.
     *
     * Verifica que la propiedad `$fillable` del modelo `Autores` contenga
     * exactamente los campos 'nombre' y 'pais'. Esto es importante para
     * la asignación masiva segura.
     * 1. Obtiene el array `$fillable` de una nueva instancia del modelo `Autores`.
     * 2. Define el array esperado de atributos fillable.
     * 3. Compara el array obtenido con el esperado usando `assertEquals`.
     *
     * @test
     * @return void
     */
    public function autor_fillable_attributes_are_correct(): void
    {
        // Arrange: Obtener los atributos fillable del modelo.
        $fillable = (new Autores())->getFillable();
        // Arrange: Definir los atributos fillable esperados.
        $expected = ['nombre', 'pais'];
        // Assert: Comparar los atributos fillable actuales con los esperados.
        $this->assertEquals($expected, $fillable);
    }
}
