<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Pedidos; // Importa el modelo
use App\Models\User; // Necesario para la relación cliente_id

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pedidos>
 */
class PedidosFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente de la factory.
     *
     * @var string
     */
    protected $model = Pedidos::class; // Especifica el modelo

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Asigna un cliente existente o crea uno nuevo (asegúrate que UserFactory cree 'cliente' por defecto)
            'cliente_id' => User::factory(),
            // Por defecto, los pedidos creados por factory estarán pendientes (simulando carrito)
            'status' => Pedidos::STATUS_PENDIENTE,
            // Fecha y total se suelen establecer al completar el pedido
            'fecha_pedido' => null,
            'total' => null,
        ];
    }

    /**
     * Indica que el pedido está completado.
     */
    public function completado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Pedidos::STATUS_COMPLETADO,
            'fecha_pedido' => fake()->dateTimeBetween('-1 year', 'now'), // Fecha pasada
            'total' => fake()->randomFloat(2, 10, 500), // Un total aleatorio para el estado completado
        ]);
    }

    /**
     * Indica que el pedido está procesando.
     */
    public function procesando(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Pedidos::STATUS_PROCESANDO,
            'fecha_pedido' => now(), // Fecha más reciente
             // Total podría ser null o ya calculado
            'total' => fake()->optional(0.5)->randomFloat(2, 10, 500),
        ]);
    }
}
