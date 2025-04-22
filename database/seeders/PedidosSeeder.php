<?php
// database/seeders/PedidosSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Trait para deshabilitar eventos de modelo durante el seeding.
use Illuminate\Database\Seeder; // Clase base para los seeders.
use App\Models\Pedidos; // Modelo Pedidos para interactuar con la tabla.
use App\Models\User; // Modelo User para obtener IDs de usuarios válidos.

/**
 * Class PedidosSeeder
 *
 * Seeder encargado de poblar la tabla `pedidos` con datos iniciales.
 * Primero elimina todos los registros existentes en la tabla. Luego, busca
 * usuarios específicos (creados por `UserSeeder`) y, si los encuentra,
 * crea pedidos asociados a ellos con diferentes estados y datos.
 *
 * @package Database\Seeders
 */
class PedidosSeeder extends Seeder
{
    /**
     * Ejecuta las operaciones de seeding para la tabla `pedidos`.
     *
     * Este método primero vacía la tabla `pedidos` usando `Pedidos::query()->delete()`.
     * A continuación, busca un usuario específico con el email 'cliente@spherework.com'
     * utilizando `User::where(...)->first()`. También busca al usuario administrador,
     * aunque no se utiliza para crear pedidos en la lógica actual.
     * Si encuentra el usuario cliente (`$userCliente1`), procede a crear dos pedidos para él:
     * 1. Un pedido con estado `Pedidos::STATUS_COMPLETADO`, un total de ejemplo (38.45) y una fecha
     *    de pedido establecida a 5 días antes de la fecha actual (`now()->subDays(5)`).
     * 2. Un pedido con estado `Pedidos::STATUS_PENDIENTE` (representando un carrito),
     *    con `total` y `fecha_pedido` establecidos a `null`, como es apropiado para un pedido no finalizado.
     * Si el usuario `cliente@spherework.com` no se encuentra, se emite una advertencia
     * en la consola utilizando `$this->command->warn()` indicando que no se crearon pedidos para él.
     *
     * @return void
     */
    public function run(): void
    {
        // Vacía la tabla 'pedidos' antes de insertar nuevos datos.
        Pedidos::query()->delete();

        // Busca el usuario cliente específico por email.
        // Se considera más robusto buscar por un atributo único que asumir IDs fijos.
        $userCliente1 = User::where('email', 'cliente@spherework.com')->first();
        // Busca el usuario administrador (actualmente no se usa para crear pedidos en este seeder).
        $userAdmin = User::where('email', 'admin@spherework.com')->first();

        // Procede a crear pedidos solo si el usuario cliente fue encontrado.
        if ($userCliente1) {
            // Crea un pedido completado para el usuario cliente encontrado.
            Pedidos::create([
                'cliente_id'   => $userCliente1->id, // Asigna el ID del usuario encontrado.
                'status'       => Pedidos::STATUS_COMPLETADO, // Establece el estado usando la constante del modelo.
                'total'        => 38.45, // Asigna un total de ejemplo.
                'fecha_pedido' => now()->subDays(5), // Establece la fecha del pedido a 5 días atrás.
            ]);

            // Crea un pedido pendiente (carrito) para el mismo usuario cliente.
            Pedidos::create([
                'cliente_id'   => $userCliente1->id, // Asigna el ID del usuario encontrado.
                'status'       => Pedidos::STATUS_PENDIENTE, // Establece el estado a pendiente.
                'total'        => null, // El total es nulo para pedidos pendientes.
                'fecha_pedido' => null, // La fecha es nula para pedidos pendientes.
            ]);
        } else {
            // Si no se encontró el usuario cliente, muestra una advertencia en la consola.
            $this->command->warn('Usuario cliente (cliente@spherework.com) no encontrado. No se crearon pedidos para él.');
        }
    }
}
