<?php

use Illuminate\Database\Migrations\Migration; // Clase base para migraciones.
use Illuminate\Database\Schema\Blueprint; // Clase para definir la estructura de la tabla.
use Illuminate\Support\Facades\Schema; // Fachada para interactuar con el esquema de la base de datos.

/**
 * Class CreateJobsTable
 *
 * Migración que crea las tablas necesarias para el sistema de colas (Queues) de Laravel.
 * Define las tablas `jobs` (para trabajos en cola), `job_batches` (para lotes de trabajos)
 * y `failed_jobs` (para registrar trabajos que han fallado).
 * Estas tablas son utilizadas por Laravel cuando se configura el driver de cola 'database'.
 */
return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * Crea las tablas `jobs`, `job_batches` y `failed_jobs` con sus
     * respectivas columnas y configuraciones, necesarias para el funcionamiento
     * del sistema de colas de Laravel con el driver de base de datos.
     *
     * @return void
     */
    public function up(): void
    {
        /**
         * Crea la tabla `jobs`.
         * Almacena la información de los trabajos que están actualmente en la cola
         * esperando ser procesados por un worker.
         */
        Schema::create('jobs', function (Blueprint $table) {
            $table->id(); // ID único autoincremental para el trabajo.
            // Nombre de la cola a la que pertenece el trabajo. Indexado para búsquedas eficientes.
            $table->string('queue')->index();
            // Datos serializados del trabajo (la clase del job y sus propiedades).
            $table->longText('payload');
            // Número de veces que se ha intentado procesar el trabajo.
            $table->unsignedTinyInteger('attempts');
            // Timestamp Unix de cuándo un worker reservó el trabajo (null si no está reservado).
            $table->unsignedInteger('reserved_at')->nullable();
            // Timestamp Unix de cuándo el trabajo estará disponible para ser procesado de nuevo (después de un reintento).
            $table->unsignedInteger('available_at');
            // Timestamp Unix de cuándo se creó el trabajo (se añadió a la cola).
            $table->unsignedInteger('created_at');
        });

        /**
         * Crea la tabla `job_batches`.
         * Almacena información sobre lotes de trabajos, permitiendo agrupar
         * múltiples trabajos y monitorear su progreso colectivo.
         */
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary(); // ID único del lote (generalmente UUID), clave primaria.
            $table->string('name'); // Nombre descriptivo del lote.
            $table->integer('total_jobs'); // Número total de trabajos en el lote.
            $table->integer('pending_jobs'); // Número de trabajos pendientes de procesar en el lote.
            $table->integer('failed_jobs'); // Número de trabajos que han fallado dentro del lote.
            // IDs (UUIDs) serializados de los trabajos que han fallado en el lote.
            $table->longText('failed_job_ids');
            // Opciones adicionales serializadas para el lote (ej. callbacks). Puede ser nulo.
            $table->mediumText('options')->nullable();
            // Timestamp Unix de cuándo se canceló el lote (null si no se canceló).
            $table->integer('cancelled_at')->nullable();
            // Timestamp Unix de cuándo se creó el lote.
            $table->integer('created_at');
            // Timestamp Unix de cuándo finalizó el lote (todos los trabajos completados o fallidos). Puede ser nulo.
            $table->integer('finished_at')->nullable();
        });

        /**
         * Crea la tabla `failed_jobs`.
         * Registra información detallada sobre los trabajos que han fallado
         * durante su procesamiento en la cola.
         */
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id(); // ID único autoincremental para el registro del trabajo fallido.
            $table->string('uuid')->unique(); // UUID único del trabajo que falló.
            $table->text('connection'); // Nombre de la conexión de cola utilizada.
            $table->text('queue'); // Nombre de la cola en la que estaba el trabajo.
            $table->longText('payload'); // Datos serializados del trabajo que falló.
            $table->longText('exception'); // Mensaje y traza de la excepción que causó el fallo.
            // Timestamp de cuándo ocurrió el fallo (se establece automáticamente a la hora actual).
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * Elimina las tablas `jobs`, `job_batches` y `failed_jobs` si existen,
     * revirtiendo la operación realizada en el método `up`.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'jobs' si existe.
        Schema::dropIfExists('jobs');
        // Elimina la tabla 'job_batches' si existe.
        Schema::dropIfExists('job_batches');
        // Elimina la tabla 'failed_jobs' si existe.
        Schema::dropIfExists('failed_jobs');
    }
};
