<?php
// filepath: tests\Unit\Models\ComentariosModelTest.php

namespace Tests\Unit\Models; // Define el namespace para las pruebas unitarias de modelos.

use Tests\TestCase; // Importa la clase base para todas las pruebas en Laravel.
use App\Models\Comentarios; // Importa el modelo Comentarios que se va a probar.
use App\Models\User; // Importa el modelo User para probar la relación.
use App\Models\Libros; // Importa el modelo Libros para probar la relación.
use Illuminate\Foundation\Testing\RefreshDatabase; // Importa el trait para resetear la BD entre pruebas.

/**
 * Class ComentariosModelTest
 *
 * Suite de pruebas unitarias para el modelo `Comentarios`.
 * Verifica las relaciones Eloquent (`belongsTo` con User y Libros) y las
 * propiedades configuradas en el modelo, como los atributos `$fillable`.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba, facilitando el uso de factories.
 *
 * @package Tests\Unit\Models
 */
class ComentariosModelTest extends TestCase
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
     * Prueba la relación "belongsTo" entre Comentario y User.
     *
     * Verifica que un comentario pertenece a un usuario.
     * 1. Crea una instancia de `User` usando su factory.
     * 2. Crea una instancia de `Comentarios` asociada a ese usuario (estableciendo `user_id`).
     * 3. Comprueba que al acceder a la relación `$comentario->user`, se obtiene una instancia de la clase `User`.
     * 4. Comprueba que el ID del usuario relacionado (`$comentario->user->id`) coincide con el ID del usuario original (`$user->id`).
     *
     * @test
     * @return void
     */
    public function comentario_belongs_to_user(): void
    {
        // Arrange: Crear un usuario y un comentario asociado.
        $user = User::factory()->create();
        $comentario = Comentarios::factory()->create(['user_id' => $user->id]);

        // Assert: Verificar la relación 'user'.
        // Verifica que la relación devuelve una instancia del modelo User.
        $this->assertInstanceOf(User::class, $comentario->user);
        // Verifica que el ID del usuario relacionado es el correcto.
        $this->assertEquals($user->id, $comentario->user->id);
    }

    /**
     * Prueba la relación "belongsTo" entre Comentario y Libro.
     *
     * Verifica que un comentario pertenece a un libro.
     * 1. Crea una instancia de `Libros` usando su factory.
     * 2. Crea una instancia de `Comentarios` asociada a ese libro (estableciendo `libro_id`).
     * 3. Comprueba que al acceder a la relación `$comentario->libro`, se obtiene una instancia de la clase `Libros`.
     * 4. Comprueba que el ID del libro relacionado (`$comentario->libro->id`) coincide con el ID del libro original (`$libro->id`).
     *
     * @test
     * @return void
     */
    public function comentario_belongs_to_libro(): void
    {
        // Arrange: Crear un libro y un comentario asociado.
        $libro = Libros::factory()->create();
        $comentario = Comentarios::factory()->create(['libro_id' => $libro->id]);

        // Assert: Verificar la relación 'libro'.
        // Verifica que la relación devuelve una instancia del modelo Libros.
        $this->assertInstanceOf(Libros::class, $comentario->libro);
        // Verifica que el ID del libro relacionado es el correcto.
        $this->assertEquals($libro->id, $comentario->libro->id);
    }

    /**
     * Prueba que los atributos `$fillable` del modelo Comentario son correctos.
     *
     * Verifica que la propiedad `$fillable` del modelo `Comentarios` contenga
     * exactamente los campos 'user_id', 'libro_id', 'comentario' y 'puntuacion'.
     * Esto es importante para la asignación masiva segura.
     * 1. Obtiene el array `$fillable` de una nueva instancia del modelo `Comentarios`.
     * 2. Define el array esperado de atributos fillable.
     * 3. Compara el array obtenido con el esperado usando `assertEquals`.
     *
     * @test
     * @return void
     */
    public function comentario_fillable_attributes_are_correct(): void
    {
        // Arrange: Obtener los atributos fillable del modelo.
        $fillable = (new Comentarios())->getFillable();
        // Arrange: Definir los atributos fillable esperados.
        $expected = ['user_id', 'libro_id', 'comentario', 'puntuacion'];
        // Assert: Comparar los atributos fillable actuales con los esperados.
        $this->assertEquals($expected, $fillable);
    }
}
