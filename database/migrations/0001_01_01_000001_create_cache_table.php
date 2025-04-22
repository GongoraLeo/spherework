<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreateCacheTable
 *
 * Migración que crea las tablas necesarias para el sistema de caché
 * y bloqueo de caché de Laravel cuando se utiliza el driver 'database'.
 * Define las tablas `cache` y `cache_locks`.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * Crea la tabla `cache` para almacenar los datos cacheados y la tabla
     * `cache_locks` para gestionar los bloqueos atómicos de caché.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `cache`.
         * Utilizada por el driver de caché 'database' de Laravel para almacenar
         * pares clave-valor cacheados y su tiempo de expiración.
         */
        Schema::create('cache', function (Blueprint $table) {
            // Columna para la clave única del elemento cacheado, actúa como clave primaria.
            $table->string('key')->primary();
            // Columna para almacenar el valor cacheado (serializado). `mediumText` permite valores más grandes.
            $table->mediumText('value');
            // Columna para almacenar el timestamp Unix de cuándo expira el elemento cacheado.
            $table->integer('expiration');
        });

        /**
         * Crea la tabla `cache_locks`.
         * Utilizada por el sistema de bloqueo atómico de caché de Laravel para
         * prevenir condiciones de carrera al regenerar elementos cacheados.
         */
        Schema::create('cache_locks', function (Blueprint $table) {
            // Columna para la clave única del bloqueo, actúa como clave primaria.
            $table->string('key')->primary();
            // Columna para almacenar un identificador único del propietario del bloqueo.
            $table->string('owner');
            // Columna para almacenar el timestamp Unix de cuándo expira el bloqueo.
            $table->integer('expiration');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina las tablas `cache` y `cache_locks` si existen,
     * revirtiendo la operación realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'cache' si existe.
        Schema::dropIfExists('cache');
        // Elimina la tabla 'cache_locks' si existe.
        Schema::dropIfExists('cache_locks');
    }
};
