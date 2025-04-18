<?php
// filepath: app/Http/Controllers/AdminDashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para verificar la autenticación y el rol del usuario.
use Illuminate\View\View;             // Para especificar el tipo de retorno de la vista.
use Illuminate\Http\RedirectResponse; // Para especificar el tipo de retorno de redirección.
use App\Models\Libros;                // Modelo para interactuar con la tabla 'libros'.
use App\Models\User;                  // Modelo para interactuar con la tabla 'users'.
use App\Models\Pedidos;               // Modelo para interactuar con la tabla 'pedidos'.
use App\Models\Detallespedidos;       // Modelo para interactuar con la tabla 'detallespedidos'.
use Illuminate\Support\Facades\DB;    // Fachada DB, aunque las consultas usan principalmente Eloquent/Query Builder.

/**
 * Class AdminDashboardController
 *
 * Gestiona la visualización del panel principal para usuarios administradores.
 * Se encarga de recopilar datos resumidos y estadísticas clave para mostrarlos
 * en la vista del dashboard de administración.
 *
 * @package App\Http\Controllers
 */
class AdminDashboardController extends Controller
{
    /**
     * Muestra el panel de control principal del administrador.
     *
     * Este método verifica primero si el usuario autenticado es un administrador.
     * Si lo es, recopila diversas estadísticas (libros más vendidos, clientes recientes,
     * totales de pedidos y clientes) mediante consultas a la base de datos.
     * Finalmente, renderiza la vista 'admin.dashboard' pasándole estos datos.
     * Si el usuario no es administrador, lo redirige a su perfil estándar con un mensaje de error.
     *
     * @param Request $request La solicitud HTTP entrante (no se usa directamente aquí, pero es estándar).
     * @return View|RedirectResponse Retorna la vista del dashboard para administradores o una redirección si no está autorizado.
     */
    public function index(Request $request): View|RedirectResponse
    {
        // 1. Autorización: Asegurarse de que solo los administradores puedan acceder.
        // Verificamos el rol del usuario autenticado actualmente.
        if (Auth::user()->rol !== 'administrador') {
            // Si no es admin, redirigir a su perfil normal (profile.show)
            // Se eligió profile.show en lugar de profile.entry para evitar un posible bucle
            // si profile.entry redirigiera de nuevo aquí por error.
            return redirect()->route('profile.show')->with('error', 'Acceso no autorizado al panel de administración.');
        }

        // 2. Obtener Datos para Estadísticas y Resúmenes

        // 2.1. Libros más vendidos:
        // Se realiza una consulta compleja para determinar los libros con más unidades vendidas.
        $librosMasVendidos = Libros::select('libros.id', 'libros.titulo') // Seleccionamos ID y título del libro.
            // Usamos selectRaw para calcular la suma de cantidades vendidas y le damos un alias 'total_vendido'.
            ->selectRaw('SUM(detallespedidos.cantidad) as total_vendido')
            // Unimos la tabla 'libros' con 'detallespedidos' usando la clave foránea 'libro_id'.
            ->join('detallespedidos', 'libros.id', '=', 'detallespedidos.libro_id')
            // Unimos 'detallespedidos' con 'pedidos' usando la clave foránea 'pedido_id'.
            ->join('pedidos', 'detallespedidos.pedido_id', '=', 'pedidos.id')
            // Filtramos los pedidos para incluir solo aquellos que representan una venta efectiva
            // (Completado, Enviado, Entregado). Se usan constantes del modelo Pedidos por claridad y mantenibilidad.
            ->whereIn('pedidos.status', [
                Pedidos::STATUS_COMPLETADO,
                Pedidos::STATUS_ENVIADO,
                Pedidos::STATUS_ENTREGADO,
            ])
            // Agrupamos los resultados por libro (ID y título) para que SUM() funcione correctamente por cada libro.
            ->groupBy('libros.id', 'libros.titulo')
            // Ordenamos los resultados por la suma calculada ('total_vendido') en orden descendente.
            ->orderByDesc('total_vendido')
            // Limitamos el resultado a los 10 libros más vendidos. Se podría usar paginate() si se necesitara paginación.
            ->take(10)
            // Ejecutamos la consulta y obtenemos los resultados como una colección.
            ->get();

        // 2.2. Clientes Recientes:
        // Consulta más simple para obtener los últimos usuarios registrados que son clientes.
        $clientesRecientes = User::where('rol', 'cliente') // Filtramos directamente por el atributo 'rol' del modelo User.
                               ->latest() // Ordena por la columna 'created_at' (por defecto) en orden descendente.
                               ->take(5) // Limitamos a los 5 más recientes.
                               ->get(); // Obtenemos la colección de usuarios.

        // 2.3. Otros datos (Contadores totales):
        // Contamos todos los registros en la tabla 'pedidos'.
        $totalPedidos = Pedidos::count();
        // Contamos todos los usuarios cuyo rol sea 'cliente'.
        $totalClientes = User::where('rol', 'cliente')->count();

        // 3. Pasar los datos recopilados a la vista del dashboard.
        // Se utiliza un array asociativo donde las claves serán los nombres de las variables
        // disponibles dentro de la vista Blade 'admin.dashboard'.
        return view('admin.dashboard', [
            'librosMasVendidos' => $librosMasVendidos, // Colección de los libros más vendidos.
            'clientesRecientes' => $clientesRecientes, // Colección de los últimos clientes registrados.
            'totalPedidos' => $totalPedidos,         // Número total de pedidos.
            'totalClientes' => $totalClientes,       // Número total de clientes.
        ]);
    }
}
