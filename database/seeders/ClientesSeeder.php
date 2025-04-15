<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Clientes;
use Illuminate\Support\Facades\Hash; // Importar Hash

class ClientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Clientes::query()->delete();

        Clientes::create([
            'nombre' => 'Juan',
            'email' => 'juan.perez@email.com',
            'password' => Hash::make('password'), // Asumiendo que clientes también se loguean o necesitan pass
        ]);

        Clientes::create([
            'nombre' => 'Maria',
            'email' => 'maria.gomez@email.com',
            'password' => Hash::make('password'),
        ]);
    }
}
