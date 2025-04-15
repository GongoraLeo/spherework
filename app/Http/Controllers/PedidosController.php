<?php

namespace App\Http\Controllers;

use App\Models\Pedidos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class PedidosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedidos = Pedidos::all();
        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pedidos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // ... (Autorización) ...

        $request->validate([
            'cliente_id' => 'required|exists:users,id',
            // 'fecha' => 'required|date', // ELIMINADO
            'status' => ['required', \Illuminate\Validation\Rule::in(array_keys(self::getStatusMap()))], // Usar status
            // Añadir validación para 'total' si se permite creación manual con total
        ]);

        // Crear sin 'fecha', se establecerá 'fecha_pedido' en checkout
        Pedidos::create($request->except('fecha')); // Asegurarse de no pasar 'fecha'
        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido creado correctamente.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Pedidos $pedidos)
    {
        $pedidos = Pedidos::find($pedidos->id);
        return view('pedidos.show', compact('pedidos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pedidos $pedidos)
    {
        $pedidos = Pedidos::find($pedidos->id);
        return view('pedidos.edit', compact('pedidos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pedidos $pedidos): RedirectResponse
    {
        // ... (Autorización) ...

        $request->validate([
            'cliente_id' => 'required|exists:users,id',
            // 'fecha' => 'required|date', // ELIMINADO
            'status' => ['required', \Illuminate\Validation\Rule::in(array_keys(self::getStatusMap()))], // Usar status
            // Añadir validación para 'total' si se permite edición manual con total
        ]);

        // Actualizar sin 'fecha'
        $pedidos->update($request->except('fecha')); // Asegurarse de no pasar 'fecha'
        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido actualizado correctamente.');
    }


    /**
     * Procesa el checkout del pedido pendiente del usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processCheckout(Request $request)
    {
        // 1. Obtener el usuario autenticado
        $user = Auth::user(); // O Auth::user() si usas el modelo User

        // 2. Encontrar el pedido PENDIENTE del usuario
        //    Usamos firstOrFail para que falle si no hay pedido pendiente
        try {
            $pedidoPendiente = Pedidos::where('cliente_id', $user->id) // Asegúrate que cliente_id sea la FK correcta
                ->where('status', Pedidos::STATUS_PENDIENTE)
                ->with('detallespedido') // Cargar detalles para calcular total
                ->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // No hay pedido pendiente, redirigir al carrito (que mostrará vacío) o a libros
            return redirect()->route('detallespedido.index')->with('error', 'No se encontró un pedido pendiente para procesar.');
        }

        // 3. Verificar que el carrito (detalles) no esté vacío
        if ($pedidoPendiente->detallespedido->isEmpty()) {
            return redirect()->route('detallespedido.index')->with('error', 'Tu carrito está vacío. Añade libros antes de proceder al pago.');
        }

        // --- Inicio Transacción (Opcional pero recomendado) ---
        // DB::beginTransaction();

        try {
            // 4. Calcular el total final
            $totalFinal = $pedidoPendiente->detallespedido->sum(function ($detalle) {
                return $detalle->cantidad * $detalle->precio; // Usa el precio guardado en el detalle
            });

            // 5. Actualizar el estado, total y fecha del pedido
            $pedidoPendiente->status = Pedidos::STATUS_COMPLETADO; // O STATUS_PROCESANDO si hay más pasos
            $pedidoPendiente->total = $totalFinal;
            $pedidoPendiente->fecha_pedido = now(); // Establece la fecha al completar
            $pedidoPendiente->save();

            // --- (Aquí iría la lógica de pago si la hubiera) ---
            // Si el pago falla, harías DB::rollBack(); y retornarías error

            // --- Commit Transacción (Opcional) ---
            // DB::commit();

            // 6. Redirigir a una página de éxito (pasando el ID del pedido completado)
            return redirect()->route('pedidos.checkout.success', ['pedido' => $pedidoPendiente->id])
                ->with('success', '¡Tu pedido ha sido realizado con éxito!');
        } catch (\Exception $e) {
            // --- Rollback Transacción en caso de error (Opcional) ---
            // DB::rollBack();

            // Loggear el error y redirigir con mensaje genérico
            // Log::error("Error en checkout: " . $e->getMessage()); // Necesitas use Illuminate\Support\Facades\Log;
            return redirect()->route('detallespedido.index')->with('error', 'Ocurrió un error al procesar tu pedido. Inténtalo de nuevo.');
        }
    }

    /**
     * Muestra la página de confirmación del pedido.
     *
     * @param  \App\Models\Pedidos $pedido
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showSuccess(Pedidos $pedido)
    {
        // Asegurarse que el usuario autenticado es el dueño del pedido que intenta ver
        if ($pedido->cliente_id !== Auth::id()) {
            // Si no es el dueño, redirigir o abortar
            // return redirect()->route('libros.index')->with('error', 'Acceso no autorizado.');
            abort(403, 'No tienes permiso para ver este pedido.');
        }

        // Cargar detalles y libros para mostrar resumen (opcional)
        $pedido->load('detallespedido.libro');

        return view('pedidos.success', compact('pedido')); // Necesitamos crear esta vista
    }

    // Helper para obtener los estados válidos (si usas validación con Rule::in)
    private static function getStatusMap(): array
    {
        return [
            Pedidos::STATUS_PENDIENTE => 'Pendiente',
            Pedidos::STATUS_PROCESANDO => 'Procesando',
            Pedidos::STATUS_COMPLETADO => 'Completado',
            Pedidos::STATUS_ENVIADO => 'Enviado',
            Pedidos::STATUS_ENTREGADO => 'Entregado',
            Pedidos::STATUS_CANCELADO => 'Cancelado',
        ];
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pedidos $pedidos)
    {
        $pedidos = Pedidos::find($pedidos->id);
        $pedidos->delete();
        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido eliminado correctamente.');
    }
}
