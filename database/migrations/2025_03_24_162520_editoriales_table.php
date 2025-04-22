<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreateEditorialesTable
 *
 * Migración que crea la tabla `editoriales` en la base de datos.
 * Esta tabla almacena la información sobre las editoriales de los libros.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * Crea la tabla `editoriales` con las columnas `id`, `nombre`, `pais`,
     * `created_at` y `updated_at`.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `editoriales`.
         * Define la estructura de la tabla que almacenará los datos de las editoriales.
         */
        Schema::create('editoriales', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental y clave primaria.
            $table->string('nombre'); // Columna para el nombre de la editorial.
            $table->string('pais'); // Columna para el país de origen de la editorial.
            $table->timestamps(); // Columnas `created_at` y `updated_at` automáticas para el registro de tiempo.
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina la tabla `editoriales` si existe, revirtiendo la operación
     * realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'editoriales' si existe.
        Schema::dropIfExists('editoriales');
    }
};
