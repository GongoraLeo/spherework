<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreateClientesTable
 *
 * Migración que crea la tabla `clientes` en la base de datos.
 * Esta tabla parece destinada a almacenar información de clientes, aunque
 * la estructura principal de usuarios se maneja en la tabla `users`.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * Crea la tabla `clientes` con las columnas `id`, `nombre`, `email`,
     * `password`, `created_at` y `updated_at`.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `clientes`.
         * Define la estructura de la tabla que almacenará los datos de los clientes.
         */
        Schema::create('clientes', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental y clave primaria.
            $table->string('nombre'); // Columna para el nombre del cliente.
            $table->string('email'); // Columna para el email del cliente.
            $table->string('password'); // Columna para la contraseña del cliente.
            $table->timestamps(); // Columnas `created_at` y `updated_at` automáticas.
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina la tabla `clientes` si existe, revirtiendo la operación
     * realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'clientes' si existe.
        Schema::dropIfExists('clientes');
    }
};
