<?php
// filepath: app/Http/Controllers/ClientesController.php

namespace App\Http\Controllers;

use App\Models\User; // Importa el modelo User, que representa tanto clientes como administradores.
use App\Models\Pedidos; // Modelo Pedidos, necesario para cargar los pedidos del cliente.
use App\Models\Comentarios; // Modelo Comentarios, necesario para cargar los comentarios del cliente.
use Illuminate\Http\Request; // Objeto para manejar la solicitud HTTP (no se usa directamente en estos métodos).
use Illuminate\Support\Facades\Auth; // Fachada para verificar la autenticación y rol del usuario logueado.
use Illuminate\View\View;             // Para el type hinting del retorno de vistas.
use Illuminate\Http\RedirectResponse; // Para el type hinting del retorno de redirecciones.
use Illuminate\Support\Facades\Log;    // Fachada para registrar información o advertencias.

/**
 * Class ClientesController
 *
 * Controlador dedicado a la gestión de usuarios con rol 'cliente' desde la perspectiva
 * del administrador. Permite listar clientes y ver sus perfiles detallados.
 * Las acciones están restringidas al rol 'administrador'.
 *
 * @package App\Http\Controllers
 */
class ClientesController extends Controller
{
    /**
     * Muestra una lista paginada de todos los usuarios con rol 'cliente'.
     *
     * Esta acción está restringida a usuarios administradores.
     * Recupera los usuarios filtrados por rol, los ordena por nombre y los pagina.
     * Renderiza la vista del índice de clientes del panel de administración.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'admin.clientes.index' o redirige si no es admin.
     */
    public function index(): View|RedirectResponse
    {
        // 1. Autorización: Verificar si el usuario autenticado es un administrador.
        // Se comprueba el atributo 'rol' del usuario logueado.
        if (Auth::user()->rol !== 'administrador') {
            // Si no es admin, redirige a la ruta de entrada del perfil con un mensaje de error.
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // 2. Obtención de Datos: Recuperar los usuarios que son clientes.
        // Se utiliza el modelo User y se filtra por la columna 'rol'.
        $clientes = User::where('rol', 'cliente')
                        ->orderBy('name') // Ordena alfabéticamente por nombre.
                        ->paginate(20); // Pagina los resultados (20 por página). Ajustable.

        // 3. Retornar la Vista: Mostrar la lista de clientes.
        // Se renderiza la vista 'resources/views/admin/clientes/index.blade.php'.
        // Se pasa la colección paginada de clientes a la vista mediante `compact()`.
        // La vista accederá a los datos a través de la variable $clientes.
        return view('admin.clientes.index', compact('clientes'));
    }


    /**
     * Muestra el perfil detallado de un cliente específico (vista de administrador).
     *
     * Restringido a administradores. Utiliza Route Model Binding para obtener la instancia
     * del usuario (`$cliente`) basado en el ID de la ruta. Verifica que el usuario
     * solicitado sea realmente un cliente. Carga información relacionada como pedidos
     * y comentarios recientes del cliente.
     *
     * @param  \App\Models\User $cliente Instancia del modelo User inyectada por Laravel
     *                                   basada en el parámetro de ruta (ej. /admin/clientes/{cliente}).
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'admin.clientes.show' o redirige si hay errores.
     */
    public function show(User $cliente): View|RedirectResponse
    {
        // 1. Autorización: Verificar que el usuario LOGUEADO sea administrador.
        if (Auth::user()->rol !== 'administrador') {
            // Registra un intento de acceso no autorizado.
            Log::warning("Intento no autorizado de ver cliente ID {$cliente->id} por usuario ID " . Auth::id());
            // Redirige si no es admin.
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // 2. Verificación Adicional: Asegurarse de que el usuario solicitado ($cliente) es de rol 'cliente'.
        // Esto previene que un admin intente ver el perfil de otro admin a través de esta ruta.
        if ($cliente->rol !== 'cliente') {
            // Registra el intento.
            Log::info("Admin intentó ver perfil de usuario ID {$cliente->id} que no es cliente (Rol: {$cliente->rol}).");
            // Redirige a la lista de clientes del admin con un error.
            // **CORRECCIÓN**: La redirección debe usar 'admin.clientes.index' según la estructura de rutas.
            return redirect()->route('admin.clientes.index')->with('error', 'El usuario especificado no es un cliente.');
        }

        // 3. Carga de Datos Relacionados (Eager Loading):
        // Se utiliza `load()` para cargar relaciones después de que el modelo principal ($cliente) ya ha sido obtenido.
        // Esto es eficiente porque realiza consultas separadas solo para las relaciones necesarias.
        // Se cargan las mismas relaciones que se mostrarían en el perfil del propio cliente (ProfileController@show).
        $cliente->load([
            // Carga la relación 'pedidos' definida en el modelo User.
            'pedidos' => function ($query) {
                // Se aplica una restricción a la consulta de la relación:
                // Solo se cargan pedidos cuyo estado NO sea 'pendiente'.
                $query->where('status', '!=', Pedidos::STATUS_PENDIENTE)
                      // Se ordenan por fecha de pedido descendente.
                      ->latest('fecha_pedido')
                      // Se limita a los 5 más recientes.
                      ->take(5);
            },
            // Carga la relación 'comentarios' definida en el modelo User.
            'comentarios' => function ($query) {
                // Se aplica una restricción a la consulta de la relación:
                // Se carga también la relación 'libro' de cada comentario (para mostrar el título del libro).
                $query->with('libro')
                      // Se ordenan por fecha de creación descendente.
                      ->latest()
                      // Se limita a los 10 más recientes.
                      ->take(10);
            }
        ]);

        // 4. Extracción Opcional de Datos Cargados (para claridad en la vista):
        // Aunque se podría acceder directamente a $cliente->pedidos y $cliente->comentarios en la vista,
        // extraerlos aquí puede hacer el código de la vista un poco más limpio.
        $pedidos = $cliente->pedidos;
        $comentarios = $cliente->comentarios;

        // 5. Retornar la Vista: Mostrar el perfil del cliente para el admin.
        // Se renderiza la vista 'resources/views/admin/clientes/show.blade.php'.
        // Se pasa el objeto $cliente principal y las colecciones de $pedidos y $comentarios.
        return view('admin.clientes.show', compact('cliente', 'pedidos', 'comentarios'));
    }


}
