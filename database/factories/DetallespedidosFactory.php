<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Detallespedidos; // Importa el modelo
use App\Models\Pedidos; // Necesario para la relación
use App\Models\Libros; // Necesario para la relación y obtener precio

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Detallespedidos>
 */
class DetallespedidosFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente de la factory.
     *
     * @var string
     */
    protected $model = Detallespedidos::class; // Especifica el modelo

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Crear o usar un libro para obtener su precio
        $libro = Libros::factory()->create(); // Crea un libro para este detalle

        return [
            // Asigna un pedido existente o crea uno nuevo (probablemente pendiente por defecto)
            'pedido_id' => Pedidos::factory(),
            'libro_id' => $libro->id, // Usa el ID del libro creado
            'cantidad' => fake()->numberBetween(1, 5), // Cantidad aleatoria pequeña
            // Usa el precio real del libro asociado en el momento de crear el detalle
            'precio' => $libro->precio,
        ];
    }
}
