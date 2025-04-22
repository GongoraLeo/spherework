<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreatePedidosTable
 *
 * Migración que crea la tabla `pedidos` en la base de datos.
 * Esta tabla almacena la información de los pedidos realizados por los usuarios,
 * incluyendo su estado, total y la relación con el usuario (cliente).
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * Crea la tabla `pedidos` con las columnas `id`, `cliente_id`, `status`,
     * `total`, `fecha_pedido`, `created_at` y `updated_at`.
     * Define una clave foránea en `cliente_id` que referencia a la tabla `users`.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `pedidos`.
         * Define la estructura para almacenar los datos de los pedidos.
         */
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental y clave primaria.

            // Columna para la clave foránea que referencia a la tabla 'users'.
            // Se mantiene el nombre 'cliente_id' por consistencia con el código existente.
            $table->foreignId('cliente_id');

            // Columna para el estado del pedido (ej. 'pendiente', 'completado').
            // Se establece 'pendiente' como valor por defecto.
            $table->string('status')->default('pendiente');
            // Columna para el total del pedido, tipo decimal (10 dígitos, 2 decimales), puede ser nula.
            $table->decimal('total', 10, 2)->nullable();
            // Columna para la fecha y hora del pedido, tipo timestamp, puede ser nula.
            $table->timestamp('fecha_pedido')->nullable();

            $table->timestamps(); // Columnas `created_at` y `updated_at` automáticas.

            /**
             * Define la restricción de clave foránea para `cliente_id`.
             * Asegura que el valor en `cliente_id` corresponda a un `id` existente
             * en la tabla `users`. Si se elimina un usuario, los pedidos asociados
             * también se eliminarán (`onDelete('cascade')`).
             */
            $table->foreign('cliente_id')
                  ->references('id')
                  ->on('users') // Apunta a la tabla 'users'.
                  ->onDelete('cascade'); // Define la acción en cascada al eliminar el usuario.
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina la tabla `pedidos` si existe, revirtiendo la operación
     * realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'pedidos' si existe.
        Schema::dropIfExists('pedidos');
    }
};
