<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->text('comentario'); // Columna para el texto
            $table->integer('puntuacion')->nullable(); // Puntuación opcional

            // Columnas para las relaciones
            $table->foreignId('libro_id');
            $table->foreignId('user_id'); // Reemplaza cliente_id

            $table->timestamps(); // created_at y updated_at

            // Definir claves foráneas
            // Asegúrate que las tablas 'libros' y 'users' se creen antes.
            $table->foreign('libro_id')
                  ->references('id')
                  ->on('libros')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};
