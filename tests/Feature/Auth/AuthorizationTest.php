<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase; // Trait para resetear la base de datos entre tests.
use Tests\TestCase; // Clase base para los tests.
use App\Models\User; // Modelo User para crear usuarios de prueba.
use App\Models\Libros; // Modelo Libros para pruebas de rutas públicas y gestión.
use App\Models\Autores; // Modelo Autores para pruebas de gestión.
use App\Models\Editoriales; // Modelo Editoriales para pruebas de gestión.
use App\Models\Pedidos; // Modelo Pedidos para pruebas de acceso a pedidos.
use App\Models\Comentarios; // Modelo Comentarios (no usado directamente aquí, pero importado).

/**
 * Class AuthorizationTest
 *
 * Suite de pruebas de Feature para verificar las reglas de autorización
 * de la aplicación. Comprueba que los usuarios con diferentes roles (admin, cliente)
 * y los usuarios no autenticados (invitados) tengan el acceso correcto o sean
 * redirigidos apropiadamente desde diferentes rutas (administración, cliente, públicas).
 * Utiliza el trait `RefreshDatabase` para asegurar un estado limpio de la BD
 * para cada prueba.
 *
 * @package Tests\Feature\Auth
 */
class AuthorizationTest extends TestCase
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
     * Configura el entorno de prueba antes de cada test.
     *
     * Crea un usuario administrador, un usuario cliente y otro usuario cliente
     * utilizando factories. Estas instancias se almacenan en propiedades de la clase
     * para ser utilizadas en los diferentes métodos de prueba. Llama al método `setUp`
     * de la clase padre.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp(); // Llama al método setUp de la clase padre.
        // Crea un usuario con rol 'administrador' usando la factory y el estado 'admin'.
        $this->admin = User::factory()->admin()->create();
        // Crea el primer usuario con rol 'cliente'.
        $this->client = User::factory()->create(['rol' => 'cliente']);
        // Crea el segundo usuario con rol 'cliente'.
        $this->otherClient = User::factory()->create(['rol' => 'cliente']);
    }

    // --- Rutas Admin ---

    /**
     * Prueba que un administrador puede acceder al dashboard de administración.
     *
     * Simula una petición GET a la ruta 'admin.dashboard' actuando como administrador.
     * Verifica que la respuesta HTTP tenga estado 200 (OK).
     *
     * @test
     * @return void
     */
    public function admin_can_access_admin_dashboard(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertStatus(200);
    }

    /**
     * Prueba que un cliente no puede acceder al dashboard de administración.
     *
     * Simula una petición GET a la ruta 'admin.dashboard' actuando como cliente.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.show'.
     * Verifica que la sesión contenga un mensaje de error específico.
     *
     * @test
     * @return void
     */
    public function client_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->client)
             ->get(route('admin.dashboard'))
             ->assertRedirect(route('profile.show')) // Redirección esperada según AdminDashboardController.
             ->assertSessionHas('error', 'Acceso no autorizado al panel de administración.');
    }

    /**
     * Prueba que un administrador puede acceder a la lista de clientes y al perfil de un cliente.
     *
     * Simula peticiones GET a las rutas 'admin.clientes.index' y 'admin.clientes.show'
     * (para un cliente específico) actuando como administrador.
     * Verifica que ambas respuestas HTTP tengan estado 200 (OK).
     *
     * @test
     * @return void
     */
    public function admin_can_access_admin_client_list_and_show(): void
    {
        $this->actingAs($this->admin)->get(route('admin.clientes.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.clientes.show', $this->client))->assertStatus(200);
    }

    /**
     * Prueba que un cliente no puede acceder a la lista de clientes ni al perfil de otro cliente
     * a través de las rutas de administración.
     *
     * Simula peticiones GET a las rutas 'admin.clientes.index' y 'admin.clientes.show'
     * (para otro cliente) actuando como cliente.
     * Verifica que ambas respuestas sean redirecciones a la ruta 'profile.entry'.
     * Verifica que ambas respuestas generen un mensaje de error específico en la sesión.
     *
     * @test
     * @return void
     */
    public function client_cannot_access_admin_client_list_or_show(): void
    {
        $this->actingAs($this->client)
             ->get(route('admin.clientes.index'))
             ->assertRedirect(route('profile.entry')) // Redirección esperada según ClientesController@index.
             ->assertSessionHas('error', 'Acceso no autorizado.');

        $this->actingAs($this->client)
             ->get(route('admin.clientes.show', $this->otherClient))
             ->assertRedirect(route('profile.entry')) // Redirección esperada según ClientesController@show.
             ->assertSessionHas('error', 'Acceso no autorizado.');
    }

    /**
     * Prueba que un administrador puede realizar todas las operaciones CRUD sobre autores.
     *
     * Crea un autor de prueba.
     * Simula peticiones GET, POST, PUT y DELETE a las rutas correspondientes de gestión de autores
     * ('admin.autores.*') actuando como administrador.
     * Verifica que las peticiones GET (index, create, edit) devuelvan estado 200 (OK).
     * Verifica que las peticiones POST, PUT y DELETE (store, update, destroy) resulten en una redirección.
     *
     * @test
     * @return void
     */
    public function admin_can_manage_autores(): void
    {
        $autor = Autores::factory()->create();
        $this->actingAs($this->admin)->get(route('admin.autores.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.autores.create'))->assertStatus(200);
        $this->actingAs($this->admin)->post(route('admin.autores.store'), ['nombre' => 'Nuevo Autor', 'pais' => 'Pais'])->assertRedirect();
        $this->actingAs($this->admin)->get(route('admin.autores.edit', $autor))->assertStatus(200);
        $this->actingAs($this->admin)->put(route('admin.autores.update', $autor), ['nombre' => 'Autor Editado', 'pais' => 'Pais Editado'])->assertRedirect();
        $this->actingAs($this->admin)->delete(route('admin.autores.destroy', $autor))->assertRedirect();
    }

    /**
     * Prueba que un cliente no puede realizar operaciones de gestión sobre autores.
     *
     * Crea un autor de prueba.
     * Simula peticiones GET, POST, PUT y DELETE a las rutas de gestión de autores ('admin.autores.*')
     * actuando como cliente.
     * Verifica que las peticiones GET (index, create, edit) resulten en redirecciones específicas
     * según la lógica del controlador.
     * Verifica que las peticiones POST, PUT y DELETE (store, update, destroy) resulten en un
     * estado HTTP 403 (Forbidden), indicando que la acción fue abortada por falta de permisos.
     *
     * @test
     * @return void
     */
    public function client_cannot_manage_autores(): void
    {
        $autor = Autores::factory()->create();
        $this->actingAs($this->client)->get(route('admin.autores.index'))->assertRedirect(route('profile.entry')); // Según AutoresController@index.
        $this->actingAs($this->client)->get(route('admin.autores.create'))->assertRedirect(route('admin.autores.index')); // Según AutoresController@create.
        $this->actingAs($this->client)->post(route('admin.autores.store'), ['nombre' => 'X', 'pais' => 'Y'])->assertStatus(403); // Abort.
        $this->actingAs($this->client)->get(route('admin.autores.edit', $autor))->assertRedirect(route('admin.autores.index')); // Según AutoresController@edit.
        $this->actingAs($this->client)->put(route('admin.autores.update', $autor), ['nombre' => 'X', 'pais' => 'Y'])->assertStatus(403); // Abort.
        $this->actingAs($this->client)->delete(route('admin.autores.destroy', $autor))->assertStatus(403); // Abort.
    }

    // (Pruebas similares para Editoriales no están implementadas aquí)

    /**
     * Prueba que un administrador puede realizar operaciones de gestión sobre libros.
     *
     * Crea un libro, autor y editorial de prueba.
     * Simula peticiones GET, POST, PUT y DELETE a las rutas correspondientes de gestión de libros
     * ('libros.create', 'libros.store', 'libros.edit', 'libros.update', 'libros.destroy')
     * actuando como administrador.
     * Verifica que las peticiones GET (create, edit) devuelvan estado 200 (OK).
     * Verifica que las peticiones POST, PUT y DELETE (store, update, destroy) resulten en una
     * redirección a la ruta 'libros.index'.
     *
     * @test
     * @return void
     */
    public function admin_can_manage_libros(): void
    {
        $libro = Libros::factory()->create();
        $autor = Autores::factory()->create();
        $editorial = Editoriales::factory()->create();

        $this->actingAs($this->admin)->get(route('libros.create'))->assertStatus(200);
        // Usa factory()->raw() para generar datos válidos para el POST/PUT.
        $this->actingAs($this->admin)->post(route('libros.store'), Libros::factory()->raw(['autor_id' => $autor->id, 'editorial_id' => $editorial->id]))->assertRedirect(route('libros.index'));
        $this->actingAs($this->admin)->get(route('libros.edit', $libro))->assertStatus(200);
        $this->actingAs($this->admin)->put(route('libros.update', $libro), Libros::factory()->raw(['autor_id' => $autor->id, 'editorial_id' => $editorial->id]))->assertRedirect(route('libros.index'));
        $this->actingAs($this->admin)->delete(route('libros.destroy', $libro))->assertRedirect(route('libros.index'));
    }

    /**
     * Prueba que un cliente no puede realizar operaciones de gestión sobre libros.
     *
     * Crea un libro de prueba.
     * Simula peticiones GET, POST, PUT y DELETE a las rutas de gestión de libros
     * ('libros.create', 'libros.store', 'libros.edit', 'libros.update', 'libros.destroy')
     * actuando como cliente.
     * Verifica que las peticiones GET (create, edit) resulten en redirecciones a 'libros.index'.
     * Verifica que las peticiones POST, PUT y DELETE (store, update, destroy) resulten en un
     * estado HTTP 403 (Forbidden), indicando que la acción fue abortada por falta de permisos.
     *
     * @test
     * @return void
     */
    public function client_cannot_manage_libros(): void
    {
        $libro = Libros::factory()->create();
        $this->actingAs($this->client)->get(route('libros.create'))->assertRedirect(route('libros.index')); // Según LibrosController@create.
        $this->actingAs($this->client)->post(route('libros.store'), [])->assertStatus(403); // Abort.
        $this->actingAs($this->client)->get(route('libros.edit', $libro))->assertRedirect(route('libros.index')); // Según LibrosController@edit.
        $this->actingAs($this->client)->put(route('libros.update', $libro), [])->assertStatus(403); // Abort.
        $this->actingAs($this->client)->delete(route('libros.destroy', $libro))->assertStatus(403); // Abort.
    }

    // --- Rutas Cliente ---

    /**
     * Prueba que un cliente puede acceder a su propio perfil (vista y edición).
     *
     * Simula peticiones GET a las rutas 'profile.show' y 'profile.edit' actuando como cliente.
     * Verifica que ambas respuestas HTTP tengan estado 200 (OK).
     *
     * @test
     * @return void
     */
    public function client_can_access_own_profile_show_and_edit(): void
    {
        $this->actingAs($this->client)->get(route('profile.show'))->assertStatus(200);
        $this->actingAs($this->client)->get(route('profile.edit'))->assertStatus(200);
    }

    /**
     * Prueba que un cliente puede acceder a la vista de su carrito de compras.
     *
     * Simula una petición GET a la ruta 'detallespedidos.index' actuando como cliente.
     * Verifica que la respuesta HTTP tenga estado 200 (OK).
     *
     * @test
     * @return void
     */
    public function client_can_access_cart(): void
    {
        $this->actingAs($this->client)->get(route('detallespedidos.index'))->assertStatus(200);
    }

    /**
     * Prueba que un cliente puede ver los detalles de su propio pedido completado.
     *
     * Crea un pedido completado asociado al cliente.
     * Simula una petición GET a la ruta 'pedidos.show' para ese pedido, actuando como cliente.
     * Verifica que la respuesta HTTP tenga estado 200 (OK).
     *
     * @test
     * @return void
     */
    public function client_can_view_own_completed_order(): void
    {
        // Arrange: Crear un pedido para el cliente.
        $pedido = Pedidos::factory()->completado()->create(['cliente_id' => $this->client->id]);
        // Act: Realizar la petición como el cliente dueño del pedido.
        $this->actingAs($this->client)->get(route('pedidos.show', $pedido))->assertStatus(200);
    }

    /**
     * Prueba que un cliente no puede ver los detalles del pedido de otro cliente.
     *
     * Crea un pedido completado asociado a `$otherClient`.
     * Simula una petición GET a la ruta 'pedidos.show' para ese pedido, actuando como `$client`.
     * Verifica que la respuesta sea una redirección a la ruta 'profile.show'.
     * Verifica que la sesión contenga un mensaje de error específico.
     *
     * @test
     * @return void
     */
    public function client_cannot_view_other_client_order(): void
    {
        // Arrange: Crear un pedido para otro cliente.
        $otherPedido = Pedidos::factory()->completado()->create(['cliente_id' => $this->otherClient->id]);
        // Act: Intentar ver el pedido de otro cliente.
        $this->actingAs($this->client)
             ->get(route('pedidos.show', $otherPedido))
             // Assert: Verificar la redirección y el mensaje de error.
             ->assertRedirect(route('profile.show')) // Redirección esperada según PedidosController@show.
             ->assertSessionHas('error', 'No tienes permiso para ver este pedido.');
    }

    /**
     * Prueba que un administrador puede ver los detalles del pedido de cualquier cliente.
     *
     * Crea un pedido completado asociado a `$client`.
     * Simula una petición GET a la ruta 'pedidos.show' para ese pedido, actuando como administrador.
     * Verifica que la respuesta HTTP tenga estado 200 (OK).
     *
     * @test
     * @return void
     */
    public function admin_can_view_any_client_order(): void
    {
        // Arrange: Crear un pedido para un cliente.
        $pedido = Pedidos::factory()->completado()->create(['cliente_id' => $this->client->id]);
        // Act: Realizar la petición como administrador.
        $this->actingAs($this->admin)->get(route('pedidos.show', $pedido))->assertStatus(200);
    }

    // --- Rutas Públicas ---

    /**
     * Prueba que un usuario no autenticado (invitado) puede acceder al índice y
     * a la vista de detalle de los libros.
     *
     * Crea un libro de prueba.
     * Simula peticiones GET a las rutas 'libros.index' y 'libros.show' sin autenticar usuario.
     * Verifica que ambas respuestas HTTP tengan estado 200 (OK).
     *
     * @test
     * @return void
     */
    public function guest_can_access_public_book_index_and_show(): void
    {
        // Arrange: Crear un libro.
        $libro = Libros::factory()->create();
        // Act & Assert: Realizar peticiones GET como invitado.
        $this->get(route('libros.index'))->assertStatus(200);
        $this->get(route('libros.show', $libro))->assertStatus(200);
    }

    /**
     * Prueba que un usuario no autenticado (invitado) es redirigido al login
     * desde varias rutas protegidas.
     *
     * Simula peticiones GET y POST a rutas protegidas por autenticación
     * (perfil, carrito, índice de pedidos, checkout, dashboard admin) sin autenticar usuario.
     * Verifica que todas las respuestas sean redirecciones a la ruta nombrada 'login'.
     *
     * @test
     * @return void
     */
    public function guest_is_redirected_from_protected_routes(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
        $this->get(route('detallespedidos.index'))->assertRedirect(route('login'));
        $this->get(route('pedidos.index'))->assertRedirect(route('login')); // Ruta admin, protegida por auth.
        $this->post(route('pedidos.checkout.process'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
