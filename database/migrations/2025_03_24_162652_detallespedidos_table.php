<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreateDetallespedidosTable
 *
 * Migración que crea la tabla `detallespedidos` en la base de datos.
 * Esta tabla actúa como una tabla intermedia (o de detalle) que almacena
 * los ítems individuales asociados a cada pedido, vinculando un pedido
 * con un libro específico y registrando la cantidad y el precio unitario.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * Crea la tabla `detallespedidos` con las columnas `id`, `cantidad`, `precio`,
     * `pedido_id`, `libro_id`, `created_at` y `updated_at`. Las columnas
     * `pedido_id` y `libro_id` están preparadas para ser claves foráneas,
     * aunque la definición explícita de las restricciones se realiza en
     * migraciones posteriores o se asume que se manejará por convención.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `detallespedidos`.
         * Define la estructura para almacenar los ítems de cada pedido.
         */
        Schema::create('detallespedidos', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental y clave primaria.
            $table->integer('cantidad'); // Columna para la cantidad del libro en este detalle.
            $table->decimal('precio'); // Columna para el precio unitario del libro en este detalle.

            // Columna para la clave foránea que referencia a la tabla 'pedidos'.
            $table->foreignId('pedido_id');
            // Columna para la clave foránea que referencia a la tabla 'libros'.
            $table->foreignId('libro_id');

            $table->timestamps(); // Columnas `created_at` y `updated_at` automáticas.
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina la tabla `detallespedidos` si existe, revirtiendo la operación
     * realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'detallespedidos' si existe.
        Schema::dropIfExists('detallespedidos');
    }
};
