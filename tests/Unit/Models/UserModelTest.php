<?php
// filepath: tests\Unit\Models\UserModelTest.php

namespace Tests\Unit\Models; // Define el namespace para las pruebas unitarias de modelos.

use Tests\TestCase; // Importa la clase base para todas las pruebas en Laravel.
use App\Models\User; // Importa el modelo User que se va a probar.
use App\Models\Pedidos; // Importa el modelo Pedidos para probar la relación.
use App\Models\Comentarios; // Importa el modelo Comentarios para probar la relación.
use App\Models\Libros; // Importa el modelo Libros, necesario para crear Comentarios.
use Illuminate\Foundation\Testing\RefreshDatabase; // Importa el trait para resetear la BD entre pruebas.
use Illuminate\Support\Facades\Hash; // Importa la fachada Hash para verificar contraseñas.

/**
 * Class UserModelTest
 *
 * Suite de pruebas unitarias para el modelo `User`.
 * Verifica las relaciones Eloquent (`hasMany` con Pedidos y Comentarios),
 * las conversiones de tipos (`casts`) y la configuración de atributos ocultos (`hidden`).
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba, facilitando el uso de factories.
 *
 * @package Tests\Unit\Models
 */
class UserModelTest extends TestCase
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
     * Prueba la relación "hasMany" entre User y Pedidos.
     *
     * Verifica que un usuario puede tener muchos pedidos asociados.
     * 1. Crea una instancia de `User` usando su factory.
     * 2. Crea 3 instancias de `Pedidos` asociadas a ese usuario (estableciendo `cliente_id`).
     * 3. Comprueba que al acceder a la relación `$user->pedidos`, se obtiene una instancia de `Illuminate\Database\Eloquent\Collection`.
     * 4. Comprueba que la colección contiene exactamente 3 elementos.
     * 5. Comprueba que el primer elemento de la colección es una instancia de la clase `Pedidos`.
     *
     * @test
     * @return void
     */
    public function user_has_many_pedidos(): void
    {
        // Arrange: Crear un usuario y pedidos asociados.
        $user = User::factory()->create();
        Pedidos::factory()->count(3)->create(['cliente_id' => $user->id]);

        // Assert: Verificar la relación 'pedidos'.
        // Verifica que la relación devuelve una colección Eloquent.
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->pedidos);
        // Verifica que la colección contiene el número esperado de pedidos.
        $this->assertCount(3, $user->pedidos);
        // Verifica que los elementos de la colección son instancias del modelo Pedidos.
        $this->assertInstanceOf(Pedidos::class, $user->pedidos->first());
    }

    /**
     * Prueba la relación "hasMany" entre User y Comentarios.
     *
     * Verifica que un usuario puede tener muchos comentarios asociados.
     * 1. Crea una instancia de `User` y una de `Libros` (necesaria para el comentario).
     * 2. Crea 2 instancias de `Comentarios` asociadas a ese usuario y libro.
     * 3. Comprueba que al acceder a la relación `$user->comentarios`, se obtiene una instancia de `Illuminate\Database\Eloquent\Collection`.
     * 4. Comprueba que la colección contiene exactamente 2 elementos.
     * 5. Comprueba que el primer elemento de la colección es una instancia de la clase `Comentarios`.
     *
     * @test
     * @return void
     */
    public function user_has_many_comentarios(): void
    {
        // Arrange: Crear un usuario, un libro y comentarios asociados.
        $user = User::factory()->create();
        $libro = Libros::factory()->create(); // Libro necesario para el comentario.
        Comentarios::factory()->count(2)->create([
            'user_id' => $user->id,
            'libro_id' => $libro->id,
        ]);

        // Assert: Verificar la relación 'comentarios'.
        // Verifica que la relación devuelve una colección Eloquent.
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->comentarios);
        // Verifica que la colección contiene el número esperado de comentarios.
        $this->assertCount(2, $user->comentarios);
        // Verifica que los elementos de la colección son instancias del modelo Comentarios.
        $this->assertInstanceOf(Comentarios::class, $user->comentarios->first());
    }

    /**
     * Prueba que el atributo `rol` se convierte a string.
     *
     * Verifica que la configuración `$casts` del modelo `User` convierte
     * correctamente el atributo `rol` a tipo `string`.
     * 1. Crea una instancia de `User` estableciendo el `rol`.
     * 2. Comprueba que el tipo del atributo `$user->rol` sea `string`.
     *
     * @test
     * @return void
     */
    public function user_rol_is_cast_to_string(): void
    {
        // Arrange: Crear un usuario con un rol específico.
        $user = User::factory()->create(['rol' => 'administrador']);
        // Assert: Verificar que el tipo del atributo 'rol' es string.
        $this->assertIsString($user->rol);
    }

    /**
     * Prueba que el atributo `password` está oculto en la serialización.
     *
     * Verifica que la configuración `$hidden` del modelo `User` oculta
     * el atributo `password` cuando el modelo se convierte a un array (ej. para JSON).
     * 1. Crea una instancia de `User`.
     * 2. Convierte el modelo a un array usando `toArray()`.
     * 3. Comprueba que la clave 'password' no exista en el array resultante.
     *
     * @test
     * @return void
     */
    public function user_password_is_hidden(): void
    {
        // Arrange: Crear un usuario.
        $user = User::factory()->create();
        // Assert: Verificar que 'password' no está presente en la representación de array.
        $this->assertArrayNotHasKey('password', $user->toArray());
    }

     /**
      * Prueba que el atributo `password` se hashea automáticamente.
      *
      * Verifica que la configuración `$casts` con 'hashed' para `password`
      * funciona correctamente. Aunque no se puede probar el cast directamente,
      * se verifica que el valor almacenado no sea el string original y que
      * coincida al usar `Hash::check`.
      * 1. Crea una instancia de `User` pasando una contraseña en texto plano ('plain-password').
      * 2. Comprueba que el valor del atributo `$user->password` no sea igual al texto plano original.
      * 3. Comprueba que `Hash::check` confirme que el texto plano original coincide con el valor hasheado almacenado.
      *
      * @test
      * @return void
      */
    public function user_password_is_hashed(): void
    {
         // Arrange: Crear un usuario con una contraseña en texto plano.
         $user = User::factory()->create(['password' => 'plain-password']);
         // Assert: Verificar que la contraseña almacenada no es el texto plano.
         $this->assertNotEquals('plain-password', $user->password);
         // Assert: Verificar que el texto plano coincide con el hash almacenado usando Hash::check.
         $this->assertTrue(Hash::check('plain-password', $user->password));
    }
}
