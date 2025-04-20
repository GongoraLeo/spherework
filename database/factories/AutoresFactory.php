<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Autores; // Asegúrate de importar el modelo

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Autores>
 */
class AutoresFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente de la factory.
     *
     * @var string
     */
    protected $model = Autores::class; // Especifica el modelo

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(), // Genera un nombre de persona
            'pais'   => fake()->country(), // Genera un nombre de país
        ];
    }
}
