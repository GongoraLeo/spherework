<?php
// filepath: tests\Unit\Models\PedidosModelTest.php

namespace Tests\Unit\Models; // Define el namespace para las pruebas unitarias de modelos.

use Tests\TestCase; // Importa la clase base para todas las pruebas en Laravel.
use App\Models\Pedidos; // Importa el modelo Pedidos que se va a probar.
use App\Models\User; // Importa el modelo User para probar la relación con cliente.
use App\Models\Detallespedidos; // Importa el modelo Detallespedidos para probar la relación.
use App\Models\Libros; // Importa el modelo Libros, necesario para crear Detallespedidos.
use Illuminate\Foundation\Testing\RefreshDatabase; // Importa el trait para resetear la BD entre pruebas.
use Carbon\Carbon; // Importa la clase Carbon para verificar el tipo de fecha.

/**
 * Class PedidosModelTest
 *
 * Suite de pruebas unitarias para el modelo `Pedidos`.
 * Verifica las relaciones Eloquent (`belongsTo` con User, `hasMany` con Detallespedidos),
 * las conversiones de tipos (`casts`) y la existencia de constantes de estado.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba, facilitando el uso de factories.
 *
 * @package Tests\Unit\Models
 */
// CORREGIDO: El nombre de la clase debe coincidir con el nombre del archivo (PedidosModelTest)
class PedidosModelTest extends TestCase
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
     * Prueba la relación "belongsTo" entre Pedido y User (cliente).
     *
     * Verifica que un pedido pertenece a un usuario (cliente).
     * 1. Crea una instancia de `User` usando su factory.
     * 2. Crea una instancia de `Pedidos` asociada a ese usuario (estableciendo `cliente_id`).
     * 3. Comprueba que al acceder a la relación `$pedido->cliente`, se obtiene una instancia de la clase `User`.
     * 4. Comprueba que el ID del cliente relacionado (`$pedido->cliente->id`) coincide con el ID del usuario original (`$user->id`).
     *
     * @test
     * @return void
     */
    public function pedido_belongs_to_cliente(): void // Cliente es User
    {
        // Arrange: Crear un usuario y un pedido asociado.
        $user = User::factory()->create();
        $pedido = Pedidos::factory()->create(['cliente_id' => $user->id]);

        // Assert: Verificar la relación 'cliente'.
        // Verifica que la relación devuelve una instancia del modelo User.
        $this->assertInstanceOf(User::class, $pedido->cliente);
        // Verifica que el ID del cliente relacionado es el correcto.
        $this->assertEquals($user->id, $pedido->cliente->id);
    }

    /**
     * Prueba la relación "hasMany" entre Pedido y Detallespedidos.
     *
     * Verifica que un pedido puede tener muchos detalles de pedido asociados.
     * 1. Crea una instancia de `Pedidos` y una de `Libros` (necesaria para el detalle).
     * 2. Crea 2 instancias de `Detallespedidos` asociadas a ese pedido y libro.
     * 3. Comprueba que al acceder a la relación `$pedido->detallespedidos`, se obtiene una instancia de `Illuminate\Database\Eloquent\Collection`.
     * 4. Comprueba que la colección contiene exactamente 2 elementos.
     * 5. Comprueba que el primer elemento de la colección es una instancia de la clase `Detallespedidos`.
     *
     * @test
     * @return void
     */
    public function pedido_has_many_detallespedidos(): void
    {
        // Arrange: Crear un pedido, un libro y detalles asociados.
        $pedido = Pedidos::factory()->create();
        $libro = Libros::factory()->create(); // Libro necesario para el detalle.
        Detallespedidos::factory()->count(2)->create([
            'pedido_id' => $pedido->id,
            'libro_id' => $libro->id,
        ]);

        // Assert: Verificar la relación 'detallespedidos'.
        // Verifica que la relación devuelve una colección Eloquent.
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $pedido->detallespedidos);
        // Verifica que la colección contiene el número esperado de detalles.
        $this->assertCount(2, $pedido->detallespedidos);
        // Verifica que los elementos de la colección son instancias del modelo Detallespedidos.
        $this->assertInstanceOf(Detallespedidos::class, $pedido->detallespedidos->first());
    }

    /**
     * Prueba que el atributo `fecha_pedido` se convierte a un objeto DateTime (Carbon).
     *
     * Verifica que la configuración `$casts` del modelo `Pedidos` convierte
     * correctamente el atributo `fecha_pedido` a una instancia de `Carbon\Carbon`.
     * 1. Crea una instancia de `Pedidos` estableciendo `fecha_pedido` con `now()`.
     * 2. Refresca la instancia desde la base de datos para asegurar que se apliquen los casts.
     * 3. Comprueba que el tipo del atributo `$pedido->fecha_pedido` sea `Carbon\Carbon`.
     *
     * @test
     * @return void
     */
    public function pedido_fecha_pedido_is_cast_to_datetime(): void
    {
        // Arrange: Crear un pedido con fecha.
        $pedido = Pedidos::factory()->create(['fecha_pedido' => now()]);
        // Arrange: Refrescar el modelo desde la BD.
        $pedido = $pedido->fresh();
        // Assert: Verificar que el atributo es una instancia de Carbon.
        $this->assertInstanceOf(Carbon::class, $pedido->fecha_pedido);
    }

    /**
     * Prueba que las constantes de estado definidas en el modelo Pedidos existen y tienen los valores esperados.
     *
     * Verifica los valores de las constantes `STATUS_PENDIENTE` y `STATUS_COMPLETADO`.
     *
     * @test
     * @return void
     */
    public function pedido_status_constants_exist(): void
    {
        // Assert: Verificar los valores de las constantes de estado.
        $this->assertEquals('pendiente', Pedidos::STATUS_PENDIENTE);
        $this->assertEquals('completado', Pedidos::STATUS_COMPLETADO);
        $this->assertEquals('procesando', Pedidos::STATUS_PROCESANDO);
        $this->assertEquals('enviado', Pedidos::STATUS_ENVIADO);
        $this->assertEquals('entregado', Pedidos::STATUS_ENTREGADO);
        $this->assertEquals('cancelado', Pedidos::STATUS_CANCELADO);
    }
}
