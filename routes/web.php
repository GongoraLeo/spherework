<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutoresController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ComentariosController;
use App\Http\Controllers\LibrosController;
use App\Http\Controllers\DetallespedidosController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\EditorialesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ProfileEntryController;

/**
 * --------------------------------------------------------------------------
 * Web Routes
 * --------------------------------------------------------------------------
 *
 * Aquí se definen todas las rutas web para la aplicación. Estas rutas
 * son cargadas por el RouteServiceProvider dentro de un grupo que
 * contiene el middleware "web".
 */

// --- Rutas Públicas ---
// Estas rutas son accesibles para cualquier visitante, sin necesidad de autenticación.

/**
 * Ruta para la página principal (índice de libros).
 * Muestra el listado principal de libros utilizando el método 'index' de LibrosController.
 * @name libros.index
 */
Route::get('/', [LibrosController::class, 'index'])->name('libros.index');

/**
 * Ruta para mostrar los detalles de un libro específico.
 * Utiliza el método 'show' de LibrosController.
 * Se añadió una restricción 'where' para asegurar que el parámetro {libros} sea numérico.
 * Esta restricción evita conflictos con otras rutas como '/libros/create',
 * asumiendo que los IDs de los libros son siempre números enteros.
 * @name libros.show
 * @param int $libros ID del libro a mostrar.
 */
Route::get('/libros/{libros}', [LibrosController::class, 'show'])
    ->where('libros', '[0-9]+') // Restricción para que {libros} solo acepte dígitos.
    ->name('libros.show');

// --- Rutas Autenticadas ---
// Este grupo de rutas requiere que el usuario esté autenticado.
// Se aplica el middleware 'auth' a todas las rutas definidas dentro del grupo.

Route::middleware('auth')->group(function () {

    /**
     * Ruta estándar '/dashboard' de Laravel.
     * Redirige automáticamente a la ruta 'profile.entry' para usuarios autenticados y verificados.
     * Se utiliza el middleware 'verified' para asegurar que el email del usuario ha sido verificado.
     * @name dashboard
     */
    Route::get('/dashboard', function () {
        // Redirige al punto de entrada del perfil del usuario.
        return redirect()->route('profile.entry');
    })->middleware('verified')->name('dashboard');

    /**
     * Ruta de entrada al perfil del usuario.
     * Gestionada por el controlador invocable ProfileEntryController.
     * Determina a qué vista redirigir al usuario (perfil cliente o dashboard admin).
     * @name profile.entry
     */
    Route::get('/profile-entry', ProfileEntryController::class)->name('profile.entry');

    // --- RUTAS DE ADMINISTRACIÓN (URLs con prefijo /admin/) ---
    // Estas rutas están destinadas a la gestión por parte de administradores.
    // Aunque no usan Route::prefix('/admin'), sus URIs comienzan con /admin/.
    // La autorización específica (rol administrador) se gestiona dentro de los controladores correspondientes.

        /**
         * Ruta para el panel principal de administración.
         * Muestra el dashboard del administrador utilizando AdminDashboardController.
         * @name admin.dashboard
         */
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
             ->name('admin.dashboard');

        /**
         * Rutas para la gestión de clientes por parte del administrador.
         * Permiten listar y ver detalles de los clientes.
         */
        Route::get('/admin/clientes', [ClientesController::class, 'index'])->name('admin.clientes.index');
        Route::get('/admin/clientes/{cliente}', [ClientesController::class, 'show'])->name('admin.clientes.show');

        /**
         * Rutas CRUD completas para la gestión de Autores por el administrador.
         * Incluyen listado, creación, almacenamiento, visualización, edición, actualización y eliminación.
         */
        Route::get('/admin/autores', [AutoresController::class, 'index'])->name('admin.autores.index');
        Route::get('/admin/autores/create', [AutoresController::class, 'create'])->name('admin.autores.create');
        Route::post('/admin/autores', [AutoresController::class, 'store'])->name('admin.autores.store');
        Route::get('/admin/autores/{autores}', [AutoresController::class, 'show'])->name('admin.autores.show');
        Route::get('/admin/autores/{autores}/edit', [AutoresController::class, 'edit'])->name('admin.autores.edit');
        Route::put('/admin/autores/{autores}', [AutoresController::class, 'update'])->name('admin.autores.update');
        Route::delete('/admin/autores/{autores}', [AutoresController::class, 'destroy'])->name('admin.autores.destroy');

        /**
         * Rutas CRUD completas para la gestión de Editoriales por el administrador.
         * Incluyen listado, creación, almacenamiento, visualización, edición, actualización y eliminación.
         */
        Route::get('/admin/editoriales', [EditorialesController::class, 'index'])->name('admin.editoriales.index');
        Route::get('/admin/editoriales/create', [EditorialesController::class, 'create'])->name('admin.editoriales.create');
        Route::post('/admin/editoriales', [EditorialesController::class, 'store'])->name('admin.editoriales.store');
        Route::get('/admin/editoriales/{editoriales}', [EditorialesController::class, 'show'])->name('admin.editoriales.show');
        Route::get('/admin/editoriales/{editoriales}/edit', [EditorialesController::class, 'edit'])->name('admin.editoriales.edit');
        Route::put('/admin/editoriales/{editoriales}', [EditorialesController::class, 'update'])->name('admin.editoriales.update');
        Route::delete('/admin/editoriales/{editoriales}', [EditorialesController::class, 'destroy'])->name('admin.editoriales.destroy');

    // --- FIN RUTAS DE ADMINISTRACIÓN ---


    // --- OTRAS RUTAS AUTENTICADAS (Sin prefijo /admin/ en URL) ---
    // Rutas para funcionalidades accesibles por usuarios autenticados (clientes y/o administradores).

    /**
     * Rutas para la gestión del perfil del usuario autenticado.
     * Permiten ver, editar, actualizar y eliminar el propio perfil.
     */
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); // Usa PATCH para actualizaciones parciales.
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * Rutas para la gestión del carrito de compras (Detalles de Pedidos no confirmados).
     * Permiten ver el carrito, añadir elementos (store), actualizar cantidades (update) y eliminar elementos (destroy).
     */
    Route::get('/detallespedidos', [DetallespedidosController::class, 'index'])->name('detallespedidos.index'); // Muestra el carrito actual.
    Route::post('/detallespedidos', [DetallespedidosController::class, 'store'])->name('detallespedidos.store'); // Añade un libro al carrito.
    Route::put('/detallespedidos/{detallespedidos}', [DetallespedidosController::class, 'update'])->name('detallespedidos.update'); // Actualiza cantidad en el carrito.
    Route::delete('/detallespedidos/{detallespedidos}', [DetallespedidosController::class, 'destroy'])->name('detallespedidos.destroy'); // Elimina un item del carrito.

    /**
     * Rutas para la gestión de comentarios sobre libros.
     * Permiten crear, editar, actualizar y eliminar comentarios.
     * La autorización para editar/eliminar se maneja en ComentariosController.
     */
    Route::post('/comentarios', [ComentariosController::class, 'store'])->name('comentarios.store'); // Guarda un nuevo comentario.
    Route::get('/comentarios/{comentarios}/edit', [ComentariosController::class, 'edit'])->name('comentarios.edit'); // Muestra formulario de edición.
    Route::match(['put', 'patch'], '/comentarios/{comentarios}', [ComentariosController::class, 'update'])->name('comentarios.update'); // Actualiza un comentario existente (acepta PUT o PATCH).
    Route::delete('/comentarios/{comentarios}', [ComentariosController::class, 'destroy'])->name('comentarios.destroy'); // Elimina un comentario.

    /**
     * Rutas relacionadas con el proceso de finalización de compra (checkout) y la gestión de pedidos.
     */
    Route::post('/checkout/process', [PedidosController::class, 'processCheckout'])->name('pedidos.checkout.process'); // Procesa el carrito para crear un pedido.
    Route::get('/checkout/success/{pedidos}', [PedidosController::class, 'showSuccess'])->name('pedidos.checkout.success'); // Muestra la página de éxito tras el checkout.

    /**
     * Rutas CRUD para la gestión de Pedidos.
     * Permiten listar, crear (potencialmente admin), almacenar, ver detalles, editar (admin), actualizar (admin) y eliminar (admin) pedidos.
     * El acceso y las acciones permitidas dependen de la lógica en PedidosController (cliente ve los suyos, admin gestiona todos).
     */
    Route::get('/pedidos', [PedidosController::class, 'index'])->name('pedidos.index'); // Lista pedidos (del usuario o todos para admin).
    Route::get('/pedidos/create', [PedidosController::class, 'create'])->name('pedidos.create'); // Formulario para crear pedido (uso admin?).
    Route::post('/pedidos', [PedidosController::class, 'store'])->name('pedidos.store'); // Guarda un nuevo pedido (uso admin?).
    Route::get('/pedidos/{pedidos}', [PedidosController::class, 'show'])->name('pedidos.show'); // Muestra detalles de un pedido.
    Route::get('/pedidos/{pedidos}/edit', [PedidosController::class, 'edit'])->name('pedidos.edit'); // Formulario para editar pedido (admin).
    Route::put('/pedidos/{pedidos}', [PedidosController::class, 'update'])->name('pedidos.update'); // Actualiza un pedido (admin).
    Route::delete('/pedidos/{pedidos}', [PedidosController::class, 'destroy'])->name('pedidos.destroy'); // Elimina un pedido (admin).

    /**
     * Rutas para la gestión de Libros (creación, edición, actualización, eliminación).
     * Estas operaciones requieren autenticación y probablemente permisos de administrador,
     * que se verifican dentro de LibrosController.
     * Se aplica la misma restricción 'where' para el parámetro {libros} por consistencia
     * y para evitar conflictos con otras posibles rutas futuras.
     */
    Route::get('/libros/create', [LibrosController::class, 'create'])->name('libros.create'); // Muestra formulario para crear libro.
    Route::post('/libros', [LibrosController::class, 'store'])->name('libros.store'); // Guarda un nuevo libro.
    Route::get('/libros/{libros}/edit', [LibrosController::class, 'edit'])
        ->where('libros', '[0-9]+') // Restricción numérica para el ID del libro.
        ->name('libros.edit'); // Muestra formulario para editar libro.
    Route::put('/libros/{libros}', [LibrosController::class, 'update'])
        ->where('libros', '[0-9]+') // Restricción numérica para el ID del libro.
        ->name('libros.update'); // Actualiza un libro existente.
    Route::delete('/libros/{libros}', [LibrosController::class, 'destroy'])
        ->where('libros', '[0-9]+') // Restricción numérica para el ID del libro.
        ->name('libros.destroy'); // Elimina un libro.

}); // Fin del grupo de middleware 'auth'


// --- Rutas de Autenticación (Breeze) ---
/**
 * Incluye las rutas predefinidas por Laravel Breeze para la autenticación.
 * Esto cubre el registro, inicio de sesión, recuperación de contraseña,
 * verificación de email y cierre de sesión.
 * Las definiciones de estas rutas se encuentran en el archivo 'auth.php'.
 */
require __DIR__.'/auth.php';

