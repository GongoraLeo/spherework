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
     * Esta acción está diseñada principalmente para usuarios administradores.
     * Primero, verifica si el usuario autenticado es administrador (`Auth::check()` y `Auth::user()->rol`).
     * Si no lo es, redirige a la ruta 'profile.entry' con un mensaje de error.
     * Si está autorizado, recupera todos los pedidos utilizando el modelo `Pedidos`.
     * Realiza Eager Loading de la relación 'cliente' (`with('cliente')`) para mostrar el nombre del cliente
     * de forma eficiente. Ordena los pedidos por fecha descendente (`latest('fecha_pedido')`)
     * y los pagina (`paginate(20)`), mostrando 20 pedidos por página.
     * Finalmente, renderiza la vista 'pedidos.index' pasándole la colección paginada de pedidos.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'pedidos.index' o redirige si no es admin.
     */
    public function index(): View|RedirectResponse // Modificado para incluir RedirectResponse por la autorización
    {
        // 1. Autorización: Verifica si el usuario autenticado es un administrador.
        // Se comprueba el rol del usuario logueado.
        if (!Auth::check() || Auth::user()->rol !== 'administrador') {
            // Si no es admin, redirige a la entrada del perfil con un mensaje de error.
            // Se eligió redirigir en lugar de abort(403) para una experiencia de usuario más guiada.
             return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado para ver todos los pedidos.');
        }

        // 2. Obtención de Datos: Recupera todos los pedidos con información del cliente.
        // `with('cliente')` realiza Eager Loading de la relación 'cliente' definida en el modelo Pedidos.
        // `latest('fecha_pedido')` ordena los pedidos por la columna 'fecha_pedido' de más reciente a más antiguo.
        // `paginate(20)` divide los resultados en páginas de 20 pedidos.
        $pedidos = Pedidos::with('cliente')->latest('fecha_pedido')->paginate(20);

        // 3. Retornar la Vista: Muestra la lista de pedidos.
        // Renderiza la vista 'resources/views/pedidos/index.blade.php'.
        // Pasa la colección paginada de pedidos a la vista.
        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Muestra el formulario para crear un nuevo pedido manualmente (por un administrador).
     *
     * Restringido a administradores. Verifica si el usuario autenticado es administrador.
     * Si no lo es, redirige al índice de pedidos del admin con un mensaje de error.
     * Si está autorizado, simplemente retorna la vista 'pedidos.create'.
     * El código comentado muestra cómo se podría pasar una lista de clientes si fuera necesario.
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
     * Restringido a administradores. Verifica la autorización; si falla, usa `abort(403)`.
     * Valida los datos recibidos del formulario (`cliente_id`, `status`, `total`, `fecha_pedido`)
     * usando `$request->validate()`. La regla `Rule::in` para el estado utiliza el helper
     * `self::getStatusMap()` para asegurar que sea un estado válido.
     * Dentro de un bloque try-catch, crea el registro del pedido usando `Pedidos::create($request->all())`.
     * Si tiene éxito, redirige al índice de pedidos con un mensaje de éxito.
     * Si falla, registra el error y redirige de vuelta al formulario con un mensaje de error y los datos introducidos.
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
     * Utiliza Route Model Binding para obtener la instancia del pedido (`$pedidos`).
     * Verifica la autorización: el usuario autenticado (`$user`) debe ser el propietario
     * (`$user->id == $pedidos->cliente_id`) O un administrador (`$user->rol === 'administrador'`).
     * Si no está autorizado, redirige a 'profile.show' con un error.
     * Si está autorizado, realiza Eager Loading de las relaciones 'cliente' y 'detallespedidos.libro'
     * usando `$pedidos->load()` para mostrar toda la información relevante en la vista.
     * Finalmente, renderiza la vista 'pedidos.show', pasándole la instancia del pedido cargada.
     *
     * @param  \App\Models\Pedidos $pedidos Instancia del modelo Pedidos inyectada por Laravel.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'pedidos.show' o redirige si no está autorizado.
     */
    public function show(Pedidos $pedidos): View|RedirectResponse
    {
        // 1. Autorización: Verifica si el usuario autenticado es el dueño o un administrador.
        $user = Auth::user();

        // Comprueba si el ID del usuario logueado NO coincide con el cliente_id del pedido
        // Y TAMPOCO el usuario logueado es administrador.
        // Nota: Es crucial que $pedidos->cliente_id no sea null para que la comparación funcione para el cliente.
        if ($user->id != $pedidos->cliente_id && $user->rol !== 'administrador') {
            // Si no cumple ninguna condición, redirige al perfil del usuario con un error.
            return redirect()->route('profile.show')->with('error', 'No tienes permiso para ver este pedido.');
        }

        // 2. Carga de Relaciones (Eager Loading):
        // `load()` carga las relaciones en el modelo $pedidos ya existente.
        // Carga la relación 'cliente' (para mostrar datos del cliente).
        // Carga la relación 'detallespedidos' y, anidadamente, la relación 'libro' de cada detalle.
        $pedidos->load(['cliente', 'detallespedidos.libro']);

        // 3. Retornar la Vista de Detalles:
        // Renderiza 'resources/views/pedidos/show.blade.php'.
        // Pasa la instancia del pedido `$pedidos` (con relaciones cargadas) a la vista.
        return view('pedidos.show', compact('pedidos'));
    }


    /**
     * Muestra el formulario para editar un pedido existente (por un administrador).
     *
     * Restringido a administradores. Verifica la autorización del usuario. Si no es admin,
     * redirige al índice de pedidos. Utiliza Route Model Binding para obtener la instancia
     * del pedido (`$pedidos`) a editar. Obtiene el mapa de estados válidos usando el helper
     * `self::getStatusMap()` para pasarlo a la vista y poblar un campo 'select'.
     * Renderiza la vista 'pedidos.edit', pasándole el pedido y el mapa de estados.
     *
     * @param  \App\Models\Pedidos $pedidos Instancia del pedido a editar (Route Model Binding).
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'pedidos.edit' o redirige si no es admin.
     */
    public function edit(Pedidos $pedidos): View|RedirectResponse
    {
        // 1. Autorización: Solo administradores pueden editar.
        if (Auth::user()->rol !== 'administrador') {
             return redirect()->route('pedidos.index')->with('error', 'Acceso no autorizado.');
        }

        // 2. Obtener Datos para Select: Recupera los posibles estados.
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
     * Restringido a administradores. Verifica la autorización; si falla, usa `abort(403)`.
     * Valida los datos recibidos del formulario (`status`, `total`, `fecha_pedido`) usando
     * `$request->validate()`. La regla `Rule::in` para el estado usa `self::getStatusMap()`.
     * Dentro de un bloque try-catch, actualiza el pedido usando `$pedidos->update()` con
     * `$request->only([...])` para asegurar que solo se modifiquen los campos permitidos.
     * Si tiene éxito, redirige al índice de pedidos con un mensaje de éxito.
     * Si falla, registra el error y redirige de vuelta al formulario con error y datos introducidos.
     *
     * @param  \Illuminate\Http\Request  $request Datos del formulario de edición.
     * @param  \App\Models\Pedidos $pedidos Instancia del pedido a actualizar (Route Model Binding).
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
        ]);

        // 3. Actualización del Pedido:
        try {
            // Se utiliza el método `update()` sobre la instancia `$pedidos`.
            // `request->only()` asegura que solo se intenten actualizar los campos especificados ('status', 'total', 'fecha_pedido'),
            // incluso si se envían otros campos en la solicitud.
            $pedidos->update($request->only(['status', 'total', 'fecha_pedido']));

            // 4. Redirección Éxito:
            // Redirige al índice de pedidos del admin con mensaje de éxito.
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
     * Obtiene el usuario autenticado; si no existe, redirige al login.
     * Inicia una transacción de base de datos (`DB::beginTransaction()`) para asegurar la atomicidad.
     * Dentro de un bloque try-catch:
     * 1. Busca el pedido pendiente (`status = Pedidos::STATUS_PENDIENTE`) del usuario, cargando
     *    sus detalles (`with('detallespedidos')`). Usa `firstOrFail()` para lanzar una excepción
     *    si no se encuentra. Se menciona la posibilidad de usar `lockForUpdate()` para alta concurrencia.
     * 2. Verifica si la colección de detalles del pedido está vacía (`isEmpty()`). Si lo está,
     *    registra la información, revierte la transacción (`DB::rollBack()`) y redirige al carrito
     *    con un mensaje de error.
     * 3. Calcula el `$totalFinal` sumando `cantidad * precio` para cada detalle del pedido.
     * 4. Actualiza el pedido pendiente: establece el `status` a `Pedidos::STATUS_COMPLETADO`,
     *    asigna el `$totalFinal` calculado, establece la `fecha_pedido` a la hora actual (`now()`)
     *    y guarda los cambios (`save()`).
     * 5. Confirma la transacción (`DB::commit()`) si todo ha ido bien.
     * 6. Redirige a la ruta de éxito (`pedidos.checkout.success`) pasando el ID del pedido
     *    y un mensaje de éxito.
     * Captura `ModelNotFoundException` (pedido no encontrado), revierte la transacción y redirige
     * al carrito con un error específico.
     * Captura cualquier otra `Exception` (incluyendo fallos de pago), revierte la transacción,
     * registra el error y redirige al carrito con un error genérico.
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
                ->with('detallespedidos') // Carga los detalles para verificar si está vacío y calcular total.
                ->firstOrFail(); // Lanza ModelNotFoundException si no se encuentra ningún pedido pendiente.

            // 4. Verificar si el Carrito (Pedido Pendiente) está Vacío:
            // Se accede a la relación 'detallespedidos' cargada previamente.
            if ($pedidoPendiente->detallespedidos->isEmpty()) {
                Log::info('Carrito vacío detectado al intentar procesar checkout para user '.$user->id);
                 DB::rollBack(); // Revertir la transacción si el carrito está vacío.
                // Redirige al índice del carrito con un error.
                return redirect()->route('detallespedidos.index')->with('error', 'Tu carrito está vacío.');
            }

            // 5. Calcular el Total Final:
            // Suma los subtotales (cantidad * precio) de cada detalle del pedido.
            $totalFinal = $pedidoPendiente->detallespedidos->sum(function ($detalle) {
                // Se asume que cantidad y precio son numéricos válidos en este punto.
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

            // 7. Confirmar Transacción:
            // Si todas las operaciones (actualización de pedido, pago) fueron exitosas,
            // `DB::commit()` hace permanentes los cambios en la base de datos.
            DB::commit();

            // 8. Redirección Éxito:
            // Redirige a la ruta de confirmación de éxito, pasando el ID del pedido procesado.
            // Se usa el nombre de ruta 'pedidos.checkout.success' definido en web.php.
            return redirect()->route('pedidos.checkout.success', ['pedidos' => $pedidoPendiente->id])
                ->with('success', '¡Tu pedido ha sido realizado con éxito!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // 9. Manejo de Error: Pedido Pendiente No Encontrado.
            DB::rollBack(); // Revertir la transacción.
            Log::warning("Intento de checkout sin pedido pendiente para user {$user->id}.");
            // Redirige al carrito con un mensaje específico.
            return redirect()->route('detallespedidos.index')->with('error', 'No se encontró un pedido pendiente.');
        } catch (\Exception $e) {
            // 10. Manejo de Error Genérico (Incluye posible fallo de pago):
            DB::rollBack(); // Revertir la transacción en cualquier otro error.
            // Registra el error detallado.
            Log::error("Error en checkout para user {$user->id}: " . $e->getMessage());
            // Redirige al carrito con un mensaje genérico.
            return redirect()->route('detallespedidos.index')->with('error', 'Ocurrió un error al procesar tu pedido. Inténtalo de nuevo.');
        }
    }

    /**
     * Muestra la página de confirmación después de un checkout exitoso.
     *
     * Utiliza Route Model Binding para obtener la instancia del pedido (`$pedidos`).
     * Verifica la autorización: el usuario autenticado debe ser el propietario del pedido
     * (`$pedidos->cliente_id === Auth::id()`). Si no, aborta con 403.
     * Verifica el estado del pedido: si aún está 'pendiente' (`Pedidos::STATUS_PENDIENTE`),
     * redirige al perfil del usuario con un error, ya que la página de éxito no es aplicable.
     * Si las verificaciones pasan, carga las relaciones 'detallespedidos.libro' usando `load()`
     * para mostrar un resumen del pedido.
     * Renderiza la vista 'pedidos.success', pasándole la instancia del pedido cargada.
     *
     * @param  \App\Models\Pedidos $pedidos Instancia del pedido cuya confirmación se mostrará.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista 'pedidos.success' o redirige si no está autorizado/listo.
     */
    public function showSuccess(Pedidos $pedidos): View|RedirectResponse
    {
        // 1. Autorización: Asegurarse que el usuario autenticado es el dueño del pedido.
        if ($pedidos->cliente_id !== Auth::id()) {
            // Detiene la ejecución si no es el dueño.
            abort(403, 'No tienes permiso para ver esta confirmación.');
        }

        // 2. Verificación de Estado: Asegurarse que el pedido ya no está pendiente.
        // Previene que se acceda a la página de éxito de un pedido que no se completó.
        if ($pedidos->status === Pedidos::STATUS_PENDIENTE) {
             // Redirige al perfil del usuario con un error si el pedido aún está pendiente.
             return redirect()->route('profile.show')->with('error', 'Este pedido aún no ha sido completado.');
        }

        // 3. Carga de Relaciones: Carga detalles y libros para mostrar el resumen.
        // `load()` carga las relaciones en el modelo $pedidos ya existente.
        $pedidos->load('detallespedidos.libro');

        // 4. Retornar la Vista de Éxito:
        // Renderiza 'resources/views/pedidos.success.blade.php'.
        // Pasa la instancia del pedido `$pedidos` (con relaciones cargadas) a la vista.
        return view('pedidos.success', compact('pedidos'));
    }

    /**
     * Método helper privado para obtener un mapa de los estados de pedido válidos.
     *
     * Devuelve un array asociativo donde las claves son las constantes de estado
     * (ej. 'pendiente') y los valores son sus representaciones legibles (ej. 'Pendiente').
     * Se utiliza para la validación de estados en `store()` y `update()`, y para
     * poblar opciones en formularios (como en `edit`). Es `private static` porque
     * no necesita una instancia del controlador y su lógica es interna a esta clase.
     * Se usan las constantes del modelo `Pedidos` para asegurar consistencia y mantenibilidad.
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
     * Restringido a administradores. Verifica la autorización; si falla, usa `abort(403)`.
     * Utiliza Route Model Binding para obtener la instancia del pedido (`$pedidos`).
     * Intenta eliminar el pedido usando `$pedidos->delete()` dentro de un bloque try-catch.
     * Si tiene éxito, redirige al índice de pedidos con un mensaje de éxito.
     * Si se produce una `QueryException` (posiblemente por restricciones de clave foránea si
     * los detalles del pedido no se eliminan en cascada), la captura, registra el error
     * y redirige con un mensaje de error específico.
     * Captura cualquier otra `Exception` genérica, la registra y redirige con un error genérico.
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
            // Se asume que la configuración de la base de datos (claves foráneas)
            // maneja la eliminación en cascada de los detalles del pedido si es necesario.
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
