<?php
// filepath: app/Http/Controllers/AdminDashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Libros;
use App\Models\User; // Usaremos el modelo User para clientes
use App\Models\Pedidos;
use App\Models\Detallespedidos;
use Illuminate\Support\Facades\DB; // Para consultas más complejas si es necesario

class AdminDashboardController extends Controller
{
    /**
     * Muestra el panel de control del administrador.
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        // 1. Autorización: Asegurarse de que solo los administradores accedan.
        if (Auth::user()->rol !== 'administrador') {
            // Si no es admin, redirigir a su perfil normal o a la home.
            return redirect()->route('profile.show')->with('error', 'Acceso no autorizado al panel de administración.');
            // O abort(403, 'Acceso denegado');
        }

        // 2. Obtener Datos para Estadísticas

        // a) Libros más vendidos (Ejemplo: Top 10)
        //    Necesitamos sumar la cantidad de Detallespedidos asociados a pedidos completados/enviados/entregados.
        $librosMasVendidos = Libros::select('libros.id', 'libros.titulo')
            // Usamos selectRaw para sumar la cantidad de la tabla detallespedidos
            ->selectRaw('SUM(detallespedidos.cantidad) as total_vendido')
            // Unimos las tablas necesarias
            ->join('detallespedidos', 'libros.id', '=', 'detallespedidos.libro_id')
            ->join('pedidos', 'detallespedidos.pedido_id', '=', 'pedidos.id')
            // Filtramos por estados de pedido que consideramos "venta"
            ->whereIn('pedidos.status', [
                Pedidos::STATUS_COMPLETADO,
                Pedidos::STATUS_ENVIADO,
                Pedidos::STATUS_ENTREGADO,
            ])
            // Agrupamos por libro para sumar las cantidades
            ->groupBy('libros.id', 'libros.titulo')
            // Ordenamos por la cantidad vendida descendente
            ->orderByDesc('total_vendido')
            // Limitamos a los 10 primeros (o usa paginate())
            ->take(10)
            ->get();

        // b) Clientes Recientes (Ejemplo: Últimos 5 registrados)
        $clientesRecientes = User::where('rol', 'cliente') // Filtramos por rol
                               ->latest() // Ordenar por fecha de creación descendente
                               ->take(5)   // Tomar los 5 más recientes
                               ->get();

        // c) Otros datos que podrías querer:
        //    - Número total de pedidos
        //    - Ingresos totales/mensuales
        //    - Número de usuarios registrados
        $totalPedidos = Pedidos::count();
        $totalClientes = User::where('rol', 'cliente')->count();
        // ... (añade más consultas según necesites) ...


        // 3. Pasar los datos a la vista
        return view('admin.dashboard', [
            'librosMasVendidos' => $librosMasVendidos,
            'clientesRecientes' => $clientesRecientes,
            'totalPedidos' => $totalPedidos,
            'totalClientes' => $totalClientes,
            // ... pasa otras variables ...
        ]);
    }
}
