<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Editoriales; // Importa el modelo

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Editoriales>
 */
class EditorialesFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente de la factory.
     *
     * @var string
     */
    protected $model = Editoriales::class; // Especifica el modelo

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->company() . ' Editorial', // Genera un nombre de compañía + sufijo
            'pais'   => fake()->country(), // Genera un nombre de país
        ];
    }
}
