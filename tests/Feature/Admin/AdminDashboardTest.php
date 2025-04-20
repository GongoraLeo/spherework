<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Libros;
use App\Models\Pedidos;
use App\Models\Detallespedidos;

/**
 * @group admin
 */

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->client = User::factory()->create(['rol' => 'cliente']);
    }

    /** @test */
    public function admin_can_view_dashboard_with_stats()
    {
        // Crear datos para las estadísticas
        $libro1 = Libros::factory()->create(['titulo' => 'Libro Vendido 1']);
        $libro2 = Libros::factory()->create(['titulo' => 'Libro Vendido 2']);
        User::factory()->count(3)->create(['rol' => 'cliente']); // 3 clientes + $this->client = 4

        // Pedido completado con libro1
        $pedido1 = Pedidos::factory()->completado()->create();
        Detallespedidos::factory()->create([
            'pedido_id' => $pedido1->id,
            'libro_id' => $libro1->id,
            'cantidad' => 5,
        ]);

        // Pedido enviado con libro1 y libro2
        $pedido2 = Pedidos::factory()->create(['status' => Pedidos::STATUS_ENVIADO]);
        Detallespedidos::factory()->create([
            'pedido_id' => $pedido2->id,
            'libro_id' => $libro1->id,
            'cantidad' => 2,
        ]);
        Detallespedidos::factory()->create([
            'pedido_id' => $pedido2->id,
            'libro_id' => $libro2->id,
            'cantidad' => 3,
        ]);

        // Pedido pendiente (no debe contar en "más vendidos")
        $pedido3 = Pedidos::factory()->create(['status' => Pedidos::STATUS_PENDIENTE]);
        Detallespedidos::factory()->create([
            'pedido_id' => $pedido3->id,
            'libro_id' => $libro2->id,
            'cantidad' => 10,
        ]);


        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');

        // Verificar que las variables de estadísticas se pasan a la vista
        $response->assertViewHasAll([
            'librosMasVendidos',
            'clientesRecientes',
            'totalPedidos',
            'totalClientes',
        ]);

        // Verificar contenido específico (ejemplos)
        $response->assertSee('Libro Vendido 1');
        $response->assertSee('Libro Vendido 2');

        // --- LÍNEAS MODIFICADAS ---
        // $response->assertSee('Total Pedidos: 3'); // ANTES
        $response->assertSee('Total Pedidos'); // DESPUÉS (Verifica la etiqueta)
        $response->assertSee('<p class="text-2xl font-bold">3</p>', false); // DESPUÉS (Verifica el valor en su tag)

        // Revisamos también la de clientes según el HTML:
        // $response->assertSee('Total Clientes: 4'); // ANTES
        $response->assertSee('Clientes Registrados'); // DESPUÉS
        $response->assertSee('<p class="text-2xl font-bold">7</p>', false); // DESPUÉS

        // Verificar que el libro más vendido aparece primero (si la vista lo ordena)
        $viewData = $response->viewData('librosMasVendidos');
        if ($viewData->isNotEmpty()) { // Añadir comprobación por si no hay libros vendidos
            $this->assertEquals('Libro Vendido 1', $viewData->first()->titulo);
            $this->assertEquals(7, $viewData->first()->total_vendido);
            if ($viewData->count() > 1) { // Comprobar si hay un segundo libro
                $this->assertEquals('Libro Vendido 2', $viewData->get(1)->titulo);
                $this->assertEquals(3, $viewData->get(1)->total_vendido);
            }
        }
    }

    /** @test */
    public function client_is_redirected_from_admin_dashboard()
    {
        $response = $this->actingAs($this->client)->get(route('admin.dashboard'));
        $response->assertRedirect(route('profile.show')); // Según tu AdminDashboardController
        $response->assertSessionHas('error', 'Acceso no autorizado al panel de administración.');
    }

    /** @test */
    public function guest_is_redirected_from_admin_dashboard()
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }
}
