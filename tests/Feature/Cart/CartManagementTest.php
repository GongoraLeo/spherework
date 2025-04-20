<?php

namespace Tests\Feature\Cart;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Libros;
use App\Models\Pedidos;
use App\Models\Detallespedidos;

class CartManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Libros $book1;
    private Libros $book2;

    protected function setUp(): void
    {
        parent::setUp();
        // Crea un usuario cliente para las pruebas
        $this->user = User::factory()->create(['rol' => 'cliente']);
        // Crea algunos libros para usar en las pruebas
        $this->book1 = Libros::factory()->create(['precio' => 10.00]);
        $this->book2 = Libros::factory()->create(['precio' => 15.00]);
    }

    /** @test */
    public function guest_cannot_view_cart()
    {
        // Intenta acceder al carrito sin estar logueado
        $response = $this->get(route('detallespedidos.index'));
        // Debe redirigir a la página de login
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_view_empty_cart()
    {
        // Accede al carrito como usuario logueado (sin pedido pendiente)
        $response = $this->actingAs($this->user)
                         ->get(route('detallespedidos.index'));

        // Verifica que la página carga correctamente
        $response->assertStatus(200);
        // Verifica que se muestra un mensaje indicando que el carrito está vacío
        $response->assertSee('Tu carrito está vacío'); // Ajusta este texto si es diferente en tu vista
        // Verifica que la variable 'detallespedidos' pasada a la vista es una colección vacía
        $response->assertViewHas('detallespedidos', function ($detalles) {
            return $detalles instanceof \Illuminate\Support\Collection && $detalles->isEmpty();
        });
        // Verifica que el total pasado a la vista es 0
        $response->assertViewHas('total', 0);
    }

    /** @test */
    public function user_can_add_a_book_to_cart_creating_pending_order()
    {
        // Simula el envío del formulario para añadir un libro al carrito
        $response = $this->actingAs($this->user)
                         ->post(route('detallespedidos.store'), [
                             'libro_id' => $this->book1->id,
                             'cantidad' => 2,
                             'precio' => $this->book1->precio, // El precio se envía desde el formulario
                         ]);

        // Verifica que redirige a la vista del carrito
        $response->assertRedirect(route('detallespedidos.index'));
        // Verifica que hay un mensaje de éxito en la sesión
        $response->assertSessionHas('success', 'Libro añadido al carrito correctamente.');

        // Verifica que se ha creado un pedido pendiente para el usuario en la BD
        $this->assertDatabaseHas('pedidos', [
            'cliente_id' => $this->user->id,
            'status' => Pedidos::STATUS_PENDIENTE,
        ]);
        // Obtiene el pedido pendiente creado
        $pedidoPendiente = Pedidos::where('cliente_id', $this->user->id)
                                  ->where('status', Pedidos::STATUS_PENDIENTE)
                                  ->first();
        // Verifica que se ha creado el detalle del pedido (ítem del carrito) en la BD
        $this->assertDatabaseHas('detallespedidos', [
            'pedido_id' => $pedidoPendiente->id,
            'libro_id' => $this->book1->id,
            'cantidad' => 2,
            'precio' => $this->book1->precio,
        ]);
    }

    /** @test */
    public function adding_same_book_updates_quantity_in_cart()
    {
        // 1. Añadir el libro por primera vez con cantidad 1
        $this->actingAs($this->user)
             ->post(route('detallespedidos.store'), [
                 'libro_id' => $this->book1->id,
                 'cantidad' => 1,
                 'precio' => $this->book1->precio,
             ]);

        // 2. Añadir el mismo libro de nuevo, sumando 3 a la cantidad
        $response = $this->actingAs($this->user)
                         ->post(route('detallespedidos.store'), [
                             'libro_id' => $this->book1->id,
                             'cantidad' => 3, // Añadir 3 más
                             'precio' => $this->book1->precio, // Podría ser diferente si el precio cambia, pero aquí usamos el mismo
                         ]);

        // Verifica la redirección y el mensaje de sesión específico para actualización
        $response->assertRedirect(route('detallespedidos.index'));
        $response->assertSessionHas('success', 'Cantidad actualizada en el carrito.');

        // Obtiene el pedido pendiente
        $pedidoPendiente = Pedidos::where('cliente_id', $this->user->id)
                                  ->where('status', Pedidos::STATUS_PENDIENTE)
                                  ->first();
        // Verifica que la cantidad en el detalle se ha actualizado a 4 (1 + 3)
        $this->assertDatabaseHas('detallespedidos', [
            'pedido_id' => $pedidoPendiente->id,
            'libro_id' => $this->book1->id,
            'cantidad' => 4,
            'precio' => $this->book1->precio, // Verifica que el precio se mantuvo (según tu lógica)
        ]);
        // Asegura que solo hay una línea de detalle para este libro en este pedido
        $this->assertDatabaseCount('detallespedidos', 1);
    }

    /** @test */
    public function user_can_update_item_quantity_in_cart()
    {
        // Setup: Crear un pedido pendiente y un detalle asociado
        $pedido = Pedidos::factory()->create([
            'cliente_id' => $this->user->id,
            'status' => Pedidos::STATUS_PENDIENTE,
        ]);
        $detalle = Detallespedidos::factory()->create([
            'pedido_id' => $pedido->id,
            'libro_id' => $this->book1->id,
            'cantidad' => 2,
            'precio' => $this->book1->precio,
        ]);

        // Simula la actualización de la cantidad a 5
        $response = $this->actingAs($this->user)
                         ->put(route('detallespedidos.update', $detalle), [ // Usa el objeto $detalle para la ruta
                             'cantidad' => 5, // Nueva cantidad
                         ]);

        // Verifica la redirección y el mensaje de éxito
        $response->assertRedirect(route('detallespedidos.index'));
        $response->assertSessionHas('success', 'Cantidad actualizada correctamente.');
        // Verifica que la cantidad en la BD se ha actualizado
        $this->assertDatabaseHas('detallespedidos', [
            'id' => $detalle->id,
            'cantidad' => 5,
        ]);
    }

     /** @test */
    public function user_cannot_update_item_from_another_user_cart()
    {
        // Setup: Crear un pedido pendiente y detalle para OTRO usuario
        $otherUser = User::factory()->create();
        $otherPedido = Pedidos::factory()->create([
            'cliente_id' => $otherUser->id,
            'status' => Pedidos::STATUS_PENDIENTE,
        ]);
        $otherDetalle = Detallespedidos::factory()->create([
            'pedido_id' => $otherPedido->id,
            'libro_id' => $this->book1->id,
            'cantidad' => 1,
        ]);

        // Simula que $this->user intenta actualizar el detalle de $otherUser
        $response = $this->actingAs($this->user)
                         ->put(route('detallespedidos.update', $otherDetalle), [
                             'cantidad' => 5,
                         ]);

        // Verifica que redirige (probablemente al carrito) con un mensaje de error
        $response->assertRedirect(route('detallespedidos.index')); // O a donde redirija tu controlador en este caso
        $response->assertSessionHas('error', 'No se pudo actualizar el item.'); // Verifica el mensaje de error específico
        // Verifica que la cantidad del detalle original NO cambió en la BD
        $this->assertDatabaseHas('detallespedidos', [
            'id' => $otherDetalle->id,
            'cantidad' => 1,
        ]);
    }

    /** @test */
    public function user_can_remove_item_from_cart()
    {
        // Setup: Crear pedido pendiente y detalle
        $pedido = Pedidos::factory()->create([
            'cliente_id' => $this->user->id,
            'status' => Pedidos::STATUS_PENDIENTE,
        ]);
        $detalle = Detallespedidos::factory()->create([
            'pedido_id' => $pedido->id,
            'libro_id' => $this->book1->id,
            'cantidad' => 1,
        ]);

        // Simula la eliminación del detalle
        $response = $this->actingAs($this->user)
                         ->delete(route('detallespedidos.destroy', $detalle)); // Usa el objeto $detalle

        // Verifica la redirección y el mensaje de éxito
        $response->assertRedirect(route('detallespedidos.index'));
        $response->assertSessionHas('success', 'Item eliminado del carrito.');
        // Verifica que el detalle ya no existe en la BD
        $this->assertDatabaseMissing('detallespedidos', ['id' => $detalle->id]);
    }

    /** @test */
    public function update_cart_item_fails_with_invalid_quantity()
    {
        $pedido = Pedidos::factory()->create(['cliente_id' => $this->user->id, 'status' => Pedidos::STATUS_PENDIENTE]);
        $detalle = Detallespedidos::factory()->create(['pedido_id' => $pedido->id, 'libro_id' => $this->book1->id, 'cantidad' => 2]);

        $response = $this->actingAs($this->user)
                         ->put(route('detallespedidos.update', $detalle), ['cantidad' => 0]); // Cantidad inválida (min:1)

        $response->assertSessionHasErrors('cantidad');
        $this->assertDatabaseHas('detallespedidos', ['id' => $detalle->id, 'cantidad' => 2]); // Verifica que no cambió

        $response = $this->actingAs($this->user)
                         ->put(route('detallespedidos.update', $detalle), ['cantidad' => -1]); // Cantidad inválida

        $response->assertSessionHasErrors('cantidad');
        $this->assertDatabaseHas('detallespedidos', ['id' => $detalle->id, 'cantidad' => 2]);
    }

     /** @test */
    public function user_cannot_remove_item_from_another_user_cart()
    {
        $otherUser = User::factory()->create();
        $otherPedido = Pedidos::factory()->create(['cliente_id' => $otherUser->id, 'status' => Pedidos::STATUS_PENDIENTE]);
        $otherDetalle = Detallespedidos::factory()->create(['pedido_id' => $otherPedido->id, 'libro_id' => $this->book1->id]);

        $response = $this->actingAs($this->user) // Logueado como $this->user
                         ->delete(route('detallespedidos.destroy', $otherDetalle)); // Intentando borrar $otherDetalle

        $response->assertRedirect(route('detallespedidos.index'));
        $response->assertSessionHas('error', 'No se pudo eliminar el item.');
        $this->assertDatabaseHas('detallespedidos', ['id' => $otherDetalle->id]); // Verifica que no se borró
    }

    /** @test */
    public function user_cannot_update_or_delete_item_from_non_pending_order()
    {
        // Crear un pedido COMPLETADO con un detalle
        $pedidoCompletado = Pedidos::factory()->completado()->create(['cliente_id' => $this->user->id]);
        $detalleCompletado = Detallespedidos::factory()->create(['pedido_id' => $pedidoCompletado->id, 'libro_id' => $this->book1->id, 'cantidad' => 1]);

        // Intentar actualizar cantidad
        $responseUpdate = $this->actingAs($this->user)
                               ->put(route('detallespedidos.update', $detalleCompletado), ['cantidad' => 5]);
        $responseUpdate->assertRedirect(route('detallespedidos.index'));
        $responseUpdate->assertSessionHas('error', 'No se pudo actualizar el item.'); // Error porque el pedido no está pendiente
        $this->assertDatabaseHas('detallespedidos', ['id' => $detalleCompletado->id, 'cantidad' => 1]); // No cambió

        // Intentar eliminar
        $responseDelete = $this->actingAs($this->user)
                               ->delete(route('detallespedidos.destroy', $detalleCompletado));
        $responseDelete->assertRedirect(route('detallespedidos.index'));
        $responseDelete->assertSessionHas('error', 'No se pudo eliminar el item.'); // Error porque el pedido no está pendiente
        $this->assertDatabaseHas('detallespedidos', ['id' => $detalleCompletado->id]); // No se borró
    }
}
