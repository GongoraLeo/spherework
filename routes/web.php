<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutoresController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ComentariosController;
use App\Http\Controllers\LibrosController;
use App\Http\Controllers\DetallespedidosController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\EmpleadosController;
use App\Http\Controllers\EditorialesController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar rutas web para tu aplicación. Estas
| rutas son cargadas por RouteServiceProvider y todas ellas serán
| asignadas al grupo de middleware "web". ¡Haz algo grandioso!
|
*/

// --- Rutas Públicas ---

// Página principal (Catálogo de Libros)
Route::get('/', [LibrosController::class, 'index'])->name('libros.index');
// Ver detalles de un libro
Route::get('/libros/{libros}', [LibrosController::class, 'show'])->name('libros.show');

// Ver Autores (Opcional: podrían requerir auth si no son públicos)
Route::get('/autores', [AutoresController::class, 'index'])->name('autores.index');
Route::get('/autores/{autores}', [AutoresController::class, 'show'])->name('autores.show');

// Ver Editoriales (Opcional: podrían requerir auth si no son públicos)
Route::get('/editoriales', [EditorialesController::class, 'index'])->name('editoriales.index');
Route::get('/editoriales/{editoriales}', [EditorialesController::class, 'show'])->name('editoriales.show');


// --- Rutas Autenticadas ---

Route::middleware('auth')->group(function () {

    // Dashboard de Breeze (si lo usas)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('verified')->name('dashboard'); // 'verified' si usas verificación de email

    // Perfil de Usuario (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Carrito de Compras (Detallespedidos)
    Route::get('/detallespedidos', [DetallespedidosController::class, 'index'])->name('detallespedidos.index'); // Ver carrito
    Route::post('/detallespedidos', [DetallespedidosController::class, 'store'])->name('detallespedidos.store'); // Añadir item
    Route::put('/detallespedidos/{detallespedidos}', [DetallespedidosController::class, 'update'])->name('detallespedidos.update'); // Actualizar cantidad
    Route::delete('/detallespedidos/{detallespedidos}', [DetallespedidosController::class, 'destroy'])->name('detallespedidos.destroy'); // Eliminar item
    // Nota: create, show, edit para detallespedidos no se usan en el flujo del carrito

    // Comentarios
    Route::post('/comentarios', [ComentariosController::class, 'store'])->name('comentarios.store'); // Crear comentario
    Route::put('/comentarios/{comentarios}', [ComentariosController::class, 'update'])->name('comentarios.update'); // Actualizar (si se permite)
    Route::delete('/comentarios/{comentarios}', [ComentariosController::class, 'destroy'])->name('comentarios.destroy'); // Eliminar (si se permite)
    // Nota: index, create, show, edit para comentarios podrían ser solo para admin

    // Proceso de Checkout
    Route::post('/pedidos/checkout', [PedidosController::class, 'processCheckout'])->name('pedidos.checkout.process');
    Route::get('/pedidos/{pedido}/success', [PedidosController::class, 'showSuccess'])->name('pedidos.checkout.success');


    // --- Rutas de Administración (Requieren Auth, Controladores deben verificar Rol 'administrador') ---

    // Gestión de Libros
    Route::get('/libros/create', [LibrosController::class, 'create'])->name('libros.create');
    Route::post('/libros', [LibrosController::class, 'store'])->name('libros.store'); // POST para crear
    Route::get('/libros/{libros}/edit', [LibrosController::class, 'edit'])->name('libros.edit');
    Route::put('/libros/{libros}', [LibrosController::class, 'update'])->name('libros.update'); // PUT para actualizar
    Route::delete('/libros/{libros}', [LibrosController::class, 'destroy'])->name('libros.destroy');

    // Gestión de Autores
    Route::get('/autores/create', [AutoresController::class, 'create'])->name('autores.create');
    Route::post('/autores', [AutoresController::class, 'store'])->name('autores.store');
    Route::get('/autores/{autores}/edit', [AutoresController::class, 'edit'])->name('autores.edit');
    Route::put('/autores/{autores}', [AutoresController::class, 'update'])->name('autores.update');
    Route::delete('/autores/{autores}', [AutoresController::class, 'destroy'])->name('autores.destroy');

    // Gestión de Editoriales
    Route::get('/editoriales/create', [EditorialesController::class, 'create'])->name('editoriales.create');
    Route::post('/editoriales', [EditorialesController::class, 'store'])->name('editoriales.store');
    Route::get('/editoriales/{editoriales}/edit', [EditorialesController::class, 'edit'])->name('editoriales.edit');
    Route::put('/editoriales/{editoriales}', [EditorialesController::class, 'update'])->name('editoriales.update');
    Route::delete('/editoriales/{editoriales}', [EditorialesController::class, 'destroy'])->name('editoriales.destroy');

    // Gestión de Pedidos (Vista Admin)
    Route::get('/pedidos', [PedidosController::class, 'index'])->name('pedidos.index'); // Listar todos los pedidos
    Route::get('/pedidos/create', [PedidosController::class, 'create'])->name('pedidos.create'); // Crear pedido manualmente?
    Route::post('/pedidos', [PedidosController::class, 'store'])->name('pedidos.store'); // Guardar pedido manual?
    Route::get('/pedidos/{pedidos}', [PedidosController::class, 'show'])->name('pedidos.show'); // Ver detalle de un pedido (admin)
    Route::get('/pedidos/{pedidos}/edit', [PedidosController::class, 'edit'])->name('pedidos.edit'); // Editar estado/etc.
    Route::put('/pedidos/{pedidos}', [PedidosController::class, 'update'])->name('pedidos.update'); // Actualizar pedido
    Route::delete('/pedidos/{pedidos}', [PedidosController::class, 'destroy'])->name('pedidos.destroy'); // Eliminar pedido

    // Gestión de Clientes (Vista Admin - Usuarios con rol 'cliente')
    // Asumiendo que ClientesController gestiona Users con rol cliente
    Route::get('/clientes', [ClientesController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create', [ClientesController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClientesController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{clientes}', [ClientesController::class, 'show'])->name('clientes.show'); // Usar {user} o {cliente}? Depende del modelo/param
    Route::get('/clientes/{clientes}/edit', [ClientesController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{clientes}', [ClientesController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{clientes}', [ClientesController::class, 'destroy'])->name('clientes.destroy');

    // Gestión de Empleados (Vista Admin - Usuarios con rol 'admin'/'gestor')
    // Asumiendo que EmpleadosController gestiona Users con esos roles
    Route::get('/empleados', [EmpleadosController::class, 'index'])->name('empleados.index');
    Route::get('/empleados/create', [EmpleadosController::class, 'create'])->name('empleados.create');
    Route::post('/empleados', [EmpleadosController::class, 'store'])->name('empleados.store');
    Route::get('/empleados/{empleados}', [EmpleadosController::class, 'show'])->name('empleados.show'); // Usar {user} o {empleado}?
    Route::get('/empleados/{empleados}/edit', [EmpleadosController::class, 'edit'])->name('empleados.edit');
    Route::put('/empleados/{empleados}', [EmpleadosController::class, 'update'])->name('empleados.update');
    Route::delete('/empleados/{empleados}', [EmpleadosController::class, 'destroy'])->name('empleados.destroy');

    // Gestión de Comentarios (Vista Admin - Opcional)
    Route::get('/comentarios', [ComentariosController::class, 'index'])->name('comentarios.index'); // Listar todos
    Route::get('/comentarios/{comentarios}', [ComentariosController::class, 'show'])->name('comentarios.show'); // Ver detalle (admin)
    Route::get('/comentarios/{comentarios}/edit', [ComentariosController::class, 'edit'])->name('comentarios.edit'); // Editar (admin)
    // DELETE y PUT ya están definidos arriba para el usuario, el controlador debe diferenciar permisos

}); // Fin del grupo middleware('auth')


// --- Rutas de Autenticación (Breeze) ---
require __DIR__.'/auth.php';

