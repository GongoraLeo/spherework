<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Libros; // Importa el modelo
use App\Models\Autores; // Necesario para la relación
use App\Models\Editoriales; // Necesario para la relación

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Libros>
 */
class LibrosFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente de la factory.
     *
     * @var string
     */
    protected $model = Libros::class; // Especifica el modelo

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4, true), // Genera una frase corta como título
            'isbn' => fake()->unique()->isbn13(), // Genera un ISBN-13 único
            'anio_publicacion' => fake()->numberBetween(1950, date('Y')), // Año entre 1950 y el actual
            'precio' => fake()->randomFloat(2, 5, 150), // Precio decimal entre 5 y 150
            // Asigna automáticamente un autor y editorial existentes o crea uno nuevo si no existe
            'autor_id' => Autores::factory(),
            'editorial_id' => Editoriales::factory(),
        ];
    }
}
