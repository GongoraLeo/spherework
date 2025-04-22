<?php
// filepath: app/Http/Controllers/DetallespedidosController.php

namespace App\Http\Controllers;

use App\Models\Detallespedidos; // Modelo Eloquent para los ítems del carrito/pedido.
use App\Models\Pedidos;         // Modelo Pedidos, necesario para encontrar/crear el pedido pendiente.
use App\Models\Libros;         // Modelo Libros, potencialmente útil (aunque el precio viene del request).
use Illuminate\Http\Request;     // Objeto para manejar la solicitud HTTP (datos de formularios).
use Illuminate\Support\Facades\Auth; // Fachada para obtener el usuario autenticado y verificar sesión.
use Illuminate\View\View;             // Para type hinting del retorno de vistas.
use Illuminate\Http\RedirectResponse; // Para type hinting del retorno de redirecciones.
use Illuminate\Support\Facades\Log;    // Fachada para registrar mensajes de error o advertencias.

/**
 * Class DetallespedidosController
 *
 * Gestiona las operaciones del carrito de compras del usuario.
 * Representa los ítems individuales (Detallespedidos) asociados a un Pedido
 * que se encuentra en estado 'pendiente'. Permite ver el carrito, añadir,
 * actualizar cantidad y eliminar ítems.
 *
 * @package App\Http\Controllers
 */
// Este controlador ahora extiende App\Http\Controllers\Controller, que a su vez
// extiende Illuminate\Routing\Controller e incluye traits útiles como AuthorizesRequests y ValidatesRequests.
class DetallespedidosController extends Controller
{
    /**
     * Muestra el contenido del carrito de compras actual del usuario autenticado.
     *
     * Primero, verifica si el usuario está autenticado usando `Auth::check()`. Si no lo está,
     * redirige a la ruta de login. Si está autenticado, obtiene el usuario y busca su pedido
     * con estado 'pendiente' (`Pedidos::STATUS_PENDIENTE`) usando `Pedidos::where(...)->first()`.
     * Si encuentra un pedido pendiente, recupera los detalles (ítems) asociados a ese pedido
     * (`Detallespedidos::where(...)`) utilizando Eager Loading (`with(['libro.autor', 'libro.editorial'])`)
     * para cargar eficientemente la información del libro, autor y editorial relacionados.
     * Luego, calcula el total del carrito sumando el producto de precio y cantidad para cada detalle,
     * realizando una verificación para asegurar que precio y cantidad sean numéricos.
     * Finalmente, renderiza la vista 'detallespedidos.index', pasándole la colección de detalles
     * (o una colección vacía si no hay pedido pendiente) y el total calculado.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista del carrito o redirige al login si no está autenticado.
     */
    public function index(): View|RedirectResponse
    {
        // 1. Autorización: Verifica si el usuario está autenticado.
        // Es crucial para asegurar que solo usuarios logueados accedan a su carrito.
        if (!Auth::check()) {
            // Si no está logueado, redirige a la página de login con un mensaje de error.
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver tu carrito.');
        }

        // 2. Obtención del Usuario y Datos Iniciales:
        $user = Auth::user(); // Obtiene el modelo del usuario autenticado.
        $total = 0; // Inicializa el total del carrito.
        $detallespedidos = collect(); // Inicializa como colección vacía por si no hay pedido pendiente.

        // 3. Búsqueda del Pedido Pendiente:
        // Busca un único pedido que pertenezca al usuario (`cliente_id`) y tenga estado 'pendiente'.
        $pedidoPendiente = Pedidos::where('cliente_id', $user->id)
                                 ->where('status', Pedidos::STATUS_PENDIENTE)
                                 ->first(); // Obtiene el primer resultado o null.

        // 4. Obtención de Detalles si existe Pedido Pendiente:
        if ($pedidoPendiente) {
            // Si se encontró un pedido pendiente, obtener sus detalles (ítems).
            // Se usa `with()` para Eager Loading: carga los libros y sus relaciones (autor, editorial)
            // en una consulta eficiente para evitar problemas N+1 en la vista.
            $detallespedidos = Detallespedidos::where('pedido_id', $pedidoPendiente->id)
                                            ->with(['libro.autor', 'libro.editorial']) // Carga el libro y sus relaciones anidadas.
                                            ->get(); // Obtiene la colección de detalles.

            // 5. Cálculo del Total del Carrito:
            // Se utiliza el método `sum()` de la colección de detalles.
            // La función anónima calcula el subtotal para cada ítem (detalle).
            $total = $detallespedidos->sum(function ($detalle) {
                // Verificación defensiva: asegura que precio y cantidad sean numéricos antes de multiplicar.
                $price = is_numeric($detalle->precio) ? $detalle->precio : 0;
                $quantity = is_numeric($detalle->cantidad) ? $detalle->cantidad : 0;
                return $price * $quantity; // Calcula subtotal: precio * cantidad.
            });
        }

        // 6. Retornar la Vista:
        // Renderiza 'resources/views/detallespedidos/index.blade.php'.
        // Pasa la colección de detalles (`$detallespedidos`) y el total calculado (`$total`) a la vista.
        return view('detallespedidos.index', compact('detallespedidos', 'total'));
    }

    /**
     * Muestra el formulario para crear un nuevo recurso.
     *
     * Este método no es aplicable para la lógica del carrito de compras, ya que los ítems
     * (Detallespedidos) se añaden implícitamente a través del método `store` al interactuar
     * con el catálogo de libros. Por lo tanto, redirige al índice de libros (`libros.index`)
     * con un mensaje informativo para guiar al usuario sobre cómo añadir ítems.
     *
     * @return \Illuminate\Http\RedirectResponse Siempre redirige a la lista de libros.
     */
    public function create(): RedirectResponse
    {
        // Redirige al usuario a la página principal de libros (`libros.index`).
        // Se añade un mensaje 'info' para guiar al usuario.
        return redirect()->route('libros.index')->with('info', 'Para añadir libros, usa el botón "Añadir al Carrito".');
    }

    /**
     * Almacena un nuevo ítem (Detallespedidos) en el carrito del usuario.
     *
     * Este método se invoca al enviar el formulario "Añadir al Carrito" desde la vista de un libro.
     * Primero, valida los datos recibidos (`libro_id`, `cantidad`, `precio`) usando `$request->validate()`.
     * Luego, verifica si el usuario está autenticado (`Auth::check()`); si no, redirige al login.
     * Utiliza `Pedidos::firstOrCreate()` para buscar un pedido pendiente existente para el usuario
     * o crear uno nuevo si no existe, asociándolo al `cliente_id` del usuario y estableciendo
     * el estado a `Pedidos::STATUS_PENDIENTE`. Se incluye un try-catch para manejar errores
     * durante esta operación.
     * A continuación, busca si ya existe un detalle (`Detallespedidos`) para el mismo libro
     * dentro del pedido pendiente encontrado o creado.
     * Si el detalle ya existe (`$detalleExistente`), incrementa su cantidad sumando la nueva cantidad
     * recibida y guarda el cambio. Se decidió mantener el precio original con el que se añadió.
     * Si el detalle no existe, crea un nuevo registro `Detallespedidos` con los datos del libro,
     * cantidad, precio (tomado del request) y el ID del pedido pendiente.
     * Finalmente, redirige al usuario a la vista del carrito (`detallespedidos.index`) con un
     * mensaje de éxito indicando si el libro fue añadido o su cantidad actualizada.
     *
     * @param  \Illuminate\Http\Request  $request Objeto con los datos del formulario (libro_id, cantidad, precio).
     * @return \Illuminate\Http\RedirectResponse Redirige a la vista del carrito o a login/atrás si hay errores.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validación de Datos:
        // Se utiliza el método `validate` heredado del controlador base.
        $request->validate([
            'libro_id' => 'required|exists:libros,id', // El libro debe existir en la tabla 'libros'.
            'cantidad' => 'required|integer|min:1',   // La cantidad debe ser un entero positivo.
            'precio'   => 'required|numeric|min:0',   // El precio debe ser numérico y no negativo.
        ]);

        // 2. Autorización: Verifica si el usuario está autenticado.
        if (!Auth::check()) {
            // Si no está autenticado, registra una advertencia y redirige al login.
            Log::warning("Intento de añadir al carrito sin autenticar.");
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para añadir libros al carrito.');
        }
        // A partir de aquí, es seguro usar Auth::user().
        $user = Auth::user();

        // 3. Buscar o Crear Pedido Pendiente:
        // `firstOrCreate` busca un registro que coincida con el primer array.
        // Si no lo encuentra, crea uno nuevo usando los datos del primer array y opcionalmente los del segundo.
        try {
            $pedidoPendiente = Pedidos::firstOrCreate(
                [
                    'cliente_id' => $user->id, // Condición: El pedido debe pertenecer al usuario actual.
                    'status'     => Pedidos::STATUS_PENDIENTE, // Condición: El pedido debe estar pendiente.
                ],
                [
                    // Valores que se usarán SOLO si se CREA un nuevo pedido.
                    // 'total' => 0, // El total se recalcula al finalizar o al mostrar el carrito.
                ]
            );
        } catch (\Exception $e) {
            // Manejo de error si falla la consulta/creación del pedido.
            Log::error("Error en firstOrCreate Pedido: " . $e->getMessage(), ['user_id' => $user->id]);
            // Redirige de vuelta a la página anterior con un error genérico.
            return redirect()->back()->with('error', 'Ocurrió un error al acceder a tu carrito. Inténtalo de nuevo.');
        }


        // 4. Buscar Detalle Existente o Crear Nuevo Ítem:
        // Comprueba si el libro específico (`$request->libro_id`) ya está
        // en el pedido pendiente actual (`$pedidoPendiente->id`).
        $detalleExistente = Detallespedidos::where('pedido_id', $pedidoPendiente->id)
                                         ->where('libro_id', $request->libro_id)
                                         ->first(); // Obtiene el detalle si existe, o null.

        if ($detalleExistente) {
            // 4.1. Si el libro ya existe en el carrito: Actualizar cantidad.
            // Se suma la nueva cantidad (`$request->cantidad`) a la cantidad existente.
            $detalleExistente->cantidad += $request->cantidad;
            // Nota: Se decide no actualizar el precio aquí, manteniendo el precio original
            // con el que se añadió el ítem por primera vez para consistencia.
            $detalleExistente->save(); // Guarda los cambios en el registro del detalle existente.
            $message = 'Cantidad actualizada en el carrito.'; // Mensaje de éxito para el usuario.
        } else {
            // 4.2. Si el libro no existe en el carrito: Crear un nuevo registro de detalle.
            // Se utiliza el método estático `create` del modelo Detallespedidos.
            Detallespedidos::create([
                'pedido_id' => $pedidoPendiente->id, // ID del pedido pendiente asociado.
                'libro_id'  => $request->libro_id, // ID del libro añadido.
                'cantidad'  => $request->cantidad, // Cantidad inicial.
                'precio'    => $request->precio, // Precio del libro en el momento de añadirlo (viene del formulario).
            ]);
            $message = 'Libro añadido al carrito correctamente.'; // Mensaje de éxito para el usuario.
        }

        // 5. Redirección Éxito:
        // Redirige al usuario a la vista del carrito (`detallespedidos.index`).
        // Se incluye un mensaje flash de éxito en la sesión.
        return redirect()->route('detallespedidos.index')
                       ->with('success', $message);
    }

    /**
     * Muestra un recurso específico (Detallespedidos).
     *
     * Este método no es relevante para la funcionalidad del carrito, donde todos los ítems
     * se visualizan juntos en la vista `index`. Por convención de recursos RESTful,
     * existe, pero simplemente redirige al usuario a la vista principal del carrito.
     *
     * @param  \App\Models\Detallespedidos  $detallespedidos Instancia inyectada por Route Model Binding (no utilizada).
     * @return \Illuminate\Http\RedirectResponse Siempre redirige al índice del carrito.
     */
    public function show(Detallespedidos $detallespedidos): RedirectResponse
    {
         // Redirige siempre a la vista principal del carrito (`detallespedidos.index`).
         return redirect()->route('detallespedidos.index');
    }

    /**
     * Muestra el formulario para editar un recurso específico (Detallespedidos).
     *
     * Este método no se utiliza en la implementación actual del carrito. La edición
     * (específicamente, la actualización de la cantidad) se realiza directamente
     * desde la vista `index` mediante el método `update`. Por lo tanto, esta ruta
     * simplemente redirige al índice del carrito.
     *
     * @param  \App\Models\Detallespedidos  $detallespedidos Instancia inyectada por Route Model Binding (no utilizada).
     * @return \Illuminate\Http\RedirectResponse Siempre redirige al índice del carrito.
     */
    public function edit(Detallespedidos $detallespedidos): RedirectResponse
    {
        // Redirige siempre a la vista principal del carrito (`detallespedidos.index`).
        return redirect()->route('detallespedidos.index');
    }

    /**
     * Actualiza la cantidad de un ítem específico en el carrito.
     *
     * Se invoca típicamente desde un formulario en la vista del carrito (`detallespedidos.index`).
     * Valida que la `cantidad` recibida sea un entero positivo.
     * Verifica que el usuario esté autenticado (`Auth::check()`).
     * Realiza una comprobación de autorización crucial: utiliza `loadMissing('pedido')` para
     * cargar la relación con el pedido asociado al detalle (si no está ya cargada) y luego
     * verifica que este pedido exista, pertenezca al usuario autenticado (`cliente_id`) y
     * tenga el estado 'pendiente' (`Pedidos::STATUS_PENDIENTE`). Si alguna de estas condiciones
     * no se cumple, registra una advertencia y redirige con un error, previniendo modificaciones
     * no autorizadas.
     * Si la autorización es correcta, actualiza el atributo `cantidad` del modelo `$detallespedidos`
     * con el valor validado y guarda los cambios usando `save()`.
     * Finalmente, redirige de vuelta a la vista del carrito (`detallespedidos.index`) con un mensaje de éxito.
     *
     * @param  \Illuminate\Http\Request  $request Objeto con la nueva cantidad.
     * @param  \App\Models\Detallespedidos  $detallespedidos Instancia del ítem a actualizar (Route Model Binding).
     * @return \Illuminate\Http\RedirectResponse Redirige al índice del carrito o a login/atrás si hay error.
     */
    public function update(Request $request, Detallespedidos $detallespedidos): RedirectResponse
    {
        // 1. Validación de Datos:
        $request->validate([
            'cantidad' => 'required|integer|min:1', // La nueva cantidad debe ser un entero positivo.
        ]);

        // 2. Autorización: Verifica autenticación.
        if (!Auth::check()) {
             // Redirige al login si no está autenticado.
             return redirect()->route('login')->with('error', 'Debes iniciar sesión para modificar tu carrito.');
        }
        $user = Auth::user(); // Obtiene el usuario autenticado.

        // Carga la relación 'pedido' del detalle si aún no está cargada para la verificación.
        // `loadMissing` es eficiente, solo carga si no se ha cargado previamente.
        $detallespedidos->loadMissing('pedido');
        $pedidoDelDetalle = $detallespedidos->pedido; // Accede a la relación cargada.

        // 3. Comprobación de Propiedad y Estado:
        // Verifica que el detalle pertenezca a un pedido PENDIENTE del usuario actual.
        // - ¿Existe el pedido asociado al detalle? (!$pedidoDelDetalle)
        // - ¿El ID del cliente del pedido coincide con el ID del usuario logueado? ($pedidoDelDetalle->cliente_id !== $user->id)
        // - ¿El estado del pedido es 'pendiente'? ($pedidoDelDetalle->status !== Pedidos::STATUS_PENDIENTE)
        if (!$pedidoDelDetalle || $pedidoDelDetalle->cliente_id !== $user->id || $pedidoDelDetalle->status !== Pedidos::STATUS_PENDIENTE) {
             // Si alguna condición falla, registra una advertencia y redirige con error.
             Log::warning("Intento no autorizado de actualizar detalle ID {$detallespedidos->id} por usuario ID {$user->id}");
             return redirect()->route('detallespedidos.index')->with('error', 'No se pudo actualizar el item.');
        }

        // 4. Actualización de la Cantidad:
        // Si la autorización es correcta, actualiza el atributo 'cantidad' del modelo $detallespedidos.
        $detallespedidos->cantidad = $request->cantidad;
        $detallespedidos->save(); // Guarda los cambios en la base de datos.

        // 5. Redirección Éxito:
        // Redirige de vuelta a la vista del carrito con un mensaje de éxito.
        return redirect()->route('detallespedidos.index')
                       ->with('success', 'Cantidad actualizada correctamente.');
    }

    /**
     * Elimina un ítem específico (Detallespedidos) del carrito.
     *
     * Se invoca típicamente desde un botón o formulario en la vista del carrito (`detallespedidos.index`).
     * Verifica que el usuario esté autenticado (`Auth::check()`).
     * Realiza la misma comprobación de autorización estricta que en `update()`: carga la relación
     * con el pedido (`loadMissing('pedido')`) y verifica que el pedido exista, pertenezca al
     * usuario autenticado (`cliente_id`) y esté en estado 'pendiente' (`Pedidos::STATUS_PENDIENTE`).
     * Si la autorización falla, registra una advertencia y redirige con error.
     * Si la autorización es correcta, elimina el registro `Detallespedidos` de la base de datos
     * usando el método `delete()` sobre la instancia inyectada por Route Model Binding.
     * Finalmente, redirige de vuelta a la vista del carrito (`detallespedidos.index`) con un mensaje de éxito.
     *
     * @param  \App\Models\Detallespedidos  $detallespedidos Instancia del ítem a eliminar (Route Model Binding).
     * @return \Illuminate\Http\RedirectResponse Redirige al índice del carrito o a login/atrás si hay error.
     */
    public function destroy(Detallespedidos $detallespedidos): RedirectResponse
    {
         // 1. Autorización: Verifica autenticación.
        if (!Auth::check()) {
             return redirect()->route('login')->with('error', 'Debes iniciar sesión para modificar tu carrito.');
        }
        $user = Auth::user(); // Obtiene el usuario autenticado.

        // Carga la relación 'pedido' si es necesario para la verificación de propiedad y estado.
        $detallespedidos->loadMissing('pedido');
        $pedidoDelDetalle = $detallespedidos->pedido; // Accede a la relación.

        // 2. Comprobación de Propiedad y Estado:
        // Misma verificación crucial que en update() para asegurar que solo se borren ítems del carrito pendiente del usuario.
        if (!$pedidoDelDetalle || $pedidoDelDetalle->cliente_id !== $user->id || $pedidoDelDetalle->status !== Pedidos::STATUS_PENDIENTE) {
             Log::warning("Intento no autorizado de eliminar detalle ID {$detallespedidos->id} por usuario ID {$user->id}");
             return redirect()->route('detallespedidos.index')->with('error', 'No se pudo eliminar el item.');
        }

        // 3. Eliminación del Ítem:
        // Si la autorización es correcta, elimina el registro del detalle de la base de datos.
        $detallespedidos->delete();


        // 4. Redirección Éxito:
        // Redirige de vuelta a la vista del carrito con un mensaje de éxito.
        return redirect()->route('detallespedidos.index')
                       ->with('success', 'Item eliminado del carrito.');
    }
}
