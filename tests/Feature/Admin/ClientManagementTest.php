<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\User; // Modelo User para crear usuarios de prueba.
use App\Models\Pedidos; // Modelo Pedidos para probar la vista show.
use App\Models\Comentarios; // Modelo Comentarios para probar la vista show.
use PHPUnit\Framework\Attributes\Test; // Atributo para marcar métodos como tests (PHPUnit 10+).

/**
 * Class ClientManagementTest
 *
 * Suite de pruebas de Feature para verificar la funcionalidad de gestión de clientes
 * desde la perspectiva del administrador. Comprueba que un administrador pueda
 * listar clientes y ver el perfil específico de un cliente, y que los usuarios
 * con rol de cliente no tengan acceso a estas funcionalidades de administración.
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba. Pertenece al grupo de pruebas 'admin'.
 *
 * @group admin
 * @package Tests\Feature\Admin
 */
class ClientManagementTest extends TestCase
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
    private User $client1;
    /**
     * @var User Instancia del segundo usuario cliente utilizada en las pruebas.
     */
    private User $client2;

    /**
     * Configura el entorno de prueba antes de cada test.
     *
     * Crea un usuario administrador y dos usuarios clientes utilizando factories,
     * asegurando explícitamente sus roles. Estas instancias se almacenan en
     * propiedades de la clase para ser utilizadas en los métodos de prueba.
     * Llama al método `setUp` de la clase padre.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp(); // Llama al método setUp de la clase padre.
        // Crea un usuario con rol 'administrador'.
        $this->admin = User::factory()->create(['rol' => 'administrador']);
        // Crea el primer usuario cliente con un nombre específico.
        $this->client1 = User::factory()->create(['rol' => 'cliente', 'name' => 'Cliente Uno']);
        // Crea el segundo usuario cliente con un nombre específico.
        $this->client2 = User::factory()->create(['rol' => 'cliente', 'name' => 'Cliente Dos']);
    }

    /**
     * Prueba que un administrador puede ver la lista de clientes.
     *
     * Simula una petición GET a la ruta 'admin.clientes.index' actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'admin.clientes.index'.
     * Verifica que la vista reciba la variable 'clientes'.
     * Verifica que los nombres de los clientes creados ('Cliente Uno', 'Cliente Dos')
     * sean visibles en la respuesta.
     *
     * @return void
     */
    #[Test]
    public function admin_can_view_client_list(): void
    {
        // Act: Realizar la petición como administrador.
        $response = $this->actingAs($this->admin)->get(route('admin.clientes.index'));

        // Assert: Verificar la respuesta y el contenido.
        $response->assertStatus(200);
        $response->assertViewIs('admin.clientes.index');
        $response->assertViewHas('clientes');
        $response->assertSee('Cliente Uno');
        $response->assertSee('Cliente Dos');
    }

    /**
     * Prueba que un cliente no puede ver la lista de clientes del admin.
     *
     * Simula una petición GET a la ruta 'admin.clientes.index' actuando como cliente (`client1`).
     * Verifica que la respuesta sea una redirección a la ruta 'profile.entry'.
     * Verifica que la sesión contenga un mensaje de error específico de acceso no autorizado.
     *
     * @return void
     */
    #[Test]
    public function client_cannot_view_client_list(): void
    {
        // Act: Realizar la petición como cliente.
        $response = $this->actingAs($this->client1)->get(route('admin.clientes.index'));
        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('profile.entry'));
        $response->assertSessionHas('error', 'Acceso no autorizado.');
    }

    /**
     * Prueba que un administrador puede ver el perfil específico de un cliente.
     *
     * Crea un pedido completado y un comentario asociados al cliente `client1`.
     * Simula una petición GET a la ruta 'admin.clientes.show' para `client1`, actuando como administrador.
     * Verifica que la respuesta HTTP sea 200 (OK).
     * Verifica que se renderice la vista 'admin.clientes.show'.
     * Verifica que la vista reciba la variable 'cliente' con la instancia de `client1`.
     * Verifica que la vista reciba las variables 'pedidos' y 'comentarios'.
     * Verifica que el nombre y email de `client1` sean visibles en la respuesta.
     * Verifica que las etiquetas 'Pedidos Recientes' y 'Comentarios Recientes' estén presentes.
     *
     * @return void
     */
    #[Test]
    public function admin_can_view_specific_client_profile(): void
    {
        // Arrange: Crear datos asociados al cliente.
        Pedidos::factory()->completado()->create(['cliente_id' => $this->client1->id]);
        Comentarios::factory()->create(['user_id' => $this->client1->id]);

        // Act: Realizar la petición como administrador para ver el perfil del cliente.
        $response = $this->actingAs($this->admin)->get(route('admin.clientes.show', $this->client1));

        // Assert: Verificar la respuesta, vista, datos y contenido.
        $response->assertStatus(200);
        $response->assertViewIs('admin.clientes.show');
        $response->assertViewHas('cliente', $this->client1);
        $response->assertViewHasAll(['pedidos', 'comentarios']);
        $response->assertSee($this->client1->name);
        $response->assertSee($this->client1->email);
        $response->assertSee('Pedidos Recientes');
        $response->assertSee('Comentarios Recientes');
    }

    /**
     * Prueba que un administrador no puede ver el perfil de otro administrador
     * a través de la ruta destinada a ver perfiles de clientes.
     *
     * Simula una petición GET a la ruta 'admin.clientes.show' pasando la instancia
     * del propio administrador (`$this->admin`) como parámetro, actuando como administrador.
     * Verifica que la respuesta sea una redirección a la ruta 'admin.clientes.index'.
     * Verifica que la sesión contenga un mensaje de error específico indicando que
     * el usuario solicitado no es un cliente.
     *
     * @return void
     */
    #[Test]
    public function admin_cannot_view_profile_of_non_client_user_via_client_route(): void
    {
        // Act: Intentar ver el perfil del admin a través de la ruta de clientes.
        $response = $this->actingAs($this->admin)->get(route('admin.clientes.show', $this->admin));

        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('admin.clientes.index'));
        $response->assertSessionHas('error', 'El usuario especificado no es un cliente.');
    }

     /**
      * Prueba que un cliente no puede ver el perfil de otro cliente
      * a través de la ruta de administración.
      *
      * Simula una petición GET a la ruta 'admin.clientes.show' para `client2`,
      * actuando como `client1`.
      * Verifica que la respuesta sea una redirección a la ruta 'profile.entry'.
      * Verifica que la sesión contenga un mensaje de error específico de acceso no autorizado.
      *
      * @return void
      */
     #[Test]
    public function client_cannot_view_specific_client_profile_via_admin_route(): void
    {
        // Act: Intentar ver el perfil de client2 actuando como client1.
        $response = $this->actingAs($this->client1)->get(route('admin.clientes.show', $this->client2));
        // Assert: Verificar la redirección y el mensaje de error.
        $response->assertRedirect(route('profile.entry'));
        $response->assertSessionHas('error', 'Acceso no autorizado.');
    }
}
