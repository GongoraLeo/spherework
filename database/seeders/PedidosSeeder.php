<?php
// database/seeders/PedidosSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pedidos;
use App\Models\User; // Importar User para obtener IDs válidos

class PedidosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vaciar la tabla antes de sembrar
        Pedidos::query()->delete();

        // Obtener los IDs de los usuarios creados por UserSeeder
        // Es más robusto que asumir IDs 1 y 2
        $userCliente1 = User::where('email', 'cliente@spherework.com')->first();
        $userAdmin = User::where('email', 'admin@spherework.com')->first(); // Ejemplo si admin pudiera tener pedidos

        // Crear pedidos solo si los usuarios existen
        if ($userCliente1) {
            // Pedido Completado para cliente 1
            Pedidos::create([
                'cliente_id'   => $userCliente1->id,
                // Usa las columnas CORRECTAS:
                'status'       => Pedidos::STATUS_COMPLETADO, // Usa 'status' y constante
                'total'        => 38.45, // Ejemplo de total calculado (precio libro1 + libro2)
                'fecha_pedido' => now()->subDays(5), // Usa 'fecha_pedido' y timestamp
            ]);

            // Pedido Pendiente (Carrito) para cliente 1
            Pedidos::create([
                'cliente_id'   => $userCliente1->id,
                'status'       => Pedidos::STATUS_PENDIENTE,
                'total'        => null, // Total es null para pendientes
                'fecha_pedido' => null, // Fecha es null para pendientes
            ]);
        } else {
            $this->command->warn('Usuario cliente (cliente@spherework.com) no encontrado. No se crearon pedidos para él.');
        }

        // Ejemplo: Pedido Enviado para otro usuario si existiera
        // $otroCliente = User::find(3); // O buscar por email
        // if ($otroCliente) {
        //     Pedidos::create([
        //         'cliente_id'   => $otroCliente->id,
        //         'status'       => Pedidos::STATUS_ENVIADO,
        //         'total'        => 21.00, // Ejemplo
        //         'fecha_pedido' => now()->subDay(),
        //     ]);
        // }

        // Puedes añadir más pedidos según necesites
    }
}
