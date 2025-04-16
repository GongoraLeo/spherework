<?php
// filepath: app/Http/Controllers/ClientesController.php

namespace App\Http\Controllers;

use App\Models\User; // Importa el modelo User
use App\Models\Pedidos;
use App\Models\Comentarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource (Admin view).
     * Muestra la lista de clientes para el administrador.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        // 1. Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // 2. Obtener clientes (paginado es buena idea si son muchos)
        $clientes = User::where('rol', 'cliente')
                        ->orderBy('name')
                        ->paginate(20); // Ajusta el número por página

        // 3. Mostrar vista de índice de clientes (admin)
        return view('admin.clientes.index', compact('clientes')); // Necesitarás crear esta vista
    }


    /**
     * Display the specified client's profile (Admin view).
     * Muestra el perfil de un cliente específico, visto por un administrador.
     *
     * @param  \App\Models\User $cliente // Route Model Binding: Laravel buscará un User con el ID de la ruta
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(User $cliente): View|RedirectResponse
    {
        // 1. Autorización: Verificar que el usuario LOGUEADO sea admin
        if (Auth::user()->rol !== 'administrador') {
            Log::warning("Intento no autorizado de ver cliente ID {$cliente->id} por usuario ID " . Auth::id());
            // Redirigir al perfil normal si no es admin
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // 2. Verificación: Asegurarse que el usuario que se intenta ver ($cliente) sea realmente un cliente
        if ($cliente->rol !== 'cliente') {
            Log::info("Admin intentó ver perfil de usuario ID {$cliente->id} que no es cliente (Rol: {$cliente->rol}).");
            // Redirigir a la lista de clientes si el ID no corresponde a un cliente
            return redirect()->route('clientes.index')->with('error', 'El usuario especificado no es un cliente.');
        }

        // 3. Cargar Datos del Cliente (Eager Loading para eficiencia)
        //    Cargamos las mismas relaciones que en ProfileController@show
        $cliente->load([
            'pedidos' => function ($query) {
                // Cargar pedidos recientes (excluyendo pendientes, por ejemplo)
                $query->where('status', '!=', Pedidos::STATUS_PENDIENTE)
                      ->latest('fecha_pedido') // Ordenar por fecha de pedido más reciente
                      ->take(5); // Limitar a 5
            },
            'comentarios' => function ($query) {
                // Cargar comentarios recientes con el libro asociado
                $query->with('libro')
                      ->latest() // Ordenar por fecha de creación más reciente
                      ->take(10); // Limitar a 10
            }
        ]);

        // Opcional: Extraer las colecciones cargadas para pasarlas explícitamente (más claro)
        $pedidos = $cliente->pedidos;
        $comentarios = $cliente->comentarios;

        // 4. Mostrar la Vista Específica del Admin
        //    Pasamos el objeto $cliente y sus datos cargados
        return view('admin.clientes.show', compact('cliente', 'pedidos', 'comentarios'));
    }

    // --- Otros métodos CRUD para Clientes (edit, update, destroy) si son necesarios para el admin ---
    // public function edit(User $cliente) { ... }
    // public function update(Request $request, User $cliente) { ... }
    // public function destroy(User $cliente) { ... }

}
