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
     * Busca el pedido con estado 'pendiente' asociado al usuario. Si existe,
     * recupera los detalles (ítems) de ese pedido, incluyendo la información
     * de los libros asociados mediante Eager Loading. Calcula el total del carrito.
     * Renderiza la vista del carrito ('detallespedidos.index').
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista del carrito o redirige al login si no está autenticado.
     */
    public function index(): View|RedirectResponse
    {
        // 1. Autorización: Verificar si el usuario está autenticado.
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
                // Verificación defensiva: asegura que precio y cantidad sean numéricos.
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
     * Este método no es aplicable para el carrito, ya que los ítems se añaden
     * desde la vista del catálogo de libros (usando el método store).
     * Redirige al índice de libros con un mensaje informativo.
     *
     * @return \Illuminate\Http\RedirectResponse Siempre redirige.
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
     * Este método se llama al enviar el formulario "Añadir al Carrito" desde la vista de un libro.
     * Valida los datos recibidos (ID del libro, cantidad, precio).
     * Verifica que el usuario esté autenticado.
     * Busca o crea un pedido con estado 'pendiente' para el usuario, asignando `cliente_id`.
     * Comprueba si el libro ya existe en el carrito pendiente; si es así, actualiza la cantidad.
     * Si no existe, crea un nuevo registro en `Detallespedidos`.
     * Finalmente, redirige al usuario a la vista del carrito.
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

        // 2. Autorización: Verificar si el usuario está autenticado.
        if (!Auth::check()) {
            // Si no está autenticado, registra una advertencia y redirige al login.
            Log::warning("Intento de añadir al carrito sin autenticar.");
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para añadir libros al carrito.');
        }
        // A partir de aquí, es seguro usar Auth::user().
        $user = Auth::user();

        // 3. Buscar o Crear Pedido Pendiente:
        // `firstOrCreate` es un método Eloquent que busca un registro que coincida
        // con el primer array de condiciones. Si no lo encuentra, crea uno nuevo
        // usando los datos del primer array y, opcionalmente, los del segundo.
        try {
            $pedidoPendiente = Pedidos::firstOrCreate(
                [
                    'cliente_id' => $user->id, // Condición: El pedido debe pertenecer al usuario actual.
                    'status'     => Pedidos::STATUS_PENDIENTE, // Condición: El pedido debe estar pendiente.
                ],
                [
                    // Valores que se usarán SOLO si se CREA un nuevo pedido.
                    // 'total' => 0, // El total se recalcula al finalizar, no es necesario aquí.
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
            // con el que se añadió el ítem por primera vez.
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
     * Este método no se utiliza en la lógica actual del carrito, ya que los detalles
     * se ven en conjunto en la vista del carrito (index).
     * Redirige directamente al índice del carrito.
     *
     * @param  \App\Models\Detallespedidos  $detallespedidos Instancia inyectada (Route Model Binding).
     * @return \Illuminate\Http\RedirectResponse Siempre redirige.
     */
    public function show(Detallespedidos $detallespedidos): RedirectResponse
    {
         // Redirige siempre a la vista principal del carrito (`detallespedidos.index`).
         return redirect()->route('detallespedidos.index');
    }

    /**
     * Muestra el formulario para editar un recurso específico (Detallespedidos).
     *
     * Este método no se utiliza en la lógica actual del carrito. La edición de la cantidad
     * se maneja directamente desde la vista del carrito (index) a través del método `update`.
     * Redirige directamente al índice del carrito.
     *
     * @param  \App\Models\Detallespedidos  $detallespedidos Instancia inyectada (Route Model Binding).
     * @return \Illuminate\Http\RedirectResponse Siempre redirige.
     */
    public function edit(Detallespedidos $detallespedidos): RedirectResponse
    {
        // Redirige siempre a la vista principal del carrito (`detallespedidos.index`).
        return redirect()->route('detallespedidos.index');
    }

    /**
     * Actualiza la cantidad de un ítem específico en el carrito.
     *
     * Se llama típicamente desde un formulario en la vista del carrito (detallespedidos.index).
     * Valida la nueva cantidad recibida.
     * Realiza una comprobación de autorización estricta: el usuario debe estar logueado
     * y el ítem (`Detallespedidos`) debe pertenecer a un pedido pendiente de ese usuario.
     * Actualiza la cantidad del ítem en la base de datos y redirige de vuelta al carrito.
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

        // 2. Autorización: Verificar autenticación y propiedad del ítem/pedido.
        if (!Auth::check()) {
             // Redirige al login si no está autenticado.
             return redirect()->route('login')->with('error', 'Debes iniciar sesión para modificar tu carrito.');
        }
        $user = Auth::user(); // Obtiene el usuario autenticado.

        // Carga la relación 'pedido' del detalle si aún no está cargada.
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
     * Se llama típicamente desde un botón/formulario en la vista del carrito.
     * Realiza una comprobación de autorización estricta similar a `update()` para asegurar
     * que el usuario solo pueda eliminar ítems de su propio carrito pendiente.
     * Elimina el registro `Detallespedidos` de la base de datos.
     * Redirige de vuelta al carrito con un mensaje.
     *
     * @param  \App\Models\Detallespedidos  $detallespedidos Instancia del ítem a eliminar (Route Model Binding).
     * @return \Illuminate\Http\RedirectResponse Redirige al índice del carrito o a login/atrás si hay error.
     */
    public function destroy(Detallespedidos $detallespedidos): RedirectResponse
    {
         // 1. Autorización: Verificar autenticación y propiedad del ítem/pedido.
        if (!Auth::check()) {
             return redirect()->route('login')->with('error', 'Debes iniciar sesión para modificar tu carrito.');
        }
        $user = Auth::user(); // Obtiene el usuario autenticado.

        // Carga la relación 'pedido' si es necesario para la verificación.
        $detallespedidos->loadMissing('pedido');
        $pedidoDelDetalle = $detallespedidos->pedido; // Accede a la relación.

        // 2. Comprobación de Propiedad y Estado:
        // Misma verificación crucial que en update().
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
