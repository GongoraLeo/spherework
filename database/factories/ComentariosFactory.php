<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Comentarios; // Importa el modelo
use App\Models\User; // Necesario para la relación
use App\Models\Libros; // Necesario para la relación

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comentarios>
 */
class ComentariosFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente de la factory.
     *
     * @var string
     */
    protected $model = Comentarios::class; // Especifica el modelo

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Asigna un usuario y libro existentes o crea nuevos
            'user_id' => User::factory(),
            'libro_id' => Libros::factory(),
            'comentario' => fake()->paragraph(), // Genera un párrafo de texto
            // Puntuación aleatoria entre 1 y 5, o null a veces (ej. 20% de las veces)
            'puntuacion' => fake()->optional(0.8, null)->numberBetween(1, 5),
        ];
    }
}
