<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pedidos; // Para probar la vista show
use App\Models\Comentarios; // Para probar la vista show
use PHPUnit\Framework\Attributes\Test; // Añadir atributo

/**
 * @group admin
 */

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $client1;
    private User $client2;

    protected function setUp(): void
    {
        parent::setUp();
        // Asegurar explícitamente el rol del admin
        $this->admin = User::factory()->create(['rol' => 'administrador']);
        $this->client1 = User::factory()->create(['rol' => 'cliente', 'name' => 'Cliente Uno']);
        $this->client2 = User::factory()->create(['rol' => 'cliente', 'name' => 'Cliente Dos']);
    }

    #[Test] // Reemplazar /** @test */
    public function admin_can_view_client_list(): void // Añadir tipo de retorno
    {
        $response = $this->actingAs($this->admin)->get(route('admin.clientes.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.clientes.index');
        $response->assertViewHas('clientes');
        $response->assertSee('Cliente Uno');
        $response->assertSee('Cliente Dos');
    }

    #[Test] // Reemplazar /** @test */
    public function client_cannot_view_client_list(): void // Añadir tipo de retorno
    {
        $response = $this->actingAs($this->client1)->get(route('admin.clientes.index'));
        $response->assertRedirect(route('profile.entry'));
        $response->assertSessionHas('error', 'Acceso no autorizado.');
    }

    #[Test] // Reemplazar /** @test */
    public function admin_can_view_specific_client_profile(): void // Añadir tipo de retorno
    {
        Pedidos::factory()->completado()->create(['cliente_id' => $this->client1->id]);
        Comentarios::factory()->create(['user_id' => $this->client1->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.clientes.show', $this->client1));

        $response->assertStatus(200);
        $response->assertViewIs('admin.clientes.show');
        $response->assertViewHas('cliente', $this->client1);
        $response->assertViewHasAll(['pedidos', 'comentarios']);
        $response->assertSee($this->client1->name);
        $response->assertSee($this->client1->email);
        $response->assertSee('Pedidos Recientes');
        $response->assertSee('Comentarios Recientes');
    }

    #[Test] // Reemplazar /** @test */
    public function admin_cannot_view_profile_of_non_client_user_via_client_route(): void // Añadir tipo de retorno
    {
        $response = $this->actingAs($this->admin)->get(route('admin.clientes.show', $this->admin));

        $response->assertRedirect(route('admin.clientes.index'));
        $response->assertSessionHas('error', 'El usuario especificado no es un cliente.');
    }

     #[Test] // Reemplazar /** @test */
    public function client_cannot_view_specific_client_profile_via_admin_route(): void // Añadir tipo de retorno
    {
        $response = $this->actingAs($this->client1)->get(route('admin.clientes.show', $this->client2));
        $response->assertRedirect(route('profile.entry'));
        $response->assertSessionHas('error', 'Acceso no autorizado.');
    }
}
