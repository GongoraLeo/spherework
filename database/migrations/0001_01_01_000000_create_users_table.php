<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreateUsersTable
 *
 * Migración inicial que crea las tablas fundamentales para la autenticación
 * y gestión de usuarios, así como las tablas para el reseteo de contraseñas
 * y el manejo de sesiones.
 * Define las tablas `users`, `password_reset_tokens` y `sessions`.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * Crea las tablas `users`, `password_reset_tokens` y `sessions`
     * con sus respectivas columnas y restricciones.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `users`.
         * Esta tabla almacena la información principal de los usuarios de la aplicación,
         * incluyendo credenciales de acceso y rol.
         */
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental y clave primaria.
            $table->string('name'); // Columna para el nombre del usuario.
            $table->string('email')->unique(); // Columna para el email, debe ser único.
            $table->timestamp('email_verified_at')->nullable(); // Columna para registrar la verificación del email, puede ser nula.
            $table->string('password'); // Columna para almacenar la contraseña hasheada.
            $table->rememberToken(); // Columna para el token de "recordarme".
            $table->timestamps(); // Columnas `created_at` y `updated_at` automáticas.
            // Columna ENUM para definir el rol del usuario, con valores permitidos 'administrador' o 'cliente'.
            // Por defecto, se asigna el rol 'cliente'.
            $table->enum('rol', ['administrador', 'cliente'])->default('cliente');
        });

        /**
         * Crea la tabla `password_reset_tokens`.
         * Esta tabla almacena los tokens utilizados para el proceso de reseteo de contraseñas.
         */
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // Columna para el email asociado al token, es la clave primaria.
            $table->string('token'); // Columna para almacenar el token de reseteo.
            $table->timestamp('created_at')->nullable(); // Columna para registrar cuándo se creó el token, puede ser nula.
        });

        /**
         * Crea la tabla `sessions`.
         * Esta tabla es utilizada por Laravel para almacenar los datos de sesión
         * cuando se utiliza el driver de sesión 'database'.
         */
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // ID único de la sesión, es la clave primaria.
            // ID del usuario asociado a la sesión, puede ser nulo (sesiones de invitados). Indexado para búsquedas rápidas.
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable(); // Dirección IP del cliente, puede ser nula.
            $table->text('user_agent')->nullable(); // Información del User-Agent del navegador, puede ser nula.
            $table->longText('payload'); // Datos serializados de la sesión.
            // Timestamp de la última actividad, indexado para facilitar la limpieza de sesiones antiguas.
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina las tablas `users`, `password_reset_tokens` y `sessions`
     * si existen, revirtiendo la operación realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'users' si existe.
        Schema::dropIfExists('users');
        // Elimina la tabla 'password_reset_tokens' si existe.
        Schema::dropIfExists('password_reset_tokens');
        // Elimina la tabla 'sessions' si existe.
        Schema::dropIfExists('sessions');
    }
};
