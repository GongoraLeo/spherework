<?php
// filepath: app/Http/Controllers/PedidosController.php

namespace App\Http\Controllers;

use App\Models\Pedidos; // Modelo Eloquent para interactuar con la tabla 'pedidos'.
use Illuminate\Http\Request; // Objeto para manejar las solicitudes HTTP entrantes.
use Illuminate\Support\Facades\Auth; // Fachada para verificar autenticación y obtener usuario/rol.
use Illuminate\Http\RedirectResponse; // Para especificar el tipo de retorno de redirecciones.
use Illuminate\Support\Facades\Log;    // Fachada para registrar mensajes de error o información.
use Illuminate\View\View;             // Para especificar el tipo de retorno de vistas.
use Illuminate\Support\Facades\DB;    // Fachada DB, usada aquí para transacciones en el checkout.
use Illuminate\Validation\Rule;     // Clase para construir reglas de validación, usada para el campo 'status'.
use App\Models\User; // Necesario para la posible carga de clientes en create() (actualmente comentado).
use App\Models\Detallespedidos; // Necesario para la relación en processCheckout() y show().

/**
 * Class PedidosController
 *
 * Gestiona las operaciones relacionadas con los pedidos. Incluye funcionalidades
 * para administradores (listar, crear, editar, actualizar, eliminar pedidos) y
 * para clientes (ver detalles de sus pedidos, procesar checkout, ver confirmación).
 *
 * @package App\Http\Controllers
 */
class PedidosController extends Controller
{
    /**
     * Muestra una lista paginada de todos los pedidos.
     *
     * Esta acción está pensada principalmente para administradores.
     * Recupera todos los pedidos, carga la relación 'cliente' para mostrar el nombre,
     * los ordena por fecha de pedido descendente y los pagina.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'pedidos.index' o redirige si no es admin (implícito).
     */
    public function index(): View|RedirectResponse // Modificado para incluir RedirectResponse por la autorización
    {
        // 1. Autorización: Verificar si el usuario autenticado es un administrador.
        // Se comprueba el rol del usuario logueado.
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
            // Si no es admin, redirige a la entrada del perfil con un mensaje de error.
            // Se podría usar abort(403) si se prefiere detener la ejecución.
             return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado para ver todos los pedidos.');
        }

        // 2. Obtención de Datos: Recuperar todos los pedidos con información del cliente.
        // `with('cliente')` realiza Eager Loading de la relación 'cliente' definida en el modelo Pedidos.
        // `latest('fecha_pedido')` ordena los pedidos por la columna 'fecha_pedido' de más reciente a más antiguo.
        // `paginate(20)` divide los resultados en páginas de 20 pedidos.
        $pedidos = Pedidos::with('cliente')->latest('fecha_pedido')->paginate(20);

        // 3. Retornar la Vista: Mostrar la lista de pedidos.
        // Renderiza la vista 'resources/views/pedidos/index.blade.php'.
        // Pasa la colección paginada de pedidos a la vista.
        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Muestra el formulario para crear un nuevo pedido manualmente (por un administrador).
     *
     * Restringido a administradores. Actualmente solo muestra la vista del formulario.
     * Podría extenderse para pasar una lista de clientes si el admin necesita seleccionarlo.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'pedidos.create' o redirige si no es admin.
     */
    public function create(): View|RedirectResponse
    {
        // 1. Autorización: Solo administradores pueden acceder a este formulario.
        if (Auth::user()->rol !== 'administrador') {
            // Redirige al índice de pedidos (admin) si no tiene permiso.
            return redirect()->route('pedidos.index')->with('error', 'Acceso no autorizado.');
        }

        // 2. Preparación de Datos (Opcional):
        // Si el formulario necesitara una lista de clientes para seleccionar:
        // $clientes = \App\Models\User::where('rol', 'cliente')->orderBy('name')->get();
        // return view('pedidos.create', compact('clientes'));

        // 3. Retornar la Vista del Formulario:
        // Renderiza 'resources/views/pedidos.create.blade.php'.
        return view('pedidos.create');
    }

    /**
     * Almacena un nuevo pedido creado manualmente en la base de datos (por un administrador).
     *
     * Restringido a administradores. Valida los datos recibidos del formulario
     * (cliente existente, estado válido, total y fecha opcionales).
     * Crea el registro del pedido y redirige al índice de pedidos.
     *
     * @param  \Illuminate\Http\Request  $request Datos del formulario de creación manual.
     * @return \Illuminate\Http\RedirectResponse Redirige al índice de pedidos o de vuelta si hay error.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Autorización: Solo administradores pueden crear pedidos manualmente.
        if (Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        // 2. Validación de Datos:
        $request->validate([
            'cliente_id' => 'required|exists:users,id', // El cliente seleccionado debe existir en la tabla 'users'.
            // El estado debe ser uno de los valores válidos definidos en el helper getStatusMap().
            'status' => ['required', Rule::in(array_keys(self::getStatusMap()))],
            'total' => 'nullable|numeric|min:0', // El total es opcional y debe ser numérico no negativo.
            'fecha_pedido' => 'nullable|date', // La fecha es opcional y debe tener formato de fecha válido.
        ]);

        // 3. Creación del Pedido:
        try {
            // Utiliza el método estático `create` del modelo Pedidos con los datos validados.
            Pedidos::create($request->all());
            // 4. Redirección Éxito:
            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido creado correctamente.');
        } catch (\Exception $e) {
            // 5. Manejo de Errores:
            Log::error("Error al crear pedido manual: " . $e->getMessage(), $request->all());
            return back()->with('error', 'Ocurrió un error al crear el pedido.')->withInput();
        }
    }


    /**
     * Muestra los detalles de un pedido específico.
     *
     * Accesible por el cliente propietario del pedido o por cualquier administrador.
     * Utiliza Route Model Binding para obtener la instancia del pedido.
     * Carga las relaciones 'cliente' y 'detallespedido' (con sus libros asociados)
     * para mostrar toda la información relevante.
     *
     * @param  \App\Models\Pedidos $pedido Instancia del modelo Pedidos inyectada por Laravel.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'pedidos.show' o redirige si no está autorizado.
     */
    public function show(Pedidos $pedido): View|RedirectResponse
    {
        // 1. Autorización: Verificar si el usuario autenticado es el dueño o un administrador.
        $user = Auth::user();

        // Comprueba si el ID del usuario logueado NO coincide con el cliente_id del pedido
        // Y TAMPOCO el usuario logueado es administrador.
        // Nota: Es crucial que $pedido->cliente_id no sea null para que la comparación funcione para el cliente.
        if ($user->id !== $pedido->cliente_id && $user->rol !== 'administrador') {
            // Si no cumple ninguna condición, redirige al perfil del usuario con un error.
            return redirect()->route('profile.show')->with('error', 'No tienes permiso para ver este pedido.');
        }

        // 2. Carga de Relaciones (Eager Loading):
        // `load()` carga las relaciones en el modelo $pedido ya existente.
        // Carga la relación 'cliente' (para mostrar datos del cliente).
        // Carga la relación 'detallespedido' y, anidadamente, la relación 'libro' de cada detalle.
        $pedido->load(['cliente', 'detallespedido.libro']);

        // 3. Retornar la Vista de Detalles:
        // Renderiza 'resources/views/pedidos/show.blade.php'.
        // Pasa la instancia del pedido `$pedido` (con relaciones cargadas) a la vista.
        return view('pedidos.show', compact('pedido'));
    }


    /**
     * Muestra el formulario para editar un pedido existente (por un administrador).
     *
     * Restringido a administradores. Utiliza Route Model Binding.
     * Obtiene el mapa de estados válidos para pasarlo a la vista (para un select).
     *
     * @param  \App\Models\Pedidos $pedidos Instancia del pedido a editar (Route Model Binding).
     *                                     Se mantiene el nombre plural `$pedidos`.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'pedidos.edit' o redirige si no es admin.
     */
    public function edit(Pedidos $pedidos): View|RedirectResponse
    {
        // 1. Autorización: Solo administradores pueden editar.
        if (Auth::user()->rol !== 'administrador') {
             return redirect()->route('pedidos.index')->with('error', 'Acceso no autorizado.');
        }

        // 2. Obtener Datos para Select: Recuperar los posibles estados.
        // Llama al método helper privado para obtener el array [código_estado => nombre_estado].
        $statuses = self::getStatusMap();

        // 3. Retornar la Vista del Formulario de Edición:
        // Renderiza 'resources/views/pedidos.edit.blade.php'.
        // Pasa el pedido a editar (`$pedidos`) y el array de estados (`$statuses`).
        return view('pedidos.edit', compact('pedidos', 'statuses'));
    }

    /**
     * Actualiza un pedido existente en la base de datos (por un administrador).
     *
     * Restringido a administradores. Valida los datos recibidos (estado válido,
     * total y fecha opcionales). Actualiza solo los campos permitidos del pedido.
     * Redirige al índice de pedidos.
     *
     * @param  \Illuminate\Http\Request  $request Datos del formulario de edición.
     * @param  \App\Models\Pedidos $pedidos Instancia del pedido a actualizar (Route Model Binding).
     *                                     Se mantiene el nombre plural `$pedidos`.
     * @return \Illuminate\Http\RedirectResponse Redirige al índice de pedidos o de vuelta si hay error.
     */
    public function update(Request $request, Pedidos $pedidos): RedirectResponse
    {
        // 1. Autorización: Solo administradores pueden actualizar.
        if (Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        // 2. Validación de Datos:
        $request->validate([
            // El estado debe ser uno de los valores válidos definidos en getStatusMap().
            'status' => ['required', Rule::in(array_keys(self::getStatusMap()))],
            'total' => 'nullable|numeric|min:0', // Permite actualizar el total si es necesario.
            'fecha_pedido' => 'nullable|date', // Permite actualizar la fecha si es necesario.
            // Nota: No se valida ni permite actualizar 'cliente_id' aquí por seguridad/lógica.
        ]);

        // 3. Actualización del Pedido:
        try {
            // Se utiliza el método `update()` sobre la instancia `$pedidos`.
            // `request->only()` asegura que solo se intenten actualizar los campos especificados,
            // incluso si se envían otros campos en la solicitud.
            $pedidos->update($request->only(['status', 'total', 'fecha_pedido']));

            // 4. Redirección Éxito:
            // Redirige al índice de pedidos del admin con mensaje de éxito.
            // Alternativa: redirigir a la vista show del pedido actualizado: route('pedidos.show', $pedidos).
            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido actualizado correctamente.');
        } catch (\Exception $e) {
            // 5. Manejo de Errores:
             Log::error("Error al actualizar pedido ID {$pedidos->id}: " . $e->getMessage(), $request->all());
             return back()->with('error', 'Ocurrió un error al actualizar el pedido.')->withInput();
        }
    }


    /**
     * Procesa el checkout del pedido pendiente del usuario actual.
     *
     * Busca el pedido pendiente del usuario, verifica que no esté vacío,
     * calcula el total final basado en los detalles, actualiza el estado del pedido
     * (a 'completado' o 'procesando'), asigna el total y la fecha.
     * Utiliza una transacción de base de datos para asegurar la atomicidad de la operación.
     * Si todo es correcto, confirma la transacción y redirige a una página de éxito.
     * Maneja errores como pedido no encontrado, carrito vacío u otros problemas.
     *
     * @param  \Illuminate\Http\Request  $request (No se usa directamente, pero es estándar).
     * @return \Illuminate\Http\RedirectResponse Redirige a la página de éxito o de vuelta con error.
     */
    public function processCheckout(Request $request): RedirectResponse
    {
        // 1. Obtener Usuario Autenticado:
        $user = Auth::user();
        // Asegurarse de que el usuario está autenticado (aunque las rutas suelen proteger esto).
        if (!$user) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para finalizar la compra.');
        }

        // 2. Iniciar Transacción de Base de Datos:
        // `DB::beginTransaction()` asegura que todas las operaciones de BD dentro del try
        // se completen exitosamente o se reviertan todas juntas si ocurre un error.
        DB::beginTransaction();

        try {
            // 3. Encontrar el Pedido Pendiente del Usuario:
            // Busca el pedido pendiente asociado al usuario.
            $pedidoPendiente = Pedidos::where('cliente_id', $user->id)
                ->where('status', Pedidos::STATUS_PENDIENTE)
                ->with('detallespedido') // Carga los detalles para verificar si está vacío y calcular total.
                // ->lockForUpdate() // Opcional: Bloquea la fila del pedido para evitar modificaciones concurrentes durante la transacción. Útil en sistemas de alta concurrencia.
                ->firstOrFail(); // Lanza ModelNotFoundException si no se encuentra ningún pedido pendiente.

            // 4. Verificar si el Carrito (Pedido Pendiente) está Vacío:
            // Se accede a la relación 'detallespedido' cargada previamente.
            if ($pedidoPendiente->detallespedido->isEmpty()) {
                 DB::rollBack(); // Revertir la transacción si el carrito está vacío.
                // Redirige al índice del carrito con un error.
                return redirect()->route('detallespedido.index')->with('error', 'Tu carrito está vacío.');
            }

            // 5. Calcular el Total Final:
            // Suma los subtotales (cantidad * precio) de cada detalle del pedido.
            $totalFinal = $pedidoPendiente->detallespedido->sum(function ($detalle) {
                return $detalle->cantidad * $detalle->precio;
            });

            // 6. Actualizar el Pedido:
            // Cambia el estado del pedido a 'completado' (o 'procesando' si hay pasos intermedios).
            $pedidoPendiente->status = Pedidos::STATUS_COMPLETADO;
            // Asigna el total calculado.
            $pedidoPendiente->total = $totalFinal;
            // Establece la fecha/hora actual como fecha del pedido completado.
            $pedidoPendiente->fecha_pedido = now();
            // Guarda los cambios en la base de datos.
            $pedidoPendiente->save();

            // --- Punto de Integración con Pasarela de Pago ---
            // Aquí iría la lógica para procesar el pago.
            // Si el pago falla, se debería lanzar una excepción para activar el DB::rollBack().
            // Ejemplo:
            // $paymentSuccess = PaymentGateway::process($pedidoPendiente, $request->payment_details);
            // if (!$paymentSuccess) {
            //     throw new \Exception('El pago falló.');
            // }
            // --- Fin Lógica de Pago ---

            // 7. Confirmar Transacción:
            // Si todas las operaciones (actualización de pedido, pago) fueron exitosas,
            // `DB::commit()` hace permanentes los cambios en la base de datos.
            DB::commit();

            // 8. Redirección Éxito:
            // Redirige a la ruta de confirmación de éxito, pasando el ID del pedido procesado.
            // Se usa el nombre de ruta 'checkout.success' definido en web.php.
            return redirect()->route('checkout.success', ['pedido' => $pedidoPendiente->id])
                ->with('success', '¡Tu pedido ha sido realizado con éxito!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // 9. Manejo de Error: Pedido Pendiente No Encontrado.
            DB::rollBack(); // Revertir la transacción.
            // Redirige al carrito con un mensaje específico.
            return redirect()->route('detallespedido.index')->with('error', 'No se encontró un pedido pendiente.');
        } catch (\Exception $e) {
            // 10. Manejo de Error Genérico (Incluye posible fallo de pago):
            DB::rollBack(); // Revertir la transacción en cualquier otro error.
            // Registra el error detallado.
            Log::error("Error en checkout para user {$user->id}: " . $e->getMessage());
            // Redirige al carrito con un mensaje genérico.
            return redirect()->route('detallespedido.index')->with('error', 'Ocurrió un error al procesar tu pedido. Inténtalo de nuevo.');
        }
    }

    /**
     * Muestra la página de confirmación después de un checkout exitoso.
     *
     * Utiliza Route Model Binding para obtener la instancia del pedido.
     * Verifica que el usuario autenticado sea el propietario del pedido y que
     * el pedido no esté en estado 'pendiente'. Carga los detalles y libros
     * asociados para mostrar un resumen del pedido realizado.
     *
     * @param  \App\Models\Pedidos $pedido Instancia del pedido cuya confirmación se mostrará.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'pedidos.success' o redirige si no está autorizado/listo.
     */
    public function showSuccess(Pedidos $pedido): View|RedirectResponse
    {
        // 1. Autorización: Asegurarse que el usuario autenticado es el dueño del pedido.
        if ($pedido->cliente_id !== Auth::id()) {
            // Detiene la ejecución si no es el dueño.
            abort(403, 'No tienes permiso para ver esta confirmación.');
        }

        // 2. Verificación de Estado: Asegurarse que el pedido ya no está pendiente.
        // Previene que se acceda a la página de éxito de un pedido que no se completó.
        if ($pedido->status === Pedidos::STATUS_PENDIENTE) {
             // Redirige al perfil del usuario con un error si el pedido aún está pendiente.
             return redirect()->route('profile.show')->with('error', 'Este pedido aún no ha sido completado.');
        }

        // 3. Carga de Relaciones: Cargar detalles y libros para mostrar el resumen.
        // `load()` carga las relaciones en el modelo $pedido ya existente.
        $pedido->load('detallespedido.libro');

        // 4. Retornar la Vista de Éxito:
        // Renderiza 'resources/views/pedidos.success.blade.php'.
        // Pasa la instancia del pedido `$pedido` (con relaciones cargadas) a la vista.
        return view('pedidos.success', compact('pedido'));
    }

    /**
     * Método helper privado para obtener un mapa de los estados de pedido válidos.
     *
     * Devuelve un array asociativo donde las claves son las constantes de estado
     * (ej. 'pendiente') y los valores son sus representaciones legibles (ej. 'Pendiente').
     * Útil para validación y para mostrar opciones en formularios (como en `edit`).
     * Es `private static` porque no depende del estado de una instancia y solo se usa internamente.
     *
     * @return array<string, string> Mapa de código de estado a nombre legible.
     */
    private static function getStatusMap(): array
    {
        // Define los estados usando las constantes del modelo Pedidos para mantenibilidad.
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
     * Elimina un pedido específico de la base de datos (acción de administrador).
     *
     * Restringido a administradores. Utiliza Route Model Binding.
     * Intenta eliminar el pedido. Maneja posibles errores, incluyendo restricciones
     * de base de datos (si los detalles no se borran en cascada).
     *
     * @param  \App\Models\Pedidos $pedidos Instancia del pedido a eliminar (Route Model Binding).
     *                                     Se mantiene el nombre plural `$pedidos`.
     * @return \Illuminate\Http\RedirectResponse Redirige al índice de pedidos con mensaje de éxito o error.
     */
    public function destroy(Pedidos $pedidos): RedirectResponse
    {
        // 1. Autorización: Solo administradores pueden eliminar pedidos.
        if (Auth::user()->rol !== 'administrador') {
             abort(403, 'Acción no autorizada.');
        }

        // 2. Intento de Eliminación:
        try {
            
            // Llama al método `delete()` sobre la instancia `$pedidos`.
            $pedidos->delete();

            // 3. Redirección Éxito:
            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido eliminado correctamente.');

        } catch (\Illuminate\Database\QueryException $e) {
             // 4. Manejo de Error Específico (Restricción de BD):
             // Captura errores de BD, típicamente por restricciones de clave foránea.
             Log::error("Error de BD al eliminar pedido ID {$pedidos->id}: " . $e->getMessage());
             return redirect()->route('pedidos.index')
                ->with('error', 'No se pudo eliminar el pedido debido a restricciones (posiblemente detalles asociados).');
        } catch (\Exception $e) {
             // 5. Manejo de Error Genérico:
             Log::error("Error al eliminar pedido ID {$pedidos->id}: " . $e->getMessage());
             return redirect()->route('pedidos.index')
                ->with('error', 'Ocurrió un error al eliminar el pedido.');
        }
    }
}
