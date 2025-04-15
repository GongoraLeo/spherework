<?php

namespace App\Http\Controllers;

use App\Models\Detallespedidos;
use App\Models\Pedidos; // Necesitas el modelo Pedidos
use App\Models\Libros; // Podría ser útil para obtener precio si no viene en request
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Necesario para obtener el usuario
use Illuminate\View\View;             // Para type hinting
use Illuminate\Http\RedirectResponse; // Para type hinting

class DetallespedidosController extends Controller
{
    /**
     * Display the user's current shopping cart.
     * CORREGIDO: Usa nombres plurales para ruta/vista y variable de colección.
     * CORREGIDO: Usa 'status' (asumiendo que la migración add_status... se ejecutó).
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $user = Auth::user();
        $total = 0;
        // Nombre de variable en plural para la colección
        $detallespedidos = collect(); // Inicializar como colección vacía

        // CORREGIDO: Buscar usando 'status' y la constante del modelo
        $pedidoPendiente = Pedidos::where('cliente_id', $user->id)
                                 ->where('status', Pedidos::STATUS_PENDIENTE) // <--- Usa 'status'
                                 ->first();

        if ($pedidoPendiente) {
            // Usar el nombre plural para la colección
            $detallespedidos = Detallespedidos::where('pedido_id', $pedidoPendiente->id)
                                            ->with(['libro.autor', 'libro.editorial']) // Cargar libro con relaciones
                                            ->get();

            $total = $detallespedidos->sum(function ($detalle) {
                $price = is_numeric($detalle->precio) ? $detalle->precio : 0;
                $quantity = is_numeric($detalle->cantidad) ? $detalle->cantidad : 0;
                return $price * $quantity;
            });
        }

        // CORREGIDO: Nombre de vista plural y variable plural
        return view('detallespedidos.index', compact('detallespedidos', 'total'));
    }

    /**
     * Show the form for creating a new resource.
     * ESTE MÉTODO NO SE USA PARA EL CARRITO.
     */
    public function create()
    {
        // Redirigir a libros o mostrar error.
        return redirect()->route('libros.index')->with('info', 'Para añadir libros, usa el botón "Añadir al Carrito".');
    }

    /**
     * Store a newly created resource in storage (Add item to cart).
     * CORREGIDO: Usa 'status' en firstOrCreate.
     * CORREGIDO: Redirige a ruta plural.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'libro_id' => 'required|exists:libros,id',
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();

        // CORREGIDO: Buscar o crear usando 'status'
        $pedidoPendiente = Pedidos::firstOrCreate(
            [
                'cliente_id' => $user->id,
                'status' => Pedidos::STATUS_PENDIENTE, // <--- Usa 'status'
            ],
            [
                // 'fecha_pedido' => now(), // Se establece al completar el pedido
                // 'total' => 0, // Podría inicializarse aquí si se desea
            ]
        );

        $detalleExistente = Detallespedidos::where('pedido_id', $pedidoPendiente->id)
                                         ->where('libro_id', $request->libro_id)
                                         ->first();

        if ($detalleExistente) {
            $detalleExistente->cantidad += $request->cantidad;
            $detalleExistente->save();
            $message = 'Cantidad actualizada en el carrito.';
        } else {
            Detallespedidos::create([
                'pedido_id' => $pedidoPendiente->id,
                'libro_id' => $request->libro_id,
                'cantidad' => $request->cantidad,
                'precio' => $request->precio,
            ]);
            $message = 'Libro añadido al carrito correctamente.';
        }

        // CORREGIDO: Redirigir a ruta plural
        return redirect()->route('detallespedidos.index')
                       ->with('success', $message);
    }

    /**
     * Display the specified resource.
     * NO suele usarse para detalles individuales del carrito.
     */
    public function show(Detallespedidos $detallespedidos) // Nombre de variable plural
    {
         // CORREGIDO: Redirige a ruta plural
         return redirect()->route('detallespedidos.index');
    }

    /**
     * Show the form for editing the specified resource.
     * NO suele usarse, la edición (cantidad) se hace en la vista index.
     */
    public function edit(Detallespedidos $detallespedidos) // Nombre de variable plural
    {
        // CORREGIDO: Redirige a ruta plural
        return redirect()->route('detallespedidos.index');
    }

    /**
     * Update the specified resource in storage (Update quantity in cart).
     * CORREGIDO: Usa 'status' en autorización.
     * CORREGIDO: Redirige a ruta plural.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Detallespedidos  $detallespedidos // Route model binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Detallespedidos $detallespedidos): RedirectResponse // Nombre de variable plural
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        // --- Autorización ---
        $user = Auth::user();
        $pedidoDelDetalle = $detallespedidos->pedido;

        // CORREGIDO: Verificar usando 'status'
        if (!$pedidoDelDetalle || $pedidoDelDetalle->cliente_id !== $user->id || $pedidoDelDetalle->status !== Pedidos::STATUS_PENDIENTE) { // <--- Usa 'status'
             // CORREGIDO: Redirige a ruta plural
             return redirect()->route('detallespedidos.index')->with('error', 'No se pudo actualizar el item.');
        }
        // --- Fin Autorización ---

        $detallespedidos->cantidad = $request->cantidad;
        $detallespedidos->save(); // Asegurarse que esto funciona

        // CORREGIDO: Redirige a ruta plural
        return redirect()->route('detallespedidos.index')
                       ->with('success', 'Cantidad actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage (Remove item from cart).
     * CORREGIDO: Usa 'status' en autorización.
     * CORREGIDO: Redirige a ruta plural.
     *
     * @param  \App\Models\Detallespedidos  $detallespedidos // Route model binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Detallespedidos $detallespedidos): RedirectResponse // Nombre de variable plural
    {
         // --- Autorización ---
        $user = Auth::user();
        $pedidoDelDetalle = $detallespedidos->pedido;

        // CORREGIDO: Verificar usando 'status'
        if (!$pedidoDelDetalle || $pedidoDelDetalle->cliente_id !== $user->id || $pedidoDelDetalle->status !== Pedidos::STATUS_PENDIENTE) { // <--- Usa 'status'
             // CORREGIDO: Redirige a ruta plural
             return redirect()->route('detallespedidos.index')->with('error', 'No se pudo eliminar el item.');
        }
         // --- Fin Autorización ---

        $detallespedidos->delete();

        // CORREGIDO: Redirige a ruta plural
        return redirect()->route('detallespedidos.index')
                       ->with('success', 'Item eliminado del carrito.');
    }
}
