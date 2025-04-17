<?php
// filepath: app/Http/Controllers/AdminDashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Libros;
use App\Models\User;
use App\Models\Pedidos;
use App\Models\Detallespedidos;
use Illuminate\Support\Facades\DB;

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
        //Asegurarse de que solo los administradores accedan.
        if (Auth::user()->rol !== 'administrador') {
            // Si no es admin, redirigir a su perfil normal o a la home.
            return redirect()->route('profile.show')->with('error', 'Acceso no autorizado al panel de administración.');
        }

        //Obtener Datos para Estadísticas

        // Libros más vendidos
        $librosMasVendidos = Libros::select('libros.id', 'libros.titulo')
            ->selectRaw('SUM(detallespedidos.cantidad) as total_vendido')
            ->join('detallespedidos', 'libros.id', '=', 'detallespedidos.libro_id')
            ->join('pedidos', 'detallespedidos.pedido_id', '=', 'pedidos.id')
            // se filtra por estados de pedido considerados ventas
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

        // Clientes Recientes
        $clientesRecientes = User::where('rol', 'cliente') //filtro por rol
                               ->latest()
                               ->take(5)
                               ->get();

        // Otros datos
        $totalPedidos = Pedidos::count();
        $totalClientes = User::where('rol', 'cliente')->count();

        // Pasar los datos a la vista
        return view('admin.dashboard', [
            'librosMasVendidos' => $librosMasVendidos,
            'clientesRecientes' => $clientesRecientes,
            'totalPedidos' => $totalPedidos,
            'totalClientes' => $totalClientes,
       
        ]);
    }
}
