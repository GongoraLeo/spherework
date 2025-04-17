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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Rutas Públicas ---

Route::get('/', [LibrosController::class, 'index'])->name('libros.index');
Route::get('/libros/{libros}', [LibrosController::class, 'show'])->name('libros.show');

// Rutas públicas para Autores y Editoriales (si las necesitas separadas del admin)
// Estas rutas NO deben requerir login/admin en el controlador si son públicas
// Route::get('/autores', [AutoresController::class, 'index'])->name('public.autores.index'); // Renombrado si hay conflicto
// Route::get('/autores/{autores}', [AutoresController::class, 'show'])->name('public.autores.show'); // Renombrado si hay conflicto
// Route::get('/editoriales', [EditorialesController::class, 'index'])->name('public.editoriales.index'); // Renombrado si hay conflicto
// Route::get('/editoriales/{editoriales}', [EditorialesController::class, 'show'])->name('public.editoriales.show'); // Renombrado si hay conflicto


// --- Rutas Autenticadas ---

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return redirect()->route('profile.entry');
    })->middleware('verified')->name('dashboard');

    Route::get('/profile-entry', ProfileEntryController::class)->name('profile.entry');

    // --- RUTAS DE ADMINISTRACIÓN (URLs con /admin/) ---
    // Aplicar middleware de admin aquí si no lo haces en el controlador
    // Route::middleware('admin')->group(function() { // <-- Considera agrupar con middleware

        // PANEL DE ADMIN
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
             ->name('admin.dashboard');

        // Gestión de clientes por el admin
        Route::get('/admin/clientes', [ClientesController::class, 'index'])->name('admin.clientes.index'); // <-- Verificada
        Route::get('/admin/clientes/{cliente}', [ClientesController::class, 'show'])->name('admin.clientes.show');
        // Añade edit/update/destroy si los implementas, con nombre 'admin.clientes.*'

        // Gestión de Autores (Admin)
        Route::get('/admin/autores', [AutoresController::class, 'index'])->name('admin.autores.index');
        Route::get('/admin/autores/create', [AutoresController::class, 'create'])->name('admin.autores.create'); // <-- Verificada (para el botón Crear)
        Route::post('/admin/autores', [AutoresController::class, 'store'])->name('admin.autores.store');
        Route::get('/admin/autores/{autores}', [AutoresController::class, 'show'])->name('admin.autores.show'); // <-- Ruta show para admin
        Route::get('/admin/autores/{autores}/edit', [AutoresController::class, 'edit'])->name('admin.autores.edit');
        Route::put('/admin/autores/{autores}', [AutoresController::class, 'update'])->name('admin.autores.update');
        Route::delete('/admin/autores/{autores}', [AutoresController::class, 'destroy'])->name('admin.autores.destroy');

        // Gestión de Editoriales (Admin)
        Route::get('/admin/editoriales', [EditorialesController::class, 'index'])->name('admin.editoriales.index');
        Route::get('/admin/editoriales/create', [EditorialesController::class, 'create'])->name('admin.editoriales.create');
        Route::post('/admin/editoriales', [EditorialesController::class, 'store'])->name('admin.editoriales.store');
        Route::get('/admin/editoriales/{editoriales}', [EditorialesController::class, 'show'])->name('admin.editoriales.show'); // <-- Verificada (para el botón Ver)
        Route::get('/admin/editoriales/{editoriales}/edit', [EditorialesController::class, 'edit'])->name('admin.editoriales.edit');
        Route::put('/admin/editoriales/{editoriales}', [EditorialesController::class, 'update'])->name('admin.editoriales.update');
        Route::delete('/admin/editoriales/{editoriales}', [EditorialesController::class, 'destroy'])->name('admin.editoriales.destroy');

    // }); // <-- Fin del grupo middleware admin (si lo usas)
    // --- FIN RUTAS DE ADMINISTRACIÓN ---


    // --- OTRAS RUTAS AUTENTICADAS (Sin /admin/ en URL) ---

    // Perfil de Usuario (Cliente)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Carrito de Compras (Detallespedidos)
    Route::get('/detallespedidos', [DetallespedidosController::class, 'index'])->name('detallespedidos.index');
    Route::post('/detallespedidos', [DetallespedidosController::class, 'store'])->name('detallespedidos.store');
    Route::put('/detallespedidos/{detallespedidos}', [DetallespedidosController::class, 'update'])->name('detallespedidos.update');
    Route::delete('/detallespedidos/{detallespedidos}', [DetallespedidosController::class, 'destroy'])->name('detallespedidos.destroy');

    // Comentarios (Usuario/Admin)
    Route::post('/comentarios', [ComentariosController::class, 'store'])->name('comentarios.store');
    Route::get('/comentarios/{comentarios}/edit', [ComentariosController::class, 'edit'])->name('comentarios.edit');
    Route::match(['put', 'patch'], '/comentarios/{comentarios}', [ComentariosController::class, 'update'])->name('comentarios.update');
    Route::delete('/comentarios/{comentarios}', [ComentariosController::class, 'destroy'])->name('comentarios.destroy');
    // Si necesitas una lista separada para admin:
    // Route::get('/admin/comentarios', [ComentariosController::class, 'indexAdmin'])->name('admin.comentarios.index');

    // Proceso de Checkout y Pedidos (Usuario/Admin)
    Route::post('/checkout/process', [PedidosController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/checkout/success/{pedidos}', [PedidosController::class, 'showSuccess'])->name('checkout.success');
    // Rutas CRUD para Pedidos
    Route::get('/pedidos', [PedidosController::class, 'index'])->name('pedidos.index'); // Controlador filtra por rol
    Route::get('/pedidos/create', [PedidosController::class, 'create'])->name('pedidos.create'); // No usado?
    Route::post('/pedidos', [PedidosController::class, 'store'])->name('pedidos.store'); // No usado?
    Route::get('/pedidos/{pedidos}', [PedidosController::class, 'show'])->name('pedidos.show'); // Controlador verifica permiso
    Route::get('/pedidos/{pedidos}/edit', [PedidosController::class, 'edit'])->name('pedidos.edit'); // Controlador verifica admin
    Route::put('/pedidos/{pedidos}', [PedidosController::class, 'update'])->name('pedidos.update'); // Controlador verifica admin
    Route::delete('/pedidos/{pedidos}', [PedidosController::class, 'destroy'])->name('pedidos.destroy'); // Controlador verifica admin
    // **NOTA:** El error "View [pedidos.index] not found" indica que falta el archivo de vista. Debes crearlo.

    // ***** GESTIÓN DE LIBROS (Admin/Permisos en Controlador) *****
    // Rutas CRUD para libros, accesibles fuera de /admin/
    // El controlador LibrosController debe verificar si el usuario es admin para create/store/edit/update/destroy
    Route::get('/libros/create', [LibrosController::class, 'create'])->name('libros.create');
    Route::post('/libros', [LibrosController::class, 'store'])->name('libros.store');
    Route::get('/libros/{libros}/edit', [LibrosController::class, 'edit'])->name('libros.edit');
    Route::put('/libros/{libros}', [LibrosController::class, 'update'])->name('libros.update');
    Route::delete('/libros/{libros}', [LibrosController::class, 'destroy'])->name('libros.destroy');


}); // Fin del grupo middleware('auth')


// --- Rutas de Autenticación (Breeze) ---
require __DIR__.'/auth.php';
