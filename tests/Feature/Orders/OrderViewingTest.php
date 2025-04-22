<?php
// filepath: tests\Feature\Orders\OrderViewingTest.php

namespace Tests\Feature\Orders;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\User; // Modelo User para crear usuarios de prueba.
use App\Models\Pedidos; // Modelo Pedidos para crear pedidos de prueba.
use App\Models\Libros; // Modelo Libros (importado pero no usado directamente en aserciones).
use App\Models\Detallespedidos; // Modelo Detallespedidos para crear detalles de pedido.
use PHPUnit\Framework\Attributes\Test; // Atributo para marcar métodos como tests (PHPUnit 10+).

/**
 * Class OrderViewingTest
 *
 * Suite de pruebas de Feature para verificar la visualización y gestión básica de pedidos
 * desde las perspectivas del cliente y del administrador. Comprueba el acceso a las
 * diferentes vistas (perfil de cliente, detalle de pedido, índice de admin, edición de admin),
 * la correcta visualización de datos y las restricciones de autorización.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba.
 *
 * @package Tests\Feature\Orders
 */
class OrderViewingTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase,
     * asegurando que los tests no interfieran entre sí.
     */
    use RefreshDatabase;

    /**
     * @var User Instancia del usuario administrador utilizada en las pruebas.
     */
    private User $admin;
    /**
     * @var User Instancia del primer usuario cliente utilizada en las pruebas.
     */
    private User $client;
    /**
     * @var User Instancia de un segundo usuario cliente utilizada para pruebas de acceso cruzado.
     */
    private User $otherClient;
    /**
     * @var Pedidos Instancia de un pedido completado asociado a `$client`.
     */
    private Pedidos $clientOrderCompleted;
    /**
     * @var Pedidos Instancia de un pedido pendiente asociado a `$client`.
     */
    private Pedidos $clientOrderPending;
    /**
     * @var Pedidos Instancia de un pedido completado asociado a `$otherClient`.
     */
    private Pedidos $otherClientOrder;

    /**
     * Configura el entorno de prueba antes de cada test.
     *
     * Crea un usuario administrador, dos usuarios clientes y tres pedidos:
     * uno completado y uno pendiente para el primer cliente, y uno completado
     * para el segundo cliente. Añade detalles a los pedidos completado y pendiente
     * del primer cliente. Estas instancias se almacenan en propiedades de la clase
     * para ser utilizadas en los métodos de prueba. Llama al método `setUp`
     * de la clase padre.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp(); // Llama al método setUp de la clase padre.
        // Crea usuarios de prueba.
        $this->admin = User::factory()->admin()->create();
        $this->client = User::factory()->create(['rol' => 'cliente']);
        $this->otherClient = User::factory()->create(['rol' => 'cliente']);

        // Crea un pedido completado para el cliente principal con detalles.
        $this->clientOrderCompleted = Pedidos::factory()->completado()->create([
            'cliente_id' => $this->client->id,
        ]);
        Detallespedidos::factory()->count(2)->create(['pedido_id' => $this->clientOrderCompleted->id]);

        // Crea un pedido pendiente para el cliente principal con un detalle.
        $this->clientOrderPending = Pedidos::factory()->create([
            'cliente_id' => $this->client->id,
            'status' => Pedidos::STATUS_PENDIENTE,
        ]);
        Detallespedidos::factory()->create(['pedido_id' => $this->clientOrderPending->id]);

        // Crea un pedido completado para el otro cliente.
        $this->otherClientOrder = Pedidos::factory()->completado()->create([
            'cliente_id' => $this->otherClient->id,
        ]);
    }

    // --- Vista Cliente (Profile) ---

    /**
     * Prueba que un cliente ve sus propios pedidos completados en su perfil.
     *
     * Simula una petición GET a la ruta 'profile.show' actuando como el cliente.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se muestre el ID y el total (formateado) del pedido completado del cliente.
     * Verifica que no se muestren ni el pedido pendiente del cliente ni el pedido del otro cliente.
     *
     * @return void
     */
    #[Test]
    public function client_sees_own_completed_orders_on_profile(): void
    {
        // Act: Acceder al perfil del cliente.
        $response = $this->actingAs($this->client)->get(route('profile.show'));

        // Assert: Verificar la respuesta y el contenido específico de los pedidos.
        $response->assertStatus(200);
        $response->assertSee('#' . $this->clientOrderCompleted->id); // Ve su pedido completado.
        // Verifica el total formateado.
        $response->assertSee(number_format($this->clientOrderCompleted->total, 2, ',', '.') . ' €');
        $response->assertDontSee('#' . $this->clientOrderPending->id); // No ve su pedido pendiente.
        $response->assertDontSee('#' . $this->otherClientOrder->id); // No ve el pedido de otro cliente.
    }

    // --- Vista Cliente (Detalle Pedido) ---

    /**
     * Prueba que un cliente puede ver los detalles de su propio pedido completado.
     *
     * Simula una petición GET a la ruta 'pedidos.show' para el pedido completado del cliente,
     * actuando como el propio cliente.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'pedidos.show'.
     * Verifica que la vista reciba la variable 'pedidos' con el pedido correcto.
     * Verifica que se muestre el número de pedido.
     * Obtiene el primer detalle del pedido y verifica que se muestren el título del libro,
     * la cantidad y el precio (formateado).
     *
     * @return void
     */
    #[Test]
    public function client_can_view_details_of_own_completed_order(): void
    {
        // Act: Acceder a los detalles del pedido completado propio.
        $response = $this->actingAs($this->client)->get(route('pedidos.show', $this->clientOrderCompleted));

        // Assert: Verificar la respuesta, vista, datos y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('pedidos.show');
        $response->assertViewHas('pedidos', $this->clientOrderCompleted);
        $response->assertSee('Número de Pedido:</strong> ' . $this->clientOrderCompleted->id, false);
        $detalle = $this->clientOrderCompleted->detallespedidos->first(); // Obtiene un detalle.
        $response->assertSee($detalle->libro->titulo); // Verifica título del libro.
        $response->assertSee($detalle->cantidad); // Verifica cantidad.
        $response->assertSee(number_format($detalle->precio, 2, ',', '.') . ' €'); // Verifica precio formateado.
    }

    /**
     * Prueba que un cliente puede ver los detalles de su propio pedido pendiente si accede directamente.
     *
     * Simula una petición GET a la ruta 'pedidos.show' para el pedido pendiente del cliente,
     * actuando como el propio cliente.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se muestre el número de pedido.
     *
     * @return void
     */
    #[Test]
    public function client_can_view_details_of_own_pending_order_if_accessed_directly(): void
    {
        // Act: Acceder a los detalles del pedido pendiente propio.
        $response = $this->actingAs($this->client)->get(route('pedidos.show', $this->clientOrderPending));
        // Assert: Verificar la respuesta y contenido básico.
        $response->assertStatus(200);
        $response->assertSee('Número de Pedido:</strong> ' . $this->clientOrderPending->id, false);
    }

    /**
     * Prueba que un cliente no puede ver los detalles del pedido de otro cliente.
     *
     * Simula una petición GET a la ruta 'pedidos.show' para el pedido del otro cliente,
     * actuando como el primer cliente.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.show'.
     * Verifica que la sesión contenga un mensaje de error específico.
     *
     * @return void
     */
    #[Test]
    public function client_cannot_view_details_of_other_client_order(): void
    {
        // Act: Intentar acceder al pedido de otro cliente.
        $response = $this->actingAs($this->client)->get(route('pedidos.show', $this->otherClientOrder));
        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error', 'No tienes permiso para ver este pedido.');
    }

    // --- Vista Admin (Índice Pedidos) ---

    /**
     * Prueba que un administrador puede ver el índice de pedidos con todos los pedidos.
     *
     * Simula una petición GET a la ruta 'pedidos.index' actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'pedidos.index'.
     * Verifica que la vista reciba la variable 'pedidos'.
     * Verifica que se muestren los IDs de los tres pedidos creados (completado, pendiente, otro cliente).
     * Verifica que se muestre 'N/A' (según el HTML esperado para el nombre del cliente en esta vista).
     *
     * @return void
     */
    #[Test]
    public function admin_can_view_order_index_with_all_orders(): void
    {
        // Act: Acceder al índice de pedidos como admin.
        $response = $this->actingAs($this->admin)->get(route('pedidos.index'));

        // Assert: Verificar la respuesta, vista y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('pedidos.index');
        $response->assertViewHas('pedidos');
        $response->assertSee('#' . $this->clientOrderCompleted->id);
        $response->assertSee('N/A'); // Verifica el placeholder del nombre del cliente.
        $response->assertSee('#' . $this->clientOrderPending->id);
        $response->assertSee('#' . $this->otherClientOrder->id);
    }

    /**
     * Prueba que un cliente no puede ver el índice de pedidos del administrador.
     *
     * Simula una petición GET a la ruta 'pedidos.index' actuando como cliente.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.entry'.
     * Verifica que la sesión contenga un mensaje de error específico.
     *
     * @return void
     */
    #[Test]
    public function client_cannot_view_admin_order_index(): void
    {
        // Act: Intentar acceder al índice de pedidos como cliente.
        $response = $this->actingAs($this->client)->get(route('pedidos.index'));
        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('profile.entry'));
        $response->assertSessionHas('error', 'Acceso no autorizado para ver todos los pedidos.');
    }

    // --- Vista Admin (Detalle Pedido) ---

    /**
     * Prueba que un administrador puede ver los detalles de cualquier pedido (cliente, pendiente, otro cliente).
     *
     * Simula peticiones GET a la ruta 'pedidos.show' para cada uno de los tres pedidos creados,
     * actuando como administrador.
     * Para cada petición, verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se muestre el número de pedido correspondiente.
     * Para los pedidos completados, verifica que se muestre el nombre del cliente asociado.
     *
     * @return void
     */
    #[Test]
    public function admin_can_view_details_of_any_order(): void
    {
        // Act & Assert: Pedido completado del cliente principal.
        $responseClient = $this->actingAs($this->admin)->get(route('pedidos.show', $this->clientOrderCompleted));
        $responseClient->assertStatus(200);
        $responseClient->assertSee('Número de Pedido:</strong> ' . $this->clientOrderCompleted->id, false);
        $responseClient->assertSee($this->client->name);

        // Act & Assert: Pedido pendiente del cliente principal.
        $responsePending = $this->actingAs($this->admin)->get(route('pedidos.show', $this->clientOrderPending));
        $responsePending->assertStatus(200);
        $responsePending->assertSee('Número de Pedido:</strong> ' . $this->clientOrderPending->id, false);

        // Act & Assert: Pedido completado del otro cliente.
        $responseOther = $this->actingAs($this->admin)->get(route('pedidos.show', $this->otherClientOrder));
        $responseOther->assertStatus(200);
        $responseOther->assertSee('Número de Pedido:</strong> ' . $this->otherClientOrder->id, false);
        $responseOther->assertSee($this->otherClient->name);
    }

    // --- Vista Admin (Edición Pedido) ---

    /**
     * Prueba que un cliente no puede ver el formulario de edición de pedidos del admin.
     *
     * Simula una petición GET a la ruta 'pedidos.edit' para un pedido, actuando como cliente.
     * Verifica que la respuesta sea una redirección a la ruta 'pedidos.index'.
     * Verifica que la sesión contenga un mensaje de error específico.
     *
     * @return void
     */
     #[Test]
    public function client_cannot_view_edit_order_form(): void
    {
        // Act: Intentar acceder al formulario de edición como cliente.
        $response = $this->actingAs($this->client)->get(route('pedidos.edit', $this->clientOrderCompleted));
        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('pedidos.index'));
        $response->assertSessionHas('error', 'Acceso no autorizado.');
    }

    /**
     * Prueba que un administrador puede actualizar el estado de un pedido.
     *
     * Define un nuevo estado ('enviado').
     * Simula una petición PUT a la ruta 'pedidos.update' para el pedido completado del cliente,
     * actuando como administrador y enviando el nuevo estado.
     * Verifica que la respuesta sea una redirección a la ruta 'pedidos.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el estado del pedido en la base de datos se haya actualizado al nuevo estado.
     *
     * @return void
     */
    #[Test]
    public function admin_can_update_order_status(): void
    {
        // Arrange: Definir nuevo estado.
        $newStatus = Pedidos::STATUS_ENVIADO;
        // Act: Realizar la petición PUT para actualizar el estado.
        $response = $this->actingAs($this->admin)->put(route('pedidos.update', $this->clientOrderCompleted), [
            'status' => $newStatus,
        ]);

        // Assert: Verificar redirección, mensaje y estado de la BD.
        $response->assertRedirect(route('pedidos.index'));
        $response->assertSessionHas('success', 'Pedido actualizado correctamente.');
        $this->assertDatabaseHas('pedidos', [
            'id' => $this->clientOrderCompleted->id,
            'status' => $newStatus,
        ]);
    }

     /**
      * Prueba que un administrador no puede actualizar un pedido con un estado inválido.
      *
      * Guarda el estado original del pedido.
      * Simula una petición PUT a la ruta 'pedidos.update' para el pedido, actuando como administrador
      * y enviando un estado inválido ('estado_inventado').
      * Verifica que la sesión contenga errores de validación para el campo 'status'.
      * Verifica que el estado del pedido en la base de datos no haya cambiado (conserve el original).
      *
      * @return void
      */
     #[Test]
    public function admin_cannot_update_order_with_invalid_status(): void
    {
        // Arrange: Guardar estado original.
        $originalStatus = $this->clientOrderCompleted->status;
        // Act: Intentar actualizar con estado inválido.
        $response = $this->actingAs($this->admin)->put(route('pedidos.update', $this->clientOrderCompleted), [
            'status' => 'estado_inventado',
        ]);
        // Assert: Verificar errores de sesión y estado de la BD.
        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('pedidos', [
            'id' => $this->clientOrderCompleted->id,
            'status' => $originalStatus,
        ]);
    }

    /**
     * Prueba que un cliente no puede actualizar el estado de un pedido.
     *
     * Simula una petición PUT a la ruta 'pedidos.update' para un pedido, actuando como cliente
     * e intentando cambiar el estado.
     * Verifica que la respuesta HTTP tenga estado 403 (Forbidden).
     *
     * @return void
     */
    #[Test]
    public function client_cannot_update_order_status(): void
    {
        // Act: Intentar actualizar el estado como cliente.
        $response = $this->actingAs($this->client)->put(route('pedidos.update', $this->clientOrderCompleted), [
            'status' => Pedidos::STATUS_ENVIADO,
        ]);
        // Assert: Verificar estado 403.
        $response->assertStatus(403);
    }

    // --- Vista Admin (Borrado Pedido) ---

    /**
     * Prueba que un administrador puede eliminar un pedido.
     *
     * Crea un nuevo pedido y un detalle asociado para eliminar.
     * Simula una petición DELETE a la ruta 'pedidos.destroy' para ese pedido, actuando como administrador.
     * Verifica que la respuesta sea una redirección a la ruta 'pedidos.index'.
     * Verifica que la sesión contenga un mensaje de éxito específico.
     * Verifica que el pedido ya no exista en la base de datos.
     *
     * @return void
     */
    #[Test]
    public function admin_can_delete_an_order(): void
    {
        // Arrange: Crear un pedido para eliminar.
        $orderToDelete = Pedidos::factory()->create();
        Detallespedidos::factory()->create(['pedido_id' => $orderToDelete->id]);

        // Act: Realizar la petición DELETE como administrador.
        $response = $this->actingAs($this->admin)->delete(route('pedidos.destroy', $orderToDelete));

        // Assert: Verificar redirección, mensaje y estado de la BD.
        $response->assertRedirect(route('pedidos.index'));
        $response->assertSessionHas('success', 'Pedido eliminado correctamente.');
        $this->assertDatabaseMissing('pedidos', ['id' => $orderToDelete->id]);
    }

    /**
     * Prueba que un cliente no puede eliminar un pedido.
     *
     * Simula una petición DELETE a la ruta 'pedidos.destroy' para un pedido, actuando como cliente.
     * Verifica que la respuesta HTTP tenga estado 403 (Forbidden).
     *
     * @return void
     */
    #[Test]
    public function client_cannot_delete_an_order(): void
    {
        // Act: Intentar eliminar un pedido como cliente.
        $response = $this->actingAs($this->client)->delete(route('pedidos.destroy', $this->clientOrderCompleted));
        // Assert: Verificar estado 403.
        $response->assertStatus(403);
    }
}
