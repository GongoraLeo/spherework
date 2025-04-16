<?php
// filepath: c:\xampp\htdocs\spherework\app\Http\Controllers\PedidosController.php

namespace App\Http\Controllers;

use App\Models\Pedidos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB; // Para transacciones (opcional)
use Illuminate\Validation\Rule;     // Para validación de status

class PedidosController extends Controller
{
    /**
     * Display a listing of the resource (For Admin).
     * Muestra una lista de todos los pedidos (generalmente para administradores).
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Autorización: Solo para administradores
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
            // O abort(403);
        }

        // Cargar pedidos con relaciones para evitar N+1
        $pedidos = Pedidos::with('cliente')->latest('fecha_pedido')->paginate(20); // O get()
        return view('pedidos.index', compact('pedidos')); // Asume que existe la vista pedidos.index
    }

    /**
     * Show the form for creating a new resource (For Admin).
     * Muestra el formulario para crear un pedido manualmente (admin).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        // Autorización: Solo para administradores
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('pedidos.index')->with('error', 'Acceso no autorizado.');
        }
        // Necesitarías pasar lista de clientes (Users) si el admin lo selecciona
        // $clientes = \App\Models\User::where('rol', 'cliente')->orderBy('name')->get();
        // return view('pedidos.create', compact('clientes'));
        return view('pedidos.create'); // Asume que existe la vista pedidos.create
    }

    /**
     * Store a newly created resource in storage (For Admin).
     * Guarda un pedido creado manualmente (admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Autorización: Solo para administradores
        if (Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        $request->validate([
            'cliente_id' => 'required|exists:users,id', // Asegura que el cliente exista
            'status' => ['required', Rule::in(array_keys(self::getStatusMap()))], // Valida contra estados definidos
            'total' => 'nullable|numeric|min:0', // Total podría ser opcional o calculado después
            'fecha_pedido' => 'nullable|date', // Fecha podría ser opcional
        ]);

        try {
            Pedidos::create($request->all()); // Crea el pedido con los datos validados
            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido creado correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al crear pedido manual: " . $e->getMessage(), $request->all());
            return back()->with('error', 'Ocurrió un error al crear el pedido.')->withInput();
        }
    }


    /**
     * Display the specified resource.
     * Muestra los detalles de un pedido específico (para usuario o admin).
     *
     * @param  \App\Models\Pedidos $pedido // Route Model Binding
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Pedidos $pedido): View|RedirectResponse
    {
        // 1. Autorización: Verificar que el usuario autenticado sea el dueño del pedido O un administrador.
        $user = Auth::user();

        // **IMPORTANTE**: Asegúrate que $pedido->cliente_id TENGA un valor válido en la BD.
        // Si $pedido->cliente_id es NULL, esta condición fallará para el cliente.
        if ($user->id !== $pedido->cliente_id && $user->rol !== 'administrador') {
            // Si no es el dueño ni admin, redirigir.
            return redirect()->route('profile.show')->with('error', 'No tienes permiso para ver este pedido.');
        }

        // 2. Eager Loading: Cargar relaciones necesarias.
        $pedido->load(['cliente', 'detallespedido.libro']);

        // 3. Retornar la vista con los datos del pedido.
        return view('pedidos.show', compact('pedido'));
    }


    /**
     * Show the form for editing the specified resource (For Admin).
     * Muestra el formulario para editar un pedido (admin).
     *
     * @param  \App\Models\Pedidos $pedidos // Mantenido plural por ruta {pedidos}
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Pedidos $pedidos): View|RedirectResponse // Mantenido plural por ruta {pedidos}
    {
        // Autorización: Solo para administradores
        if (Auth::user()->rol !== 'administrador') {
             return redirect()->route('pedidos.index')->with('error', 'Acceso no autorizado.');
        }

        // No necesitas find(), Route Model Binding ya inyectó el modelo en $pedidos
        // $pedidos = Pedidos::find($pedidos->id); // REDUNDANTE

        // Pasar los estados posibles a la vista para un select
        $statuses = self::getStatusMap();

        return view('pedidos.edit', compact('pedidos', 'statuses')); // Asume que existe la vista pedidos.edit
    }

    /**
     * Update the specified resource in storage (For Admin).
     * Actualiza un pedido (admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pedidos $pedidos // Mantenido plural por ruta {pedidos}
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Pedidos $pedidos): RedirectResponse // Mantenido plural por ruta {pedidos}
    {
        // Autorización: Solo para administradores
        if (Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        $request->validate([
            // No permitir cambiar cliente_id en update
            'status' => ['required', Rule::in(array_keys(self::getStatusMap()))],
            'total' => 'nullable|numeric|min:0', // Permitir actualizar total si es necesario
            'fecha_pedido' => 'nullable|date', // Permitir actualizar fecha si es necesario
        ]);

        // No necesitas find(), Route Model Binding ya inyectó el modelo en $pedidos
        // $pedidos = Pedidos::find($pedidos->id); // REDUNDANTE

        try {
            // Actualizar solo los campos permitidos
            $pedidos->update($request->only(['status', 'total', 'fecha_pedido']));
            return redirect()->route('pedidos.index') // O a pedidos.show
                ->with('success', 'Pedido actualizado correctamente.');
        } catch (\Exception $e) {
             Log::error("Error al actualizar pedido ID {$pedidos->id}: " . $e->getMessage(), $request->all());
             return back()->with('error', 'Ocurrió un error al actualizar el pedido.')->withInput();
        }
    }


    /**
     * Procesa el checkout del pedido pendiente del usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processCheckout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Usar transacción para asegurar atomicidad
        DB::beginTransaction();

        try {
            // Encontrar el pedido PENDIENTE del usuario (con bloqueo para evitar concurrencia si es necesario)
            $pedidoPendiente = Pedidos::where('cliente_id', $user->id)
                ->where('status', Pedidos::STATUS_PENDIENTE)
                ->with('detallespedido') // Cargar detalles para calcular total
                // ->lockForUpdate() // Opcional: Bloquear fila durante la transacción
                ->firstOrFail(); // Falla si no existe

            // Verificar que el carrito no esté vacío
            if ($pedidoPendiente->detallespedido->isEmpty()) {
                 DB::rollBack(); // Revertir transacción si está vacía
                return redirect()->route('detallespedido.index')->with('error', 'Tu carrito está vacío.');
            }

            // Calcular el total final desde los detalles
            $totalFinal = $pedidoPendiente->detallespedido->sum(function ($detalle) {
                return $detalle->cantidad * $detalle->precio;
            });

            // Actualizar el pedido
            $pedidoPendiente->status = Pedidos::STATUS_COMPLETADO; // O PROCESANDO
            $pedidoPendiente->total = $totalFinal;
            $pedidoPendiente->fecha_pedido = now(); // Establecer fecha/hora de completado
            $pedidoPendiente->save();

            // --- (Lógica de Pago iría aquí) ---
            // Si el pago falla: throw new \Exception('Pago fallido');

            // Confirmar transacción
            DB::commit();

            // Redirigir a página de éxito
            return redirect()->route('pedidos.checkout.success', ['pedido' => $pedidoPendiente->id])
                ->with('success', '¡Tu pedido ha sido realizado con éxito!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack(); // Revertir si no se encontró pedido
            return redirect()->route('detallespedido.index')->with('error', 'No se encontró un pedido pendiente.');
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir en cualquier otro error
            Log::error("Error en checkout para user {$user->id}: " . $e->getMessage());
            return redirect()->route('detallespedido.index')->with('error', 'Ocurrió un error al procesar tu pedido. Inténtalo de nuevo.');
        }
    }

    /**
     * Muestra la página de confirmación del pedido.
     *
     * @param  \App\Models\Pedidos $pedido
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showSuccess(Pedidos $pedido): View|RedirectResponse
    {
        // Autorización: Asegurarse que el usuario autenticado es el dueño
        if ($pedido->cliente_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver esta confirmación.');
        }

        // Asegurarse que el pedido esté al menos completado (o estado posterior)
        if ($pedido->status === Pedidos::STATUS_PENDIENTE) {
             return redirect()->route('profile.show')->with('error', 'Este pedido aún no ha sido completado.');
        }

        // Cargar detalles y libros para mostrar resumen
        $pedido->load('detallespedido.libro');

        return view('pedidos.success', compact('pedido')); // Asume que existe la vista pedidos.success
    }

    /**
     * Helper para obtener los estados válidos y sus nombres.
     *
     * @return array<string, string>
     */
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
     * Remove the specified resource from storage (For Admin).
     * Elimina un pedido (admin).
     *
     * @param  \App\Models\Pedidos $pedidos // Mantenido plural por ruta {pedidos}
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Pedidos $pedidos): RedirectResponse // Mantenido plural por ruta {pedidos}
    {
        // Autorización: Solo para administradores
        if (Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        // No necesitas find(), Route Model Binding ya inyectó el modelo en $pedidos
        // $pedidos = Pedidos::find($pedidos->id); // REDUNDANTE

        try {
            // Considerar qué pasa con los detalles del pedido. ¿Borrado en cascada?
            // Si no hay borrado en cascada, eliminarlos primero o manejar la restricción.
            // $pedidos->detallespedido()->delete(); // Si es necesario
            $pedidos->delete();
            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
             // Capturar error si hay restricciones (ej. si detalles no se borran)
             Log::error("Error de BD al eliminar pedido ID {$pedidos->id}: " . $e->getMessage());
             return redirect()->route('pedidos.index')
                ->with('error', 'No se pudo eliminar el pedido debido a restricciones.');
        } catch (\Exception $e) {
             Log::error("Error al eliminar pedido ID {$pedidos->id}: " . $e->getMessage());
             return redirect()->route('pedidos.index')
                ->with('error', 'Ocurrió un error al eliminar el pedido.');
        }
    }
}
