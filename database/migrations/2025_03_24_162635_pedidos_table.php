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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();

            // Columna para relacionar con el usuario (tabla 'users')
            $table->foreignId('cliente_id'); // Mantenemos el nombre por consistencia con el código existente

            // Columnas añadidas/modificadas
            $table->string('status')->default('pendiente'); // Reemplaza 'estado' ENUM
            $table->decimal('total', 10, 2)->nullable(); // Para guardar el total final
            $table->timestamp('fecha_pedido')->nullable(); // Reemplaza 'fecha' DATE

            $table->timestamps(); // created_at y updated_at

            // Definir la clave foránea para cliente_id apuntando a users
            // Asegúrate que la tabla 'users' se cree antes que esta.
            $table->foreign('cliente_id')
                  ->references('id')
                  ->on('users') // Apunta a la tabla users
                  ->onDelete('cascade'); // O 'set null' si prefieres y cliente_id es nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
