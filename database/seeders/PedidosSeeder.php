<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pedidos;

class PedidosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pedidos::query()->delete();

        Pedidos::create([
            'cliente_id' => 1, // ID de Juan Pérez
            // 'empleado_id' => null, // O el ID de un empleado si aplica
            'fecha' => now()->subDays(5), // Fecha del pedido
            'estado' => 'entregado',
        ]);

        Pedidos::create([
            'cliente_id' => 2, // ID de Maria Gomez
            'fecha' => now()->subDays(1),
            'estado' => 'enviado',
        ]);

        // Podrías crear un pedido 'carrito' si usas esa lógica
        Pedidos::create([
            'cliente_id' => 1, // ID de Juan Pérez
            'fecha' => now(),
            'estado' => 'pendiente', // O 'carrito' si defines ese estado
        ]);
    }
}
