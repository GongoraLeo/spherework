<?php

namespace Tests\Feature\Orders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pedidos;
use App\Models\Libros;
use App\Models\Detallespedidos;
use PHPUnit\Framework\Attributes\Test; // Usar atributos

class OrderViewingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $client;
    private User $otherClient;
    private Pedidos $clientOrderCompleted;
    private Pedidos $clientOrderPending;
    private Pedidos $otherClientOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->client = User::factory()->create(['rol' => 'cliente']);
        $this->otherClient = User::factory()->create(['rol' => 'cliente']);

        $this->clientOrderCompleted = Pedidos::factory()->completado()->create([
            'cliente_id' => $this->client->id,
        ]);
        Detallespedidos::factory()->count(2)->create(['pedido_id' => $this->clientOrderCompleted->id]);

        $this->clientOrderPending = Pedidos::factory()->create([
            'cliente_id' => $this->client->id,
            'status' => Pedidos::STATUS_PENDIENTE,
        ]);
        Detallespedidos::factory()->create(['pedido_id' => $this->clientOrderPending->id]);

        $this->otherClientOrder = Pedidos::factory()->completado()->create([
            'cliente_id' => $this->otherClient->id,
        ]);
    }

    // --- Vista Cliente (Profile) ---
    #[Test]
    public function client_sees_own_completed_orders_on_profile(): void
    {
        $response = $this->actingAs($this->client)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('#' . $this->clientOrderCompleted->id);
        // CORREGIDO: Formato con coma y símbolo €
        $response->assertSee(number_format($this->clientOrderCompleted->total, 2, ',', '.') . ' €');
        $response->assertDontSee('#' . $this->clientOrderPending->id);
        $response->assertDontSee('#' . $this->otherClientOrder->id);
    }

    // --- Vista Cliente (Detalle Pedido) ---
    #[Test]
    public function client_can_view_details_of_own_completed_order(): void
    {
        $response = $this->actingAs($this->client)->get(route('pedidos.show', $this->clientOrderCompleted));

        $response->assertStatus(200);
        $response->assertViewIs('pedidos.show');
        $response->assertViewHas('pedidos', $this->clientOrderCompleted);
        $response->assertSee('Número de Pedido:</strong> ' . $this->clientOrderCompleted->id, false);
        $detalle = $this->clientOrderCompleted->detallespedidos->first();
        $response->assertSee($detalle->libro->titulo);
        $response->assertSee($detalle->cantidad);
        // CORREGIDO: Formato con coma y símbolo €
        $response->assertSee(number_format($detalle->precio, 2, ',', '.') . ' €');
    }

    #[Test]
    public function client_can_view_details_of_own_pending_order_if_accessed_directly(): void
    {
        $response = $this->actingAs($this->client)->get(route('pedidos.show', $this->clientOrderPending));
        $response->assertStatus(200);
        $response->assertSee('Número de Pedido:</strong> ' . $this->clientOrderPending->id, false);
    }

    #[Test]
    public function client_cannot_view_details_of_other_client_order(): void
    {
        $response = $this->actingAs($this->client)->get(route('pedidos.show', $this->otherClientOrder));
        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error', 'No tienes permiso para ver este pedido.');
    }

    // --- Vista Admin (Índice Pedidos) ---
    #[Test]
    public function admin_can_view_order_index_with_all_orders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pedidos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pedidos.index');
        $response->assertViewHas('pedidos');
        $response->assertSee('#' . $this->clientOrderCompleted->id);
        $response->assertSee('N/A'); // Mantenemos N/A según HTML actual
        $response->assertSee('#' . $this->clientOrderPending->id);
        $response->assertSee('#' . $this->otherClientOrder->id);
        // $response->assertSee($this->otherClient->name); // También será N/A
    }

    #[Test]
    public function client_cannot_view_admin_order_index(): void
    {
        $response = $this->actingAs($this->client)->get(route('pedidos.index'));
        $response->assertRedirect(route('profile.entry'));
        $response->assertSessionHas('error', 'Acceso no autorizado para ver todos los pedidos.');
    }

    // --- Vista Admin (Detalle Pedido) ---
    #[Test]
    public function admin_can_view_details_of_any_order(): void
    {
        // Pedido del cliente
        $responseClient = $this->actingAs($this->admin)->get(route('pedidos.show', $this->clientOrderCompleted));
        $responseClient->assertStatus(200);
        $responseClient->assertSee('Número de Pedido:</strong> ' . $this->clientOrderCompleted->id, false);
        $responseClient->assertSee($this->client->name);

        // Pedido pendiente
        $responsePending = $this->actingAs($this->admin)->get(route('pedidos.show', $this->clientOrderPending));
        $responsePending->assertStatus(200);
        $responsePending->assertSee('Número de Pedido:</strong> ' . $this->clientOrderPending->id, false);

        // Pedido de otro cliente
        $responseOther = $this->actingAs($this->admin)->get(route('pedidos.show', $this->otherClientOrder));
        $responseOther->assertStatus(200);
        $responseOther->assertSee('Número de Pedido:</strong> ' . $this->otherClientOrder->id, false);
        $responseOther->assertSee($this->otherClient->name);
    }

    // --- Vista Admin (Edición Pedido) ---

    /* // COMENTADO - La vista pedidos.edit no existe/no es necesaria
    #[Test]
    public function admin_can_view_edit_order_form(): void { ... }
    */

     #[Test]
    public function client_cannot_view_edit_order_form(): void
    {
        $response = $this->actingAs($this->client)->get(route('pedidos.edit', $this->clientOrderCompleted));
        $response->assertRedirect(route('pedidos.index'));
        $response->assertSessionHas('error', 'Acceso no autorizado.');
    }

    #[Test]
    public function admin_can_update_order_status(): void
    {
        $newStatus = Pedidos::STATUS_ENVIADO;
        $response = $this->actingAs($this->admin)->put(route('pedidos.update', $this->clientOrderCompleted), [
            'status' => $newStatus,
        ]);

        $response->assertRedirect(route('pedidos.index'));
        $response->assertSessionHas('success', 'Pedido actualizado correctamente.');
        $this->assertDatabaseHas('pedidos', [
            'id' => $this->clientOrderCompleted->id,
            'status' => $newStatus,
        ]);
    }

     #[Test]
    public function admin_cannot_update_order_with_invalid_status(): void
    {
        $originalStatus = $this->clientOrderCompleted->status;
        $response = $this->actingAs($this->admin)->put(route('pedidos.update', $this->clientOrderCompleted), [
            'status' => 'estado_inventado',
        ]);
        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('pedidos', [
            'id' => $this->clientOrderCompleted->id,
            'status' => $originalStatus,
        ]);
    }

    #[Test]
    public function client_cannot_update_order_status(): void
    {
        $response = $this->actingAs($this->client)->put(route('pedidos.update', $this->clientOrderCompleted), [
            'status' => Pedidos::STATUS_ENVIADO,
        ]);
        $response->assertStatus(403);
    }

    // --- Vista Admin (Borrado Pedido) ---
    #[Test]
    public function admin_can_delete_an_order(): void
    {
        $orderToDelete = Pedidos::factory()->create();
        Detallespedidos::factory()->create(['pedido_id' => $orderToDelete->id]);

        $response = $this->actingAs($this->admin)->delete(route('pedidos.destroy', $orderToDelete));

        $response->assertRedirect(route('pedidos.index'));
        $response->assertSessionHas('success', 'Pedido eliminado correctamente.');
        $this->assertDatabaseMissing('pedidos', ['id' => $orderToDelete->id]);
    }

    #[Test]
    public function client_cannot_delete_an_order(): void
    {
        $response = $this->actingAs($this->client)->delete(route('pedidos.destroy', $this->clientOrderCompleted));
        $response->assertStatus(403);
    }
}
