<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreateEmpleadosTable
 *
 * Migración que crea la tabla `empleados` en la base de datos.
 * Esta tabla parece destinada a almacenar información sobre empleados internos,
 * con roles específicos como 'administrador' o 'gestor'.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * Crea la tabla `empleados` con las columnas `id`, `nombre`, `email`,
     * `password`, `rol`, `created_at` y `updated_at`.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `empleados`.
         * Define la estructura para almacenar datos de empleados, incluyendo
         * credenciales y un rol específico ('administrador' o 'gestor').
         */
        Schema::create('empleados', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental y clave primaria.
            $table->string('nombre'); // Columna para el nombre del empleado.
            $table->string('email'); // Columna para el email del empleado.
            $table->string('password'); // Columna para la contraseña del empleado.
            // Columna ENUM para definir el rol del empleado, limitado a 'administrador' o 'gestor'.
            $table->enum('rol', ['administrador', 'gestor']);
            $table->timestamps(); // Columnas `created_at` y `updated_at` automáticas.
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina la tabla `empleados` si existe, revirtiendo la operación
     * realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'empleados' si existe.
        Schema::dropIfExists('empleados');
    }
};
