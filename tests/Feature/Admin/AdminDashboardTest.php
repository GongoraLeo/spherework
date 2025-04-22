<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\User; // Modelo User para crear usuarios de prueba.
use App\Models\Libros; // Modelo Libros para crear libros de prueba.
use App\Models\Pedidos; // Modelo Pedidos para crear pedidos de prueba.
use App\Models\Detallespedidos; // Modelo Detallespedidos para crear detalles de pedido.

/**
 * Class AdminDashboardTest
 *
 * Suite de pruebas de Feature para verificar el acceso y contenido
 * del panel de control del administrador (`admin.dashboard`).
 * Comprueba que los administradores puedan ver el dashboard con estadísticas
 * y que los clientes y usuarios no autenticados sean redirigidos correctamente.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba.
 * Pertenece al grupo de pruebas 'admin'.
 *
 * @group admin
 * @package Tests\Feature\Admin
 */
class AdminDashboardTest extends TestCase
{
    /**
     * Trait RefreshDatabase
     *
     * Reinicia la base de datos antes de cada prueba en esta clase,
     * asegurando que los tests no interfieran entre sí.
     */
    use RefreshDatabase;

    /**
     * @var User Instancia del usuario administrador para las pruebas.
     */
    private User $admin;
    /**
     * @var User Instancia del usuario cliente para las pruebas.
     */
    private User $client;

    /**
     * Configura el entorno de prueba antes de cada test.
     *
     * Crea un usuario administrador y un usuario cliente utilizando factories.
     * Estas instancias se almacenan en propiedades de la clase para ser
     * utilizadas en los diferentes métodos de prueba. Llama al método `setUp`
     * de la clase padre.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp(); // Llama al método setUp de la clase padre.
        // Crea un usuario con rol 'administrador' usando la factory y el estado 'admin'.
        $this->admin = User::factory()->admin()->create();
        // Crea un usuario con rol 'cliente'.
        $this->client = User::factory()->create(['rol' => 'cliente']);
    }

    /**
     * Prueba que un administrador puede ver el dashboard y sus estadísticas.
     *
     * Crea datos de prueba: dos libros, tres clientes adicionales, un pedido completado,
     * un pedido enviado (ambos con detalles asociados a los libros) y un pedido pendiente.
     * Simula una petición GET a la ruta 'admin.dashboard' actuando como el usuario administrador.
     * Verifica que la respuesta HTTP tenga estado 200.
     * Verifica que la vista renderizada sea 'admin.dashboard'.
     * Verifica que la vista reciba las variables 'librosMasVendidos', 'clientesRecientes',
     * 'totalPedidos' y 'totalClientes'.
     * Verifica que el contenido de la respuesta incluya los títulos de los libros creados.
     * Verifica que el contenido de la respuesta incluya la etiqueta 'Total Pedidos' y el valor '3'
     * dentro de una etiqueta `<p>` específica.
     * Verifica que el contenido de la respuesta incluya la etiqueta 'Clientes Registrados' y el valor '7'
     * dentro de una etiqueta `<p>` específica.
     * Obtiene los datos de 'librosMasVendidos' pasados a la vista. Si la colección no está vacía,
     * verifica que el primer libro sea 'Libro Vendido 1' con un total vendido de 7. Si hay más
     * de un libro, verifica que el segundo sea 'Libro Vendido 2' con un total vendido de 3.
     *
     * @test
     * @return void
     */
    public function admin_can_view_dashboard_with_stats(): void
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

        // Verifica la presencia de las etiquetas y los valores específicos de los totales.
        $response->assertSee('Total Pedidos'); // Verifica la etiqueta.
        $response->assertSee('<p class="text-2xl font-bold">3</p>', false); // Verifica el valor (3 pedidos no pendientes).

        // Verifica la presencia de las etiquetas y los valores específicos de los totales de clientes.
        $response->assertSee('Clientes Registrados'); // Verifica la etiqueta.
        $response->assertSee('<p class="text-2xl font-bold">7</p>', false); // Verifica el valor.

        // Verificar que el libro más vendido aparece primero (si la vista lo ordena)
        $viewData = $response->viewData('librosMasVendidos');
        if ($viewData->isNotEmpty()) { // Comprueba si la colección no está vacía.
            $this->assertEquals('Libro Vendido 1', $viewData->first()->titulo); // Verifica título del más vendido.
            $this->assertEquals(7, $viewData->first()->total_vendido); // Verifica cantidad del más vendido.
            if ($viewData->count() > 1) { // Comprueba si hay un segundo libro.
                $this->assertEquals('Libro Vendido 2', $viewData->get(1)->titulo); // Verifica título del segundo.
                $this->assertEquals(3, $viewData->get(1)->total_vendido); // Verifica cantidad del segundo.
            }
        }
    }

    /**
     * Prueba que un cliente es redirigido si intenta acceder al dashboard de admin.
     *
     * Simula una petición GET a la ruta 'admin.dashboard' actuando como el usuario cliente.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.show'.
     * Verifica que la sesión contenga una clave 'error' con el mensaje específico
     * 'Acceso no autorizado al panel de administración.'.
     *
     * @test
     * @return void
     */
    public function client_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->actingAs($this->client)->get(route('admin.dashboard'));
        $response->assertRedirect(route('profile.show')); // Verifica la ruta de redirección.
        $response->assertSessionHas('error', 'Acceso no autorizado al panel de administración.'); // Verifica mensaje de error en sesión.
    }

    /**
     * Prueba que un usuario no autenticado (invitado) es redirigido al login.
     *
     * Simula una petición GET a la ruta 'admin.dashboard' sin autenticar ningún usuario.
     * Verifica que la respuesta sea una redirección a la ruta nombrada 'login'.
     *
     * @test
     * @return void
     */
    public function guest_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login')); // Verifica la redirección a login.
    }
}
