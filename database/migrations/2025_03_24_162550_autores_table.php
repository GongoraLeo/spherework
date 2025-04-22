<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreateAutoresTable
 *
 * Migración que crea la tabla `autores` en la base de datos.
 * Esta tabla almacena la información sobre los autores de los libros.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * Crea la tabla `autores` con las columnas `id`, `nombre`, `pais`,
     * `created_at` y `updated_at`.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `autores`.
         * Define la estructura de la tabla que almacenará los datos de los autores.
         */
        Schema::create('autores', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental y clave primaria.
            $table->string('nombre'); // Columna para el nombre del autor.
            $table->string('pais'); // Columna para el país de origen del autor.
            $table->timestamps(); // Columnas `created_at` y `updated_at` automáticas para el registro de tiempo.
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina la tabla `autores` si existe, revirtiendo la operación
     * realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'autores' si existe.
        Schema::dropIfExists('autores');
    }
};
