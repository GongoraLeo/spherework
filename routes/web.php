<?php

use App\Models\Autores;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutoresController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ComentariosController;
use App\Http\Controllers\LibrosController;
use App\Http\Controllers\DetallespedidosController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\EmpleadosController;
use App\Http\Controllers\EditorialesController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/killo', function () {
    return 'Killo ke es lo ke dise tú que no hay quien tentienda!';
});

//Rutas del controlador de Autores
Route::get('/autores', AutoresController::class . '@index')->name('autores.index');
Route::get('/autores/create', AutoresController::class . '@create')->name('autores.create');
Route::post('/autores', AutoresController::class . '@store')->name('autores.store');
Route::get('/autores/{autores}', AutoresController::class . '@show')->name('autores.show');
Route::get('/autores/{autores}/edit', AutoresController::class . '@edit')->name('autores.edit');
Route::put('/autores/{autores}', AutoresController::class . '@update')->name('autores.update');
Route::delete('/autores/{autores}', AutoresController::class . '@destroy')->name('autores.destroy');

//Rutas del controlador de Clientes
Route::get('/clientes', ClientesController::class . '@index')->name('clientes.index');
Route::get('/clientes/create', ClientesController::class . '@create')->name('clientes.create');
Route::post('/clientes', ClientesController::class . '@store')->name('clientes.store');
Route::get('/clientes/{clientes}', ClientesController::class . '@show')->name('clientes.show');
Route::get('/clientes/{clientes}/edit', ClientesController::class . '@edit')->name('clientes.edit');
Route::put('/clientes/{clientes}', ClientesController::class . '@update')->name('clientes.update');
Route::delete('/clientes/{clientes}', ClientesController::class . '@destroy')->name('clientes.destroy');

//Rutas del controlador de Comentarios
Route::get('/comentarios', ComentariosController::class . '@index')->name('comentarios.index');
Route::get('/comentarios/create', ComentariosController::class . '@create')->name('comentarios.create');
Route::post('/comentarios', ComentariosController::class . '@store')->name('comentarios.store');
Route::get('/comentarios/{comentarios}', ComentariosController::class . '@show')->name('comentarios.show');
Route::get('/comentarios/{comentarios}/edit', ComentariosController::class . '@edit')->name('comentarios.edit');
Route::put('/comentarios/{comentarios}', ComentariosController::class . '@update')->name('comentarios.update');
Route::delete('/comentarios/{comentarios}', ComentariosController::class . '@destroy')->name('comentarios.destroy');

//Rutas del controlador de Detallespedidos
Route::get('/detallespedidos', DetallespedidosController::class . '@index')->name('detallespedidos.index');
Route::get('/detallespedidos/create', DetallespedidosController::class . '@create')->name('detallespedidos.create');
Route::post('/detallespedidos', DetallespedidosController::class . '@store')->name('detallespedidos.store');
Route::get('/detallespedidos/{detallespedidos}', DetallespedidosController::class . '@show')->name('detallespedidos.show');
Route::get('/detallespedidos/{detallespedidos}/edit', DetallespedidosController::class . '@edit')->name('detallespedidos.edit');
Route::put('/detallespedidos/{detallespedidos}', DetallespedidosController::class . '@update')->name('detallespedidos.update');
Route::delete('/detallespedidos/{detallespedidos}', DetallespedidosController::class . '@destroy')->name('detallespedidos.destroy');

//Rutas del controlador de Editoriales
Route::get('/editoriales', EditorialesController::class . '@index')->name('editoriales.index');
Route::get('/editoriales/create', EditorialesController::class . '@create')->name('editoriales.create');
Route::post('/editoriales', EditorialesController::class . '@store')->name('editoriales.store');
Route::get('/editoriales/{editoriales}', EditorialesController::class . '@show')->name('editoriales.show');
Route::get('/editoriales/{editoriales}/edit', EditorialesController::class . '@edit')->name('editoriales.edit');
Route::put('/editoriales/{editoriales}', EditorialesController::class . '@update')->name('editoriales.update');
Route::delete('/editoriales/{editoriales}', EditorialesController::class . '@destroy')->name('editoriales.destroy');

//Rutas del controlador de Empleados
Route::get('/empleados', EmpleadosController::class . '@index')->name('empleados.index');
Route::get('/empleados/create', EmpleadosController::class . '@create')->name('empleados.create');
Route::post('/empleados', EmpleadosController::class . '@store')->name('empleados.store');
Route::get('/empleados/{empleados}', EmpleadosController::class . '@show')->name('empleados.show');
Route::get('/empleados/{empleados}/edit', EmpleadosController::class . '@edit')->name('empleados.edit');
Route::put('/empleados/{empleados}', EmpleadosController::class . '@update')->name('empleados.update');
Route::delete('/empleados/{empleados}', EmpleadosController::class . '@destroy')->name('empleados.destroy');

//Rutas del controlador de Libros
Route::get('/libros', LibrosController::class . '@index')->name('libros.index');
Route::get('/libros/create', LibrosController::class . '@create')->name('libros.create');
Route::post('/libros', LibrosController::class . '@store')->name('libros.store');
Route::get('/libros/{libros}', LibrosController::class . '@show')->name('libros.show');
Route::get('/libros/{libros}/edit', LibrosController::class . '@edit')->name('libros.edit');
Route::put('/libros/{libros}', LibrosController::class . '@update')->name('libros.update');
Route::delete('/libros/{libros}', LibrosController::class . '@destroy')->name('libros.destroy');

//Rutas del controlador de Pedidos
Route::get('/pedidos', PedidosController::class . '@index')->name('pedidos.index');
Route::get('/pedidos/create', PedidosController::class . '@create')->name('pedidos.create');
Route::post('/pedidos', PedidosController::class . '@store')->name('pedidos.store');
Route::get('/pedidos/{pedidos}', PedidosController::class . '@show')->name('pedidos.show');
Route::get('/pedidos/{pedidos}/edit', PedidosController::class . '@edit')->name('pedidos.edit');
Route::put('/pedidos/{pedidos}', PedidosController::class . '@update')->name('pedidos.update');
Route::delete('/pedidos/{pedidos}', PedidosController::class . '@destroy')->name('pedidos.destroy');


