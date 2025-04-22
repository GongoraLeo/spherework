<?php
// filepath: tests\Unit\Models\LibrosModelTest.php

namespace Tests\Unit\Models; // Define el namespace para las pruebas unitarias de modelos.

use Tests\TestCase; // Importa la clase base para todas las pruebas en Laravel.
use App\Models\Libros; // Importa el modelo Libros que se va a probar.
use App\Models\Autores; // Importa el modelo Autores para probar la relación.
use App\Models\Editoriales; // Importa el modelo Editoriales para probar la relación.
use App\Models\Comentarios; // Importa el modelo Comentarios para probar la relación.
use App\Models\Detallespedidos; // Importa el modelo Detallespedidos para probar la relación.
use App\Models\User; // Importa el modelo User, necesario para crear Comentarios.
use App\Models\Pedidos; // Importa el modelo Pedidos, necesario para crear Detallespedidos.
use Illuminate\Foundation\Testing\RefreshDatabase; // Importa el trait para resetear la BD entre pruebas.

/**
 * Class LibrosModelTest
 *
 * Suite de pruebas unitarias para el modelo `Libros`.
 * Verifica las relaciones Eloquent (`belongsTo` con Autores y Editoriales,
 * `hasMany` con Comentarios y Detallespedidos) y las propiedades configuradas
 * en el modelo, como los atributos `$fillable`.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba, facilitando el uso de factories.
 *
 * @package Tests\Unit\Models
 */
class LibrosModelTest extends TestCase
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
     * Prueba la relación "belongsTo" entre Libro y Autor.
     *
     * Verifica que un libro pertenece a un autor.
     * 1. Crea una instancia de `Autores` usando su factory.
     * 2. Crea una instancia de `Libros` asociada a ese autor (estableciendo `autor_id`).
     * 3. Comprueba que al acceder a la relación `$libro->autor`, se obtiene una instancia de la clase `Autores`.
     * 4. Comprueba que el ID del autor relacionado (`$libro->autor->id`) coincide con el ID del autor original (`$autor->id`).
     *
     * @test
     * @return void
     */
    public function libro_belongs_to_autor(): void
    {
        // Arrange: Crear un autor y un libro asociado.
        $autor = Autores::factory()->create();
        $libro = Libros::factory()->create(['autor_id' => $autor->id]);

        // Assert: Verificar la relación 'autor'.
        // Verifica que la relación devuelve una instancia del modelo Autores.
        $this->assertInstanceOf(Autores::class, $libro->autor);
        // Verifica que el ID del autor relacionado es el correcto.
        $this->assertEquals($autor->id, $libro->autor->id);
    }

    /**
     * Prueba la relación "belongsTo" entre Libro y Editorial.
     *
     * Verifica que un libro pertenece a una editorial.
     * 1. Crea una instancia de `Editoriales` usando su factory.
     * 2. Crea una instancia de `Libros` asociada a esa editorial (estableciendo `editorial_id`).
     * 3. Comprueba que al acceder a la relación `$libro->editorial`, se obtiene una instancia de la clase `Editoriales`.
     * 4. Comprueba que el ID de la editorial relacionada (`$libro->editorial->id`) coincide con el ID de la editorial original (`$editorial->id`).
     *
     * @test
     * @return void
     */
    public function libro_belongs_to_editorial(): void
    {
        // Arrange: Crear una editorial y un libro asociado.
        $editorial = Editoriales::factory()->create();
        $libro = Libros::factory()->create(['editorial_id' => $editorial->id]);

        // Assert: Verificar la relación 'editorial'.
        // Verifica que la relación devuelve una instancia del modelo Editoriales.
        $this->assertInstanceOf(Editoriales::class, $libro->editorial);
        // Verifica que el ID de la editorial relacionada es el correcto.
        $this->assertEquals($editorial->id, $libro->editorial->id);
    }

    /**
     * Prueba la relación "hasMany" entre Libro y Comentarios.
     *
     * Verifica que un libro puede tener muchos comentarios asociados.
     * 1. Crea una instancia de `Libros` y una de `User` (necesaria para el comentario).
     * 2. Crea 3 instancias de `Comentarios` asociadas a ese libro y usuario.
     * 3. Comprueba que al acceder a la relación `$libro->comentarios`, se obtiene una instancia de `Illuminate\Database\Eloquent\Collection`.
     * 4. Comprueba que la colección contiene exactamente 3 elementos.
     * 5. Comprueba que el primer elemento de la colección es una instancia de la clase `Comentarios`.
     *
     * @test
     * @return void
     */
    public function libro_has_many_comentarios(): void
    {
        // Arrange: Crear un libro, un usuario y comentarios asociados.
        $libro = Libros::factory()->create();
        $user = User::factory()->create(); // Usuario necesario para el comentario.
        Comentarios::factory()->count(3)->create([
            'libro_id' => $libro->id,
            'user_id' => $user->id,
        ]);

        // Assert: Verificar la relación 'comentarios'.
        // Verifica que la relación devuelve una colección Eloquent.
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $libro->comentarios);
        // Verifica que la colección contiene el número esperado de comentarios.
        $this->assertCount(3, $libro->comentarios);
        // Verifica que los elementos de la colección son instancias del modelo Comentarios.
        $this->assertInstanceOf(Comentarios::class, $libro->comentarios->first());
    }

    /**
     * Prueba la relación "hasMany" entre Libro y Detallespedidos.
     *
     * Verifica que un libro puede estar asociado a muchos detalles de pedido.
     * 1. Crea una instancia de `Libros` y una de `Pedidos` (necesaria para el detalle).
     * 2. Crea 2 instancias de `Detallespedidos` asociadas a ese libro y pedido.
     * 3. Comprueba que al acceder a la relación `$libro->detallespedidos`, se obtiene una instancia de `Illuminate\Database\Eloquent\Collection`.
     * 4. Comprueba que la colección contiene exactamente 2 elementos.
     * 5. Comprueba que el primer elemento de la colección es una instancia de la clase `Detallespedidos`.
     *
     * @test
     * @return void
     */
    public function libro_has_many_detallespedidos(): void
    {
        // Arrange: Crear un libro, un pedido y detalles asociados.
        $libro = Libros::factory()->create();
        $pedido = Pedidos::factory()->create(); // Pedido necesario para el detalle.
        Detallespedidos::factory()->count(2)->create([
            'libro_id' => $libro->id,
            'pedido_id' => $pedido->id,
        ]);

        // Assert: Verificar la relación 'detallespedidos'.
        // Verifica que la relación devuelve una colección Eloquent.
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $libro->detallespedidos);
        // Verifica que la colección contiene el número esperado de detalles.
        $this->assertCount(2, $libro->detallespedidos);
        // Verifica que los elementos de la colección son instancias del modelo Detallespedidos.
        $this->assertInstanceOf(Detallespedidos::class, $libro->detallespedidos->first());
    }

    /**
     * Prueba que los atributos `$fillable` del modelo Libro son correctos.
     *
     * Verifica que la propiedad `$fillable` del modelo `Libros` contenga
     * exactamente los campos 'titulo', 'isbn', 'anio_publicacion', 'autor_id',
     * 'editorial_id' y 'precio'. Esto es importante para la asignación masiva segura.
     * 1. Obtiene el array `$fillable` de una nueva instancia del modelo `Libros`.
     * 2. Define el array esperado de atributos fillable.
     * 3. Compara el array obtenido con el esperado usando `assertEquals`.
     *
     * @test
     * @return void
     */
    public function libro_fillable_attributes_are_correct(): void
    {
        // Arrange: Obtener los atributos fillable del modelo.
        $fillable = (new Libros())->getFillable();
        // Arrange: Definir los atributos fillable esperados.
        $expected = ['titulo', 'isbn', 'anio_publicacion', 'autor_id', 'editorial_id', 'precio'];
        // Assert: Comparar los atributos fillable actuales con los esperados.
        $this->assertEquals($expected, $fillable);
    }
}
