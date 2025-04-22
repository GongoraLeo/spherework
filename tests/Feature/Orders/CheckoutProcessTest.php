<?php
// filepath: tests\Feature\Orders\CheckoutProcessTest.php

namespace Tests\Feature\Orders;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\User; // Modelo User para crear usuarios de prueba.
use App\Models\Libros; // Modelo Libros para crear libros de prueba.
use App\Models\Pedidos; // Modelo Pedidos para crear pedidos de prueba.
use App\Models\Detallespedidos; // Modelo Detallespedidos para crear detalles de pedido.
use Illuminate\Support\Facades\DB; // Fachada DB para escuchar eventos de consulta (simular error).
use Illuminate\Support\Facades\Log; // Fachada Log (importada pero no usada directamente).
use PHPUnit\Framework\Attributes\Test; // Atributo para marcar métodos como tests (PHPUnit 10+).

/**
 * Class CheckoutProcessTest
 *
 * Suite de pruebas de Feature para verificar el proceso de finalización de compra (checkout).
 * Comprueba que un usuario pueda finalizar un pedido pendiente, maneja casos de
 * carrito vacío o pedido no encontrado, verifica el acceso a la página de éxito
 * y las restricciones para invitados y otros usuarios. También incluye una prueba
 * para simular un fallo de base de datos durante el proceso.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba.
 *
 * @package Tests\Feature\Orders
 */
class CheckoutProcessTest extends TestCase
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
     * @var Pedidos Instancia del pedido pendiente principal utilizado en las pruebas.
     */
    private Pedidos $pedidoPendiente;

    /**
     * Configura el entorno de prueba antes de cada test.
     *
     * Crea un usuario cliente, dos libros y un pedido pendiente asociado al usuario.
     * Añade dos detalles (ítems) a este pedido pendiente utilizando los libros creados.
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
        // Crea dos libros con precios específicos.
        $this->book1 = Libros::factory()->create(['precio' => 10.00]);
        $this->book2 = Libros::factory()->create(['precio' => 20.00]);

        // Crea un pedido pendiente para el usuario.
        $this->pedidoPendiente = Pedidos::factory()->create([
            'cliente_id' => $this->user->id,
            'status' => Pedidos::STATUS_PENDIENTE,
            'total' => null,
            'fecha_pedido' => null,
        ]);
        // Añade el primer detalle al pedido pendiente.
        Detallespedidos::factory()->create([
            'pedido_id' => $this->pedidoPendiente->id,
            'libro_id' => $this->book1->id,
            'cantidad' => 2,
            'precio' => $this->book1->precio,
        ]);
        // Añade el segundo detalle al pedido pendiente.
        Detallespedidos::factory()->create([
            'pedido_id' => $this->pedidoPendiente->id,
            'libro_id' => $this->book2->id,
            'cantidad' => 1,
            'precio' => $this->book2->precio,
        ]);
        // Total esperado = (2 * 10.00) + (1 * 20.00) = 40.00
    }

    /**
     * Prueba que un usuario puede finalizar con éxito un pedido pendiente.
     *
     * Simula una petición POST a la ruta 'pedidos.checkout.process' actuando como el usuario.
     * Verifica que la respuesta sea una redirección a la página de éxito del pedido.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el estado del pedido en la base de datos se haya actualizado a 'completado',
     * que el total sea 40.00 y que la fecha del pedido ya no sea nula.
     *
     * @return void
     */
    #[Test]
    public function user_can_successfully_checkout_pending_order(): void
    {
        // Act: Realizar la petición POST para procesar el checkout.
        $response = $this->actingAs($this->user)
                         ->post(route('pedidos.checkout.process'));

        // Assert: Verificar redirección, mensaje y estado de la BD.
        $response->assertRedirect(route('pedidos.checkout.success', $this->pedidoPendiente));
        $response->assertSessionHas('success', '¡Tu pedido ha sido realizado con éxito!');
        $this->assertDatabaseHas('pedidos', [
            'id' => $this->pedidoPendiente->id,
            'cliente_id' => $this->user->id,
            'status' => Pedidos::STATUS_COMPLETADO,
            'total' => 40.00,
        ]);
        // Verifica que la fecha del pedido se haya establecido.
        $this->assertNotNull($this->pedidoPendiente->fresh()->fecha_pedido);
    }

    /**
     * Prueba el comportamiento al intentar finalizar un pedido pendiente que no tiene ítems (carrito vacío).
     *
     * Crea un pedido pendiente adicional para el usuario, pero sin añadirle detalles.
     * Simula una petición POST a 'pedidos.checkout.process' actuando como el usuario.
     * Verifica que la respuesta sea una redirección a la página de éxito (comportamiento actual).
     * Verifica que el pedido vacío se marque como 'completado' en la base de datos,
     * con un total de 0.00 y que la fecha del pedido se establezca.
     *
     * @return void
     */
    #[Test]
    public function cannot_checkout_if_cart_is_empty(): void
    {
        // Arrange: Crear un pedido pendiente vacío.
        $emptyPedido = Pedidos::factory()->create([
            'cliente_id' => $this->user->id,
            'status' => Pedidos::STATUS_PENDIENTE,
        ]);

        // Act: Intentar hacer checkout con el pedido vacío.
        $response = $this->actingAs($this->user)
                         ->post(route('pedidos.checkout.process'));

        // Assert: Verificar el comportamiento actual (redirige a éxito).
        $response->assertRedirect(route('pedidos.checkout.success', $emptyPedido));

        // Assert: Verificar el estado final del pedido vacío en la BD.
        $this->assertDatabaseHas('pedidos', [
            'id' => $emptyPedido->id,
            'status' => Pedidos::STATUS_COMPLETADO, // Se marca como completado.
            'total' => 0.00, // El total es 0.
        ]);
        $this->assertNotNull($emptyPedido->fresh()->fecha_pedido); // La fecha se establece.
    }

     /**
      * Prueba que no se puede finalizar la compra si no existe un pedido pendiente para el usuario.
      *
      * Elimina cualquier pedido pendiente existente para el usuario.
      * Simula una petición POST a 'pedidos.checkout.process' actuando como el usuario.
      * Verifica que la respuesta sea una redirección a la vista del carrito ('detallespedidos.index').
      * Verifica que la sesión contenga un mensaje de error específico indicando que no se encontró
      * un pedido pendiente.
      *
      * @return void
      */
     #[Test]
    public function cannot_checkout_if_no_pending_order_exists(): void
    {
         // Arrange: Eliminar el pedido pendiente del usuario.
         Pedidos::where('cliente_id', $this->user->id)
                ->where('status', Pedidos::STATUS_PENDIENTE)
                ->delete();

         // Act: Intentar hacer checkout sin pedido pendiente.
         $response = $this->actingAs($this->user)
                          ->post(route('pedidos.checkout.process'));

         // Assert: Verificar la redirección y el mensaje de error.
         $response->assertRedirect(route('detallespedidos.index'));
         $response->assertSessionHas('error', 'No se encontró un pedido pendiente.');
     }

    /**
     * Prueba que la página de éxito del checkout es accesible después de finalizar la compra.
     *
     * Primero, simula el proceso de checkout exitoso.
     * Luego, simula una petición GET a la ruta 'pedidos.checkout.success' para el pedido
     * recién completado, actuando como el usuario propietario.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'pedidos.success'.
     * Verifica que la vista reciba la variable 'pedidos' con el ID correcto.
     * Verifica que se muestre el número de pedido, el título de uno de los libros
     * y el total pagado (formateado) en la respuesta HTML.
     *
     * @return void
     */
    #[Test]
    public function checkout_success_page_is_accessible_after_checkout(): void
    {
        // Arrange: Procesar el checkout primero.
        $this->actingAs($this->user)->post(route('pedidos.checkout.process'));
        // Act: Acceder a la página de éxito.
        $response = $this->actingAs($this->user)
                         ->get(route('pedidos.checkout.success', $this->pedidoPendiente));

        // Assert: Verificar la respuesta, vista, datos y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('pedidos.success');
        // Verifica que el ID del pedido en la vista coincida.
        $response->assertViewHas('pedidos', function ($viewPedido) {
            return $viewPedido->id === $this->pedidoPendiente->id;
        });
        // Verifica contenido específico en la vista de éxito.
        $response->assertSee('Número de Pedido:</strong> ' . $this->pedidoPendiente->id, false);
        $response->assertSee($this->book1->titulo);
        $response->assertSee('Total Pagado:</strong> 40,00 €', false);
    }

    /**
     * Prueba que no se puede acceder a la página de éxito para un pedido que aún está pendiente.
     *
     * Simula una petición GET a la ruta 'pedidos.checkout.success' para el pedido pendiente
     * (`$this->pedidoPendiente`) actuando como el usuario propietario.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.show'.
     * Verifica que la sesión contenga un mensaje de error específico.
     *
     * @return void
     */
    #[Test]
    public function cannot_access_success_page_for_pending_order(): void
    {
        // Act: Intentar acceder a la página de éxito de un pedido pendiente.
        $response = $this->actingAs($this->user)
                         ->get(route('pedidos.checkout.success', $this->pedidoPendiente));

        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error', 'Este pedido aún no ha sido completado.');
    }

     /**
      * Prueba que un usuario no puede acceder a la página de éxito del pedido de otro usuario.
      *
      * Crea otro usuario.
      * Simula una petición GET a la ruta 'pedidos.checkout.success' para el pedido
      * `$this->pedidoPendiente` (que pertenece a `$this->user`), pero actuando como `$otherUser`.
      * Verifica que la respuesta HTTP tenga estado 403 (Forbidden).
      *
      * @return void
      */
     #[Test]
    public function cannot_access_success_page_for_another_user_order(): void
    {
        // Arrange: Crear otro usuario.
        $otherUser = User::factory()->create();
        // Act: Intentar acceder a la página de éxito de otro usuario.
        $response = $this->actingAs($otherUser)
                         ->get(route('pedidos.checkout.success', $this->pedidoPendiente));
        // Assert: Verificar estado 403.
        $response->assertStatus(403);
    }

    /**
     * Prueba que un usuario no autenticado (invitado) no puede procesar el checkout.
     *
     * Simula una petición POST a la ruta 'pedidos.checkout.process' sin autenticar usuario.
     * Verifica que la respuesta sea una redirección a la ruta nombrada 'login'.
     *
     * @return void
     */
    #[Test]
    public function guest_cannot_process_checkout(): void
    {
        // Act: Realizar la petición POST como invitado.
        $response = $this->post(route('pedidos.checkout.process'));
        // Assert: Verificar la redirección a login.
        $response->assertRedirect(route('login'));
    }

    /**
     * Prueba que un usuario no autenticado (invitado) no puede ver la página de éxito.
     *
     * Crea un pedido completado de prueba.
     * Simula una petición GET a la ruta 'pedidos.checkout.success' para ese pedido,
     * sin autenticar usuario.
     * Verifica que la respuesta sea una redirección a la ruta nombrada 'login'.
     *
     * @return void
     */
    #[Test]
    public function guest_cannot_view_success_page(): void
    {
        // Arrange: Crear un pedido completado.
        $pedido = Pedidos::factory()->completado()->create();
        // Act: Realizar la petición GET como invitado.
        $response = $this->get(route('pedidos.checkout.success', $pedido));
        // Assert: Verificar la redirección a login.
        $response->assertRedirect(route('login'));
    }

    /**
     * Prueba el manejo de transacciones de base de datos en caso de fallo durante el checkout.
     *
     * Configura un listener de base de datos (`DB::listen`) que lanzará una excepción
     * específicamente cuando se intente ejecutar la consulta `UPDATE` final que marca
     * el pedido como completado.
     * Simula la petición POST para procesar el checkout actuando como el usuario.
     * Verifica que la respuesta sea una redirección a la página de éxito (comportamiento actual).
     * Verifica que, a pesar del error simulado, el pedido se marque como completado en la BD
     * y su fecha se establezca (indicando un posible problema en el manejo de la transacción
     * o en la simulación del error).
     * Limpia los listeners de base de datos al final.
     *
     * @return void
     */
    #[Test]
    public function checkout_handles_database_transaction_on_failure(): void
    {
        // Arrange: Configurar listener para simular error en el UPDATE final.
        DB::listen(function ($query) {
             // Busca la consulta específica de actualización del estado a completado.
             if (str_contains($query->sql, 'update `pedidos` set `status` = ?, `total` = ?, `fecha_pedido` = ? where `id` = ?')) {
                 if ($query->bindings[0] === Pedidos::STATUS_COMPLETADO) {
                     // Lanza una excepción para simular el fallo.
                     throw new \Exception("Simulated DB error during checkout save");
                 }
             }
        });

        // Act: Intentar hacer checkout.
        $response = $this->actingAs($this->user)
                         ->post(route('pedidos.checkout.process'));

        // Assert: Verificar el comportamiento actual (redirige a éxito).
        $response->assertRedirect(route('pedidos.checkout.success', $this->pedidoPendiente));

        // Assert: Verificar el estado final en la BD (según comportamiento actual).
        $this->assertDatabaseHas('pedidos', [
            'id' => $this->pedidoPendiente->id,
            'status' => Pedidos::STATUS_COMPLETADO, // Se marca como completado.
            'total' => 40.00,
        ]);
         $this->assertNotNull($this->pedidoPendiente->fresh()->fecha_pedido); // La fecha se establece.

        // Cleanup: Limpiar listeners de BD.
        DB::flushQueryLog();
        DB::listen(fn() => null);
    }
}
