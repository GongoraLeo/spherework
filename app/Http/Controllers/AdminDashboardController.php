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
use Illuminate\Support\Facades\DB;

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
     * Primero, verifica si el usuario autenticado es un administrador utilizando `Auth::user()->rol`.
     * Si lo es, recopila diversas estadísticas:
     * - Libros más vendidos: Realiza una consulta compleja uniendo `libros`, `detallespedidos` y `pedidos`,
     *   filtrando por estados de pedido completados (`STATUS_COMPLETADO`, `STATUS_ENVIADO`, `STATUS_ENTREGADO`),
     *   agrupando por libro y sumando las cantidades (`SUM(detallespedidos.cantidad)`),
     *   ordenando descendentemente por el total vendido y tomando los 10 primeros.
     * - Clientes recientes: Obtiene los últimos 5 usuarios registrados con `rol` 'cliente', ordenados por fecha de creación descendente.
     * - Totales: Cuenta el número total de registros en las tablas `pedidos` y `users` (filtrando por rol 'cliente').
     * Finalmente, renderiza la vista 'admin.dashboard' pasándole estos datos (`librosMasVendidos`, `clientesRecientes`, `totalPedidos`, `totalClientes`).
     * Si el usuario no es administrador, lo redirige a su perfil estándar (`profile.show`) con un mensaje de error.
     * Se eligió redirigir a `profile.show` en lugar de `profile.entry` para evitar posibles bucles de redirección.
     *
     * @param Request $request La solicitud HTTP entrante (no se usa directamente aquí, pero es estándar).
     * @return View|RedirectResponse Retorna la vista del dashboard para administradores o una redirección si no está autorizado.
     */
    public function index(Request $request): View|RedirectResponse
    {
        // 1. Autorización: Se asegura de que solo los administradores puedan acceder.
        // Verifica el rol del usuario autenticado actualmente.
        if (Auth::user()->rol !== 'administrador') {
            // Si no es admin, redirige a su perfil normal (profile.show).
            return redirect()->route('profile.show')->with('error', 'Acceso no autorizado al panel de administración.');
        }

        // 2. Obtener Datos para Estadísticas y Resúmenes

        // 2.1. Libros más vendidos:
        // Realiza una consulta para determinar los libros con más unidades vendidas en pedidos completados/enviados/entregados.
        $librosMasVendidos = Libros::select('libros.id', 'libros.titulo') // Selecciona ID y título del libro.
            // Usa selectRaw para calcular la suma de cantidades vendidas y le da el alias 'total_vendido'.
            ->selectRaw('SUM(detallespedidos.cantidad) as total_vendido')
            // Une la tabla 'libros' con 'detallespedidos' usando la clave foránea 'libro_id'.
            ->join('detallespedidos', 'libros.id', '=', 'detallespedidos.libro_id')
            // Une 'detallespedidos' con 'pedidos' usando la clave foránea 'pedido_id'.
            ->join('pedidos', 'detallespedidos.pedido_id', '=', 'pedidos.id')
            // Filtra los pedidos para incluir solo aquellos que representan una venta efectiva.
            // Utiliza las constantes del modelo Pedidos para mayor claridad.
            ->whereIn('pedidos.status', [
                Pedidos::STATUS_COMPLETADO,
                Pedidos::STATUS_ENVIADO,
                Pedidos::STATUS_ENTREGADO,
            ])
            // Agrupa los resultados por libro (ID y título) para que SUM() funcione correctamente por cada libro.
            ->groupBy('libros.id', 'libros.titulo')
            // Ordena los resultados por la suma calculada ('total_vendido') en orden descendente.
            ->orderByDesc('total_vendido')
            // Limita el resultado a los 10 libros más vendidos.
            ->take(10)
            // Ejecuta la consulta y obtiene los resultados como una colección.
            ->get();

        // 2.2. Clientes Recientes:
        // Realiza una consulta para obtener los últimos usuarios registrados que son clientes.
        $clientesRecientes = User::where('rol', 'cliente') // Filtra directamente por el atributo 'rol' del modelo User.
                               ->latest() // Ordena por 'created_at' descendente.
                               ->take(5) // Limita a los 5 más recientes.
                               ->get(); // Obtiene la colección de usuarios.

        // 2.3. Otros datos (Contadores totales):
        // Cuenta todos los registros en la tabla 'pedidos'.
        $totalPedidos = Pedidos::count();
        // Cuenta todos los usuarios cuyo rol sea 'cliente'.
        $totalClientes = User::where('rol', 'cliente')->count();

        // 3. Pasar los datos recopilados a la vista del dashboard.
        // Utiliza un array asociativo donde las claves serán los nombres de las variables
        // disponibles dentro de la vista Blade 'admin.dashboard'.
        return view('admin.dashboard', [
            'librosMasVendidos' => $librosMasVendidos, // Colección de los libros más vendidos.
            'clientesRecientes' => $clientesRecientes, // Colección de los últimos clientes registrados.
            'totalPedidos' => $totalPedidos,         // Número total de pedidos.
            'totalClientes' => $totalClientes,       // Número total de clientes.
        ]);
    }
}
