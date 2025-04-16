<?php
// filepath: c:\xampp\htdocs\spherework\app\Http\Controllers\DetallespedidosController.php

namespace App\Http\Controllers;

use App\Models\Detallespedidos;
use App\Models\Pedidos; // Necesitas el modelo Pedidos
use App\Models\Libros; // Podría ser útil para obtener precio si no viene en request
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Necesario para obtener el usuario y check()
use Illuminate\View\View;             // Para type hinting
use Illuminate\Http\RedirectResponse; // Para type hinting
use Illuminate\Support\Facades\Log;    // Para loggear errores (opcional)

class DetallespedidosController extends Controller
{
    /**
     * Display the user's current shopping cart.
     * Muestra el carrito de compras actual del usuario.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        // --- Añadida comprobación de autenticación también en index ---
        // Es buena práctica asegurarse que solo usuarios logueados vean su carrito.
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver tu carrito.');
        }
        // --- Fin comprobación ---

        $user = Auth::user();
        $total = 0;
        $detallespedidos = collect(); // Inicializar como colección vacía

        // Buscar el pedido PENDIENTE del usuario actual
        $pedidoPendiente = Pedidos::where('cliente_id', $user->id)
                                 ->where('status', Pedidos::STATUS_PENDIENTE)
                                 ->first();

        if ($pedidoPendiente) {
            // Si existe un pedido pendiente, obtener sus detalles con los libros asociados
            $detallespedidos = Detallespedidos::where('pedido_id', $pedidoPendiente->id)
                                            ->with(['libro.autor', 'libro.editorial']) // Cargar libro con relaciones
                                            ->get();

            // Calcular el total del carrito
            $total = $detallespedidos->sum(function ($detalle) {
                // Asegurarse que precio y cantidad sean numéricos antes de multiplicar
                $price = is_numeric($detalle->precio) ? $detalle->precio : 0;
                $quantity = is_numeric($detalle->cantidad) ? $detalle->cantidad : 0;
                return $price * $quantity;
            });
        }

        // Devolver la vista del carrito con los detalles y el total
        return view('detallespedidos.index', compact('detallespedidos', 'total'));
    }

    /**
     * Show the form for creating a new resource.
     * ESTE MÉTODO NO SE USA PARA EL CARRITO.
     */
    public function create()
    {
        // Redirigir a libros o mostrar error, ya que la creación se hace desde el catálogo.
        return redirect()->route('libros.index')->with('info', 'Para añadir libros, usa el botón "Añadir al Carrito".');
    }

    /**
     * Store a newly created resource in storage (Add item to cart).
     * Añade un item al carrito. Crea un pedido PENDIENTE si no existe.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'libro_id' => 'required|exists:libros,id',
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0', // Validar el precio que viene del formulario
        ]);

        // --- INICIO MODIFICACIÓN: Comprobar autenticación ---
        if (!Auth::check()) {
            // Si no está autenticado, redirigir al login.
            Log::warning("Intento de añadir al carrito sin autenticar."); // Usar warning o error
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para añadir libros al carrito.');
        }
        // --- FIN MODIFICACIÓN ---

        $user = Auth::user(); // Ahora es seguro llamar a Auth::user()

        // Buscar o crear el pedido PENDIENTE para este usuario.
        // **PUNTO CRÍTICO**: Aquí se asigna 'cliente_id' si se crea un nuevo pedido.
        try {
            $pedidoPendiente = Pedidos::firstOrCreate(
                [
                    'cliente_id' => $user->id, // Asigna el ID del usuario actual
                    'status' => Pedidos::STATUS_PENDIENTE,
                ],
                [
                    // Valores adicionales si se crea (opcional)
                    // 'total' => 0,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Error en firstOrCreate Pedido: " . $e->getMessage(), ['user_id' => $user->id]);
            return redirect()->back()->with('error', 'Ocurrió un error al acceder a tu carrito. Inténtalo de nuevo.');
        }


        // Buscar si el libro ya existe en este pedido pendiente
        $detalleExistente = Detallespedidos::where('pedido_id', $pedidoPendiente->id)
                                         ->where('libro_id', $request->libro_id)
                                         ->first();

        if ($detalleExistente) {
            // Si existe, actualizar cantidad
            $detalleExistente->cantidad += $request->cantidad;
            // Opcional: decidir si actualizar el precio al guardado o mantener el original
            // $detalleExistente->precio = $request->precio;
            $detalleExistente->save();
            $message = 'Cantidad actualizada en el carrito.';
        } else {
            // Si no existe, crear nuevo detalle
            Detallespedidos::create([
                'pedido_id' => $pedidoPendiente->id,
                'libro_id' => $request->libro_id,
                'cantidad' => $request->cantidad,
                'precio' => $request->precio, // Guardar el precio al momento de añadir
            ]);
            $message = 'Libro añadido al carrito correctamente.';
        }

        // Redirigir al índice del carrito
        return redirect()->route('detallespedidos.index')
                       ->with('success', $message);
    }

    /**
     * Display the specified resource.
     * NO suele usarse para detalles individuales del carrito.
     */
    public function show(Detallespedidos $detallespedidos) // Mantenido plural por ruta {detallespedidos}
    {
         // Redirigir al índice del carrito.
         return redirect()->route('detallespedidos.index');
    }

    /**
     * Show the form for editing the specified resource.
     * NO suele usarse, la edición (cantidad) se hace en la vista index.
     */
    public function edit(Detallespedidos $detallespedidos) // Mantenido plural por ruta {detallespedidos}
    {
        // Redirigir al índice del carrito.
        return redirect()->route('detallespedidos.index');
    }

    /**
     * Update the specified resource in storage (Update quantity in cart).
     * Actualiza la cantidad de un item en el carrito.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Detallespedidos  $detallespedidos // Route model binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Detallespedidos $detallespedidos): RedirectResponse // Mantenido plural por ruta {detallespedidos}
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1', // Validar nueva cantidad
        ]);

        // --- Autorización ---
        // Añadir comprobación Auth::check() aquí también por seguridad
        if (!Auth::check()) {
             return redirect()->route('login')->with('error', 'Debes iniciar sesión para modificar tu carrito.');
        }
        $user = Auth::user();

        // Cargar la relación 'pedido' si no está ya cargada
        $detallespedidos->loadMissing('pedido');
        $pedidoDelDetalle = $detallespedidos->pedido;

        // Verificar que el detalle pertenece a un pedido PENDIENTE del usuario actual
        if (!$pedidoDelDetalle || $pedidoDelDetalle->cliente_id !== $user->id || $pedidoDelDetalle->status !== Pedidos::STATUS_PENDIENTE) {
             Log::warning("Intento no autorizado de actualizar detalle ID {$detallespedidos->id} por usuario ID {$user->id}");
             return redirect()->route('detallespedidos.index')->with('error', 'No se pudo actualizar el item.');
        }
        // --- Fin Autorización ---

        // Actualizar la cantidad
        $detallespedidos->cantidad = $request->cantidad;
        $detallespedidos->save();

        // Redirigir al índice del carrito
        return redirect()->route('detallespedidos.index')
                       ->with('success', 'Cantidad actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage (Remove item from cart).
     * Elimina un item del carrito.
     *
     * @param  \App\Models\Detallespedidos  $detallespedidos // Route model binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Detallespedidos $detallespedidos): RedirectResponse // Mantenido plural por ruta {detallespedidos}
    {
         // --- Autorización ---
         // Añadir comprobación Auth::check() aquí también por seguridad
        if (!Auth::check()) {
             return redirect()->route('login')->with('error', 'Debes iniciar sesión para modificar tu carrito.');
        }
        $user = Auth::user();

        // Cargar la relación 'pedido' si no está ya cargada
        $detallespedidos->loadMissing('pedido');
        $pedidoDelDetalle = $detallespedidos->pedido;

        // Verificar que el detalle pertenece a un pedido PENDIENTE del usuario actual
        if (!$pedidoDelDetalle || $pedidoDelDetalle->cliente_id !== $user->id || $pedidoDelDetalle->status !== Pedidos::STATUS_PENDIENTE) {
             Log::warning("Intento no autorizado de eliminar detalle ID {$detallespedidos->id} por usuario ID {$user->id}");
             return redirect()->route('detallespedidos.index')->with('error', 'No se pudo eliminar el item.');
        }
         // --- Fin Autorización ---

        // Eliminar el detalle
        $detallespedidos->delete();

        // Opcional: Si el pedido queda vacío después de eliminar, podrías eliminar el pedido PENDIENTE también.
        // if ($pedidoDelDetalle->detallespedido()->count() === 0) {
        //     $pedidoDelDetalle->delete();
        // }

        // Redirigir al índice del carrito
        return redirect()->route('detallespedidos.index')
                       ->with('success', 'Item eliminado del carrito.');
    }
}
