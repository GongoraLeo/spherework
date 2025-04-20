<?php

namespace Database\Factories;
// database/factories/UserFactory.php
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User; // Importa el modelo

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente de la factory.
     *
     * @var string
     */
    protected $model = User::class; // Especifica el modelo

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
            'rol' => 'cliente', // Por defecto cliente

        ];
    }

    /**
     * Indica que el usuario es un administrador.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'rol' => 'administrador',
        ]);
    }

    /**
     * Indica que el email del usuario no está verificado.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
