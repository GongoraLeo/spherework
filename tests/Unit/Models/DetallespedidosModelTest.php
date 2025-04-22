<?php
// filepath: tests\Unit\Models\DetallespedidosModelTest.php

namespace Tests\Unit\Models; // Define el namespace para las pruebas unitarias de modelos.

use Tests\TestCase; // Importa la clase base para todas las pruebas en Laravel.
use App\Models\Detallespedidos; // Importa el modelo Detallespedidos que se va a probar.
use App\Models\Pedidos; // Importa el modelo Pedidos para probar la relación.
use App\Models\Libros; // Importa el modelo Libros para probar la relación.
use Illuminate\Foundation\Testing\RefreshDatabase; // Importa el trait para resetear la BD entre pruebas.

/**
 * Class DetallespedidosModelTest
 *
 * Suite de pruebas unitarias para el modelo `Detallespedidos`.
 * Verifica las relaciones Eloquent (`belongsTo` con Pedidos y Libros),
 * los atributos `$fillable` y el uso del trait `HasFactory`.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba, facilitando el uso de factories.
 *
 * @package Tests\Unit\Models
 */
class DetallespedidosModelTest extends TestCase
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
     * Prueba la relación "belongsTo" entre Detallepedido y Pedido.
     *
     * Verifica que un detalle de pedido pertenece a un pedido.
     * 1. Crea una instancia de `Pedidos` usando su factory.
     * 2. Crea una instancia de `Detallespedidos` asociada a ese pedido (estableciendo `pedido_id`).
     * 3. Comprueba que al acceder a la relación `$detalle->pedido`, se obtiene una instancia de la clase `Pedidos`.
     * 4. Comprueba que el ID del pedido relacionado (`$detalle->pedido->id`) coincide con el ID del pedido original (`$pedido->id`).
     *
     * @test
     * @return void
     */
    public function detallepedido_belongs_to_pedido(): void
    {
        // Arrange: Crear un pedido y un detalle asociado.
        $pedido = Pedidos::factory()->create();
        $detalle = Detallespedidos::factory()->create(['pedido_id' => $pedido->id]);

        // Assert: Verificar la relación 'pedido'.
        // Verifica que la relación devuelve una instancia del modelo Pedidos.
        $this->assertInstanceOf(Pedidos::class, $detalle->pedido);
        // Verifica que el ID del pedido relacionado es el correcto.
        $this->assertEquals($pedido->id, $detalle->pedido->id);
    }

    /**
     * Prueba la relación "belongsTo" entre Detallepedido y Libro.
     *
     * Verifica que un detalle de pedido pertenece a un libro.
     * 1. Crea una instancia de `Libros` usando su factory.
     * 2. Crea una instancia de `Detallespedidos` asociada a ese libro (estableciendo `libro_id`).
     * 3. Comprueba que al acceder a la relación `$detalle->libro`, se obtiene una instancia de la clase `Libros`.
     * 4. Comprueba que el ID del libro relacionado (`$detalle->libro->id`) coincide con el ID del libro original (`$libro->id`).
     *
     * @test
     * @return void
     */
    public function detallepedido_belongs_to_libro(): void
    {
        // Arrange: Crear un libro y un detalle asociado.
        $libro = Libros::factory()->create();
        $detalle = Detallespedidos::factory()->create(['libro_id' => $libro->id]);

        // Assert: Verificar la relación 'libro'.
        // Verifica que la relación devuelve una instancia del modelo Libros.
        $this->assertInstanceOf(Libros::class, $detalle->libro);
        // Verifica que el ID del libro relacionado es el correcto.
        $this->assertEquals($libro->id, $detalle->libro->id);
    }

    /**
     * Prueba que los atributos `$fillable` del modelo Detallepedido son correctos.
     *
     * Verifica que la propiedad `$fillable` del modelo `Detallespedidos` contenga
     * exactamente los campos 'pedido_id', 'libro_id', 'cantidad' y 'precio'.
     * Esto es importante para la asignación masiva segura.
     * 1. Obtiene el array `$fillable` de una nueva instancia del modelo `Detallespedidos`.
     * 2. Define el array esperado de atributos fillable.
     * 3. Compara el array obtenido con el esperado usando `assertEquals`.
     *
     * @test
     * @return void
     */
    public function detallepedido_fillable_attributes_are_correct(): void
    {
        // Arrange: Obtener los atributos fillable del modelo.
        $fillable = (new Detallespedidos())->getFillable();
        // Arrange: Definir los atributos fillable esperados.
        $expected = ['pedido_id', 'libro_id', 'cantidad', 'precio'];
        // Assert: Comparar los atributos fillable actuales con los esperados.
        $this->assertEquals($expected, $fillable);
    }

    /**
     * Prueba que el modelo Detallepedido utiliza el trait HasFactory.
     *
     * Verifica que el trait `Illuminate\Database\Eloquent\Factories\HasFactory`
     * esté presente en la lista de traits utilizados por la clase `Detallespedidos`.
     * Esto confirma que se pueden usar factories para este modelo.
     * 1. Obtiene la lista de traits usados por la clase `Detallespedidos`.
     * 2. Comprueba que la clave correspondiente al trait `HasFactory` exista en el array de traits.
     *
     * @test
     * @return void
     */
    public function detallepedido_uses_hasfactory_trait(): void
    {
        // Arrange: Obtener los traits usados por la clase.
        $uses = class_uses(Detallespedidos::class);
        // Assert: Verificar que el trait HasFactory está presente.
        $this->assertArrayHasKey(\Illuminate\Database\Eloquent\Factories\HasFactory::class, $uses);
    }
}
