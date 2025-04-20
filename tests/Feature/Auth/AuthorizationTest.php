<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Libros;
use App\Models\Autores;
use App\Models\Editoriales;
use App\Models\Pedidos;
use App\Models\Comentarios;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $client;
    private User $otherClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->client = User::factory()->create(['rol' => 'cliente']);
        $this->otherClient = User::factory()->create(['rol' => 'cliente']);
    }

    // --- Rutas Admin ---

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertStatus(200);
    }

    /** @test */
    public function client_cannot_access_admin_dashboard()
    {
        $this->actingAs($this->client)
             ->get(route('admin.dashboard'))
             ->assertRedirect(route('profile.show')) // Según AdminDashboardController
             ->assertSessionHas('error', 'Acceso no autorizado al panel de administración.');
    }

    /** @test */
    public function admin_can_access_admin_client_list_and_show()
    {
        $this->actingAs($this->admin)->get(route('admin.clientes.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.clientes.show', $this->client))->assertStatus(200);
    }

    /** @test */
    public function client_cannot_access_admin_client_list_or_show()
    {
        $this->actingAs($this->client)
             ->get(route('admin.clientes.index'))
             ->assertRedirect(route('profile.entry')) // Según ClientesController@index
             ->assertSessionHas('error', 'Acceso no autorizado.');

        $this->actingAs($this->client)
             ->get(route('admin.clientes.show', $this->otherClient))
             ->assertRedirect(route('profile.entry')) // Según ClientesController@show
             ->assertSessionHas('error', 'Acceso no autorizado.');
    }

    /** @test */
    public function admin_can_manage_autores()
    {
        $autor = Autores::factory()->create();
        $this->actingAs($this->admin)->get(route('admin.autores.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.autores.create'))->assertStatus(200);
        $this->actingAs($this->admin)->post(route('admin.autores.store'), ['nombre' => 'Nuevo Autor', 'pais' => 'Pais'])->assertRedirect();
        $this->actingAs($this->admin)->get(route('admin.autores.edit', $autor))->assertStatus(200);
        $this->actingAs($this->admin)->put(route('admin.autores.update', $autor), ['nombre' => 'Autor Editado', 'pais' => 'Pais Editado'])->assertRedirect();
        $this->actingAs($this->admin)->delete(route('admin.autores.destroy', $autor))->assertRedirect();
    }

    /** @test */
    public function client_cannot_manage_autores()
    {
        $autor = Autores::factory()->create();
        $this->actingAs($this->client)->get(route('admin.autores.index'))->assertRedirect(route('profile.entry')); // Según AutoresController@index
        $this->actingAs($this->client)->get(route('admin.autores.create'))->assertRedirect(route('admin.autores.index')); // Según AutoresController@create
        $this->actingAs($this->client)->post(route('admin.autores.store'), ['nombre' => 'X', 'pais' => 'Y'])->assertStatus(403); // Abort
        $this->actingAs($this->client)->get(route('admin.autores.edit', $autor))->assertRedirect(route('admin.autores.index')); // Según AutoresController@edit
        $this->actingAs($this->client)->put(route('admin.autores.update', $autor), ['nombre' => 'X', 'pais' => 'Y'])->assertStatus(403); // Abort
        $this->actingAs($this->client)->delete(route('admin.autores.destroy', $autor))->assertStatus(403); // Abort
    }

    // (Añadir pruebas similares para Editoriales)

    /** @test */
    public function admin_can_manage_libros()
    {
        $libro = Libros::factory()->create();
        $autor = Autores::factory()->create();
        $editorial = Editoriales::factory()->create();

        $this->actingAs($this->admin)->get(route('libros.create'))->assertStatus(200);
        $this->actingAs($this->admin)->post(route('libros.store'), Libros::factory()->raw(['autor_id' => $autor->id, 'editorial_id' => $editorial->id]))->assertRedirect(route('libros.index'));
        $this->actingAs($this->admin)->get(route('libros.edit', $libro))->assertStatus(200);
        $this->actingAs($this->admin)->put(route('libros.update', $libro), Libros::factory()->raw(['autor_id' => $autor->id, 'editorial_id' => $editorial->id]))->assertRedirect(route('libros.index'));
        $this->actingAs($this->admin)->delete(route('libros.destroy', $libro))->assertRedirect(route('libros.index'));
    }

    /** @test */
    public function client_cannot_manage_libros()
    {
        $libro = Libros::factory()->create();
        $this->actingAs($this->client)->get(route('libros.create'))->assertRedirect(route('libros.index')); // Según LibrosController@create
        $this->actingAs($this->client)->post(route('libros.store'), [])->assertStatus(403); // Abort
        $this->actingAs($this->client)->get(route('libros.edit', $libro))->assertRedirect(route('libros.index')); // Según LibrosController@edit
        $this->actingAs($this->client)->put(route('libros.update', $libro), [])->assertStatus(403); // Abort
        $this->actingAs($this->client)->delete(route('libros.destroy', $libro))->assertStatus(403); // Abort
    }

    // --- Rutas Cliente ---

    /** @test */
    public function client_can_access_own_profile_show_and_edit()
    {
        $this->actingAs($this->client)->get(route('profile.show'))->assertStatus(200);
        $this->actingAs($this->client)->get(route('profile.edit'))->assertStatus(200);
    }

    /** @test */
    public function client_can_access_cart()
    {
        $this->actingAs($this->client)->get(route('detallespedidos.index'))->assertStatus(200);
    }

    /** @test */
    public function client_can_view_own_completed_order()
    {
        $pedido = Pedidos::factory()->completado()->create(['cliente_id' => $this->client->id]);
        $this->actingAs($this->client)->get(route('pedidos.show', $pedido))->assertStatus(200);
    }

    /** @test */
    public function client_cannot_view_other_client_order()
    {
        $otherPedido = Pedidos::factory()->completado()->create(['cliente_id' => $this->otherClient->id]);
        $this->actingAs($this->client)
             ->get(route('pedidos.show', $otherPedido))
             ->assertRedirect(route('profile.show')) // Según PedidosController@show
             ->assertSessionHas('error', 'No tienes permiso para ver este pedido.');
    }

    /** @test */
    public function admin_can_view_any_client_order()
    {
        $pedido = Pedidos::factory()->completado()->create(['cliente_id' => $this->client->id]);
        $this->actingAs($this->admin)->get(route('pedidos.show', $pedido))->assertStatus(200);
    }

    // --- Rutas Públicas ---

    /** @test */
    public function guest_can_access_public_book_index_and_show()
    {
        $libro = Libros::factory()->create();
        $this->get(route('libros.index'))->assertStatus(200);
        $this->get(route('libros.show', $libro))->assertStatus(200);
    }

    /** @test */
    public function guest_is_redirected_from_protected_routes()
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
        $this->get(route('detallespedidos.index'))->assertRedirect(route('login'));
        $this->get(route('pedidos.index'))->assertRedirect(route('login')); // Ruta admin, protegida por auth
        $this->post(route('pedidos.checkout.process'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
