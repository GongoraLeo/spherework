<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Detallespedidos;
use App\Models\Libros; // Para obtener el precio

class DetallespedidosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Detallespedidos::query()->delete();

        // Asume IDs de pedidos y libros creados en sus seeders
        // Pedido 1 (Juan, entregado)
        $libro1 = Libros::find(1); // Cien años de soledad
        if ($libro1) {
            Detallespedidos::create([
                'pedido_id' => 1,
                'libro_id' => $libro1->id,
                'cantidad' => 1,
                'precio' => $libro1->precio, // Usar el precio del libro
            ]);
        }

        // Pedido 2 (Maria, enviado)
        $libro2 = Libros::find(2); // La casa de los espíritus
        $libro3 = Libros::find(3); // Tokio Blues
        if ($libro2) {
            Detallespedidos::create([
                'pedido_id' => 2,
                'libro_id' => $libro2->id,
                'cantidad' => 2,
                'precio' => $libro2->precio,
            ]);
        }
         if ($libro3) {
            Detallespedidos::create([
                'pedido_id' => 2,
                'libro_id' => $libro3->id,
                'cantidad' => 1,
                'precio' => $libro3->precio,
            ]);
        }

        // Pedido 3 (Juan, pendiente/carrito)
         if ($libro1) {
            Detallespedidos::create([
                'pedido_id' => 3,
                'libro_id' => $libro1->id,
                'cantidad' => 1,
                'precio' => $libro1->precio,
            ]);
        }
    }
}
