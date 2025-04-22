<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreateLibrosTable
 *
 * Migración que crea la tabla `libros` en la base de datos.
 * Esta tabla almacena la información principal sobre los libros, incluyendo
 * sus relaciones con autores y editoriales.
 */
return new class extends Migration {
    /**
     * Ejecuta las migraciones.
     *
     * Crea la tabla `libros` con las columnas `id`, `titulo`, `isbn`,
     * `anio_publicacion`, `precio`, `autor_id`, `editorial_id`,
     * `created_at` y `updated_at`. Las columnas `autor_id` y `editorial_id`
     * están preparadas para ser claves foráneas, aunque la definición explícita
     * de la restricción de clave foránea se realiza en migraciones posteriores
     * o se asume que se manejará por convención o manualmente.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `libros`.
         * Define la estructura de la tabla que almacenará los datos de los libros.
         */
        Schema::create('libros', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental y clave primaria.
            $table->string('titulo'); // Columna para el título del libro.
            $table->string('isbn'); // Columna para el código ISBN del libro.
            $table->integer('anio_publicacion'); // Columna para el año de publicación.

            // Columna para el precio del libro, tipo decimal con 8 dígitos totales y 2 decimales.
            $table->decimal('precio', 8, 2);

            // Columna para la clave foránea que referencia a la tabla 'autores'.
            $table->foreignId('autor_id');
            // Columna para la clave foránea que referencia a la tabla 'editoriales'.
            $table->foreignId('editorial_id');

            $table->timestamps(); // Columnas `created_at` y `updated_at` automáticas.
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina la tabla `libros` si existe, revirtiendo la operación
     * realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'libros' si existe.
        Schema::dropIfExists('libros');
    }
};
