<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreateComentariosTable
 *
 * Migración que crea la tabla `comentarios` en la base de datos.
 * Esta tabla almacena los comentarios y puntuaciones opcionales que los usuarios
 * realizan sobre los libros. Incluye relaciones con las tablas `libros` y `users`.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * Crea la tabla `comentarios` con las columnas `id`, `comentario`, `puntuacion`,
     * `libro_id`, `user_id`, `created_at` y `updated_at`.
     * Define las claves foráneas para `libro_id` (referenciando a `libros`) y
     * `user_id` (referenciando a `users`), ambas con eliminación en cascada (`onDelete('cascade')`).
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `comentarios`.
         * Define la estructura para almacenar los comentarios de los usuarios sobre los libros.
         */
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental y clave primaria.
            $table->text('comentario'); // Columna para el texto del comentario.
            // Columna para la puntuación numérica (ej. 1-5), puede ser nula.
            $table->integer('puntuacion')->nullable();

            // Columnas para las claves foráneas.
            $table->foreignId('libro_id'); // Referencia a la tabla 'libros'.
            $table->foreignId('user_id');  // Referencia a la tabla 'users'.

            $table->timestamps(); // Columnas `created_at` y `updated_at` automáticas.

            /**
             * Define la restricción de clave foránea para `libro_id`.
             * Asegura que `libro_id` corresponda a un `id` en la tabla `libros`.
             * Si se elimina un libro, sus comentarios asociados también se eliminarán.
             */
            $table->foreign('libro_id')
                  ->references('id')
                  ->on('libros')
                  ->onDelete('cascade');

            /**
             * Define la restricción de clave foránea para `user_id`.
             * Asegura que `user_id` corresponda a un `id` en la tabla `users`.
             * Si se elimina un usuario, sus comentarios asociados también se eliminarán.
             */
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina la tabla `comentarios` si existe, revirtiendo la operación
     * realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'comentarios' si existe.
        Schema::dropIfExists('comentarios');
    }
};
