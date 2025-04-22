<?php
// filepath: tests\Feature\Cart\CartManagementTest.php

namespace Tests\Feature\Cart;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\User; // Modelo User para crear usuarios de prueba.
use App\Models\Libros; // Modelo Libros para crear libros de prueba.
use App\Models\Pedidos; // Modelo Pedidos para crear pedidos de prueba.
use App\Models\Detallespedidos; // Modelo Detallespedidos para crear y verificar detalles.

/**
 * Class CartManagementTest
 *
 * Suite de pruebas de Feature para verificar la funcionalidad del carrito de compras
 * (gestionado por `DetallespedidosController`). Comprueba el acceso, la adición,
 * actualización y eliminación de ítems del carrito, así como las restricciones
 * de autorización y validación.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba.
 *
 * @package Tests\Feature\Cart
 */
class CartManagementTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase,
     * asegurando que los tests no interfieran entre sí.
     */
    use RefreshDatabase;

    /**
     * @var User Instancia del usuario cliente utilizada en las pruebas.
     */
    private User $user;
    /**
     * @var Libros Instancia del primer libro de prueba.
     */
    private Libros $book1;
    /**
     * @var Libros Instancia del segundo libro de prueba.
     */
    private Libros $book2;

    /**
     * Configura el entorno de prueba antes de cada test.
     *
     * Crea un usuario con rol 'cliente' y dos libros de prueba utilizando factories.
     * Estas instancias se almacenan en propiedades de la clase para ser
     * reutilizadas en los métodos de prueba. Llama al método `setUp`
     * de la clase padre.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp(); // Llama al método setUp de la clase padre.
        // Crea un usuario cliente.
        $this->user = User::factory()->create(['rol' => 'cliente']);
        // Crea dos libros de prueba con precios específicos.
        $this->book1 = Libros::factory()->create(['precio' => 10.00]);
        $this->book2 = Libros::factory()->create(['precio' => 15.00]);
    }

    /**
     * Prueba que un usuario no autenticado (invitado) no puede ver el carrito.
     *
     * Simula una petición GET a la ruta 'detallespedidos.index' sin autenticar usuario.
     * Verifica que la respuesta sea una redirección a la ruta nombrada 'login'.
     *
     * @test
     * @return void
     */
    public function guest_cannot_view_cart(): void
    {
        // Act: Realizar la petición GET como invitado.
        $response = $this->get(route('detallespedidos.index'));
        // Assert: Verificar la redirección a login.
        $response->assertRedirect(route('login'));
    }

    /**
     * Prueba que un usuario autenticado puede ver su carrito vacío.
     *
     * Simula una petición GET a la ruta 'detallespedidos.index' actuando como el usuario cliente.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se muestre el texto 'Tu carrito está vacío' en la respuesta.
     * Verifica que la variable 'detallespedidos' pasada a la vista sea una colección vacía.
     * Verifica que la variable 'total' pasada a la vista sea 0.
     *
     * @test
     * @return void
     */
    public function authenticated_user_can_view_empty_cart(): void
    {
        // Act: Realizar la petición GET como usuario autenticado.
        $response = $this->actingAs($this->user)
                         ->get(route('detallespedidos.index'));

        // Assert: Verificar la respuesta, contenido y datos de la vista.
        $response->assertStatus(200);
        $response->assertSee('Tu carrito está vacío'); // Verifica mensaje de carrito vacío.
        // Verifica que 'detallespedidos' es una colección vacía.
        $response->assertViewHas('detallespedidos', function ($detalles) {
            return $detalles instanceof \Illuminate\Support\Collection && $detalles->isEmpty();
        });
        $response->assertViewHas('total', 0); // Verifica que el total es 0.
    }

    /**
     * Prueba que un usuario puede añadir un libro al carrito, creando un pedido pendiente.
     *
     * Simula una petición POST a la ruta 'detallespedidos.store' actuando como usuario,
     * enviando el ID del libro, cantidad y precio.
     * Verifica que la respuesta sea una redirección a 'detallespedidos.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que se haya creado un registro en la tabla 'pedidos' con estado 'pendiente'
     * para el usuario.
     * Obtiene el pedido pendiente creado y verifica que se haya creado un registro
     * en 'detallespedidos' asociado a ese pedido y libro, con la cantidad y precio correctos.
     *
     * @test
     * @return void
     */
    public function user_can_add_a_book_to_cart_creating_pending_order(): void
    {
        // Act: Realizar la petición POST para añadir un libro.
        $response = $this->actingAs($this->user)
                         ->post(route('detallespedidos.store'), [
                             'libro_id' => $this->book1->id,
                             'cantidad' => 2,
                             'precio' => $this->book1->precio, // Precio enviado desde el formulario.
                         ]);

        // Assert: Verificar redirección, mensaje de sesión y estado de la BD.
        $response->assertRedirect(route('detallespedidos.index'));
        $response->assertSessionHas('success', 'Libro añadido al carrito correctamente.');

        // Verifica la creación del pedido pendiente.
        $this->assertDatabaseHas('pedidos', [
            'cliente_id' => $this->user->id,
            'status' => Pedidos::STATUS_PENDIENTE,
        ]);
        // Obtiene el pedido para verificar el detalle.
        $pedidoPendiente = Pedidos::where('cliente_id', $this->user->id)
                                  ->where('status', Pedidos::STATUS_PENDIENTE)
                                  ->first();
        // Verifica la creación del detalle del pedido.
        $this->assertDatabaseHas('detallespedidos', [
            'pedido_id' => $pedidoPendiente->id,
            'libro_id' => $this->book1->id,
            'cantidad' => 2,
            'precio' => $this->book1->precio,
        ]);
    }

    /**
     * Prueba que añadir el mismo libro al carrito actualiza la cantidad existente.
     *
     * Primero, añade el libro `book1` con cantidad 1.
     * Luego, vuelve a añadir el mismo libro `book1` con cantidad 3.
     * Verifica que la respuesta sea una redirección a 'detallespedidos.index'.
     * Verifica que la sesión contenga el mensaje de éxito específico para actualización.
     * Obtiene el pedido pendiente y verifica que en la tabla 'detallespedidos'
     * la cantidad para ese libro y pedido sea 4 (1 + 3).
     * Verifica que solo exista una única línea de detalle para ese libro en el pedido.
     *
     * @test
     * @return void
     */
    public function adding_same_book_updates_quantity_in_cart(): void
    {
        // Arrange: Añadir el libro una vez.
        $this->actingAs($this->user)
             ->post(route('detallespedidos.store'), [
                 'libro_id' => $this->book1->id,
                 'cantidad' => 1,
                 'precio' => $this->book1->precio,
             ]);

        // Act: Añadir el mismo libro de nuevo.
        $response = $this->actingAs($this->user)
                         ->post(route('detallespedidos.store'), [
                             'libro_id' => $this->book1->id,
                             'cantidad' => 3, // Añadir 3 más.
                             'precio' => $this->book1->precio,
                         ]);

        // Assert: Verificar redirección, mensaje y estado de la BD.
        $response->assertRedirect(route('detallespedidos.index'));
        $response->assertSessionHas('success', 'Cantidad actualizada en el carrito.');

        $pedidoPendiente = Pedidos::where('cliente_id', $this->user->id)
                                  ->where('status', Pedidos::STATUS_PENDIENTE)
                                  ->first();
        // Verifica que la cantidad total es 4.
        $this->assertDatabaseHas('detallespedidos', [
            'pedido_id' => $pedidoPendiente->id,
            'libro_id' => $this->book1->id,
            'cantidad' => 4,
            'precio' => $this->book1->precio, // Verifica que el precio se mantuvo.
        ]);
        // Verifica que solo hay una línea de detalle.
        $this->assertDatabaseCount('detallespedidos', 1);
    }

    /**
     * Prueba que un usuario puede actualizar la cantidad de un ítem en el carrito.
     *
     * Crea un pedido pendiente y un detalle asociado con cantidad 2.
     * Simula una petición PUT a la ruta 'detallespedidos.update' para ese detalle,
     * enviando la nueva cantidad 5.
     * Verifica que la respuesta sea una redirección a 'detallespedidos.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que la cantidad del detalle en la base de datos se haya actualizado a 5.
     *
     * @test
     * @return void
     */
    public function user_can_update_item_quantity_in_cart(): void
    {
        // Arrange: Crear pedido y detalle.
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

        // Act: Realizar la petición PUT para actualizar la cantidad.
        $response = $this->actingAs($this->user)
                         ->put(route('detallespedidos.update', $detalle), [ // Usa el objeto $detalle.
                             'cantidad' => 5, // Nueva cantidad.
                         ]);

        // Assert: Verificar redirección, mensaje y estado de la BD.
        $response->assertRedirect(route('detallespedidos.index'));
        $response->assertSessionHas('success', 'Cantidad actualizada correctamente.');
        $this->assertDatabaseHas('detallespedidos', [
            'id' => $detalle->id,
            'cantidad' => 5,
        ]);
    }

     /**
      * Prueba que un usuario no puede actualizar un ítem del carrito de otro usuario.
      *
      * Crea otro usuario, un pedido pendiente y un detalle asociado a ese otro usuario.
      * Simula una petición PUT a la ruta 'detallespedidos.update' para el detalle del otro usuario,
      * actuando como el usuario principal (`$this->user`).
      * Verifica que la respuesta sea una redirección a 'detallespedidos.index'.
      * Verifica que la sesión contenga un mensaje de error específico.
      * Verifica que la cantidad del detalle original en la base de datos no haya cambiado.
      *
      * @test
      * @return void
      */
    public function user_cannot_update_item_from_another_user_cart(): void
    {
        // Arrange: Crear pedido y detalle para otro usuario.
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

        // Act: Intentar actualizar el detalle de otro usuario.
        $response = $this->actingAs($this->user)
                         ->put(route('detallespedidos.update', $otherDetalle), [
                             'cantidad' => 5,
                         ]);

        // Assert: Verificar redirección, mensaje de error y estado de la BD.
        $response->assertRedirect(route('detallespedidos.index'));
        $response->assertSessionHas('error', 'No se pudo actualizar el item.'); // Verifica mensaje de error.
        // Verifica que la cantidad no cambió.
        $this->assertDatabaseHas('detallespedidos', [
            'id' => $otherDetalle->id,
            'cantidad' => 1,
        ]);
    }

    /**
     * Prueba que un usuario puede eliminar un ítem de su carrito.
     *
     * Crea un pedido pendiente y un detalle asociado.
     * Simula una petición DELETE a la ruta 'detallespedidos.destroy' para ese detalle,
     * actuando como el usuario propietario.
     * Verifica que la respuesta sea una redirección a 'detallespedidos.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el detalle ya no exista en la base de datos.
     *
     * @test
     * @return void
     */
    public function user_can_remove_item_from_cart(): void
    {
        // Arrange: Crear pedido y detalle.
        $pedido = Pedidos::factory()->create([
            'cliente_id' => $this->user->id,
            'status' => Pedidos::STATUS_PENDIENTE,
        ]);
        $detalle = Detallespedidos::factory()->create([
            'pedido_id' => $pedido->id,
            'libro_id' => $this->book1->id,
            'cantidad' => 1,
        ]);

        // Act: Realizar la petición DELETE para eliminar el detalle.
        $response = $this->actingAs($this->user)
                         ->delete(route('detallespedidos.destroy', $detalle)); // Usa el objeto $detalle.

        // Assert: Verificar redirección, mensaje y estado de la BD.
        $response->assertRedirect(route('detallespedidos.index'));
        $response->assertSessionHas('success', 'Item eliminado del carrito.');
        $this->assertDatabaseMissing('detallespedidos', ['id' => $detalle->id]);
    }

    /**
     * Prueba que la actualización de un ítem del carrito falla con cantidad inválida.
     *
     * Crea un pedido pendiente y un detalle asociado.
     * Simula una petición PUT a 'detallespedidos.update' con cantidad 0.
     * Verifica que la sesión contenga errores para 'cantidad'.
     * Verifica que la cantidad en la BD no haya cambiado.
     * Simula otra petición PUT con cantidad -1.
     * Verifica que la sesión contenga errores para 'cantidad'.
     * Verifica que la cantidad en la BD no haya cambiado.
     *
     * @test
     * @return void
     */
    public function update_cart_item_fails_with_invalid_quantity(): void
    {
        // Arrange: Crear pedido y detalle.
        $pedido = Pedidos::factory()->create(['cliente_id' => $this->user->id, 'status' => Pedidos::STATUS_PENDIENTE]);
        $detalle = Detallespedidos::factory()->create(['pedido_id' => $pedido->id, 'libro_id' => $this->book1->id, 'cantidad' => 2]);

        // Act & Assert 1: Intentar actualizar con cantidad 0.
        $response = $this->actingAs($this->user)
                         ->put(route('detallespedidos.update', $detalle), ['cantidad' => 0]); // Cantidad inválida (min:1).
        $response->assertSessionHasErrors('cantidad');
        $this->assertDatabaseHas('detallespedidos', ['id' => $detalle->id, 'cantidad' => 2]); // Verifica que no cambió.

        // Act & Assert 2: Intentar actualizar con cantidad -1.
        $response = $this->actingAs($this->user)
                         ->put(route('detallespedidos.update', $detalle), ['cantidad' => -1]); // Cantidad inválida.
        $response->assertSessionHasErrors('cantidad');
        $this->assertDatabaseHas('detallespedidos', ['id' => $detalle->id, 'cantidad' => 2]); // Verifica que no cambió.
    }

     /**
      * Prueba que un usuario no puede eliminar un ítem del carrito de otro usuario.
      *
      * Crea otro usuario, un pedido pendiente y un detalle asociado a ese otro usuario.
      * Simula una petición DELETE a la ruta 'detallespedidos.destroy' para el detalle del otro usuario,
      * actuando como el usuario principal (`$this->user`).
      * Verifica que la respuesta sea una redirección a 'detallespedidos.index'.
      * Verifica que la sesión contenga un mensaje de error específico.
      * Verifica que el detalle original todavía exista en la base de datos.
      *
      * @test
      * @return void
      */
    public function user_cannot_remove_item_from_another_user_cart(): void
    {
        // Arrange: Crear pedido y detalle para otro usuario.
        $otherUser = User::factory()->create();
        $otherPedido = Pedidos::factory()->create(['cliente_id' => $otherUser->id, 'status' => Pedidos::STATUS_PENDIENTE]);
        $otherDetalle = Detallespedidos::factory()->create(['pedido_id' => $otherPedido->id, 'libro_id' => $this->book1->id]);

        // Act: Intentar eliminar el detalle de otro usuario.
        $response = $this->actingAs($this->user) // Logueado como $this->user.
                         ->delete(route('detallespedidos.destroy', $otherDetalle)); // Intentando borrar $otherDetalle.

        // Assert: Verificar redirección, mensaje de error y estado de la BD.
        $response->assertRedirect(route('detallespedidos.index'));
        $response->assertSessionHas('error', 'No se pudo eliminar el item.');
        $this->assertDatabaseHas('detallespedidos', ['id' => $otherDetalle->id]); // Verifica que no se borró.
    }

    /**
     * Prueba que un usuario no puede actualizar ni eliminar ítems de un pedido que no está pendiente.
     *
     * Crea un pedido completado y un detalle asociado para el usuario.
     * Intenta actualizar la cantidad del detalle. Verifica la redirección, el mensaje de error
     * y que la cantidad no cambió en la BD.
     * Intenta eliminar el detalle. Verifica la redirección, el mensaje de error
     * y que el detalle todavía existe en la BD.
     *
     * @test
     * @return void
     */
    public function user_cannot_update_or_delete_item_from_non_pending_order(): void
    {
        // Arrange: Crear un pedido completado y un detalle.
        $pedidoCompletado = Pedidos::factory()->completado()->create(['cliente_id' => $this->user->id]);
        $detalleCompletado = Detallespedidos::factory()->create(['pedido_id' => $pedidoCompletado->id, 'libro_id' => $this->book1->id, 'cantidad' => 1]);

        // Act & Assert (Update): Intentar actualizar cantidad.
        $responseUpdate = $this->actingAs($this->user)
                               ->put(route('detallespedidos.update', $detalleCompletado), ['cantidad' => 5]);
        $responseUpdate->assertRedirect(route('detallespedidos.index'));
        $responseUpdate->assertSessionHas('error', 'No se pudo actualizar el item.'); // Error esperado.
        $this->assertDatabaseHas('detallespedidos', ['id' => $detalleCompletado->id, 'cantidad' => 1]); // Verifica que no cambió.

        // Act & Assert (Delete): Intentar eliminar.
        $responseDelete = $this->actingAs($this->user)
                               ->delete(route('detallespedidos.destroy', $detalleCompletado));
        $responseDelete->assertRedirect(route('detallespedidos.index'));
        $responseDelete->assertSessionHas('error', 'No se pudo eliminar el item.'); // Error esperado.
        $this->assertDatabaseHas('detallespedidos', ['id' => $detalleCompletado->id]); // Verifica que no se borró.
    }
}
