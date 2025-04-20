<?php

// tests/Unit/Models/UserModelTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pedidos;
use App\Models\Comentarios;
use Illuminate\Foundation\Testing\RefreshDatabase; // Útil incluso en unit si quieres usar factories fácil

class UserModelTest extends TestCase
{
    use RefreshDatabase; // Opcional, pero facilita crear modelos relacionados

    /** @test */
    public function user_has_many_pedidos()
    {
        $user = User::factory()->create();
        Pedidos::factory()->count(3)->create(['cliente_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->pedidos);
        $this->assertCount(3, $user->pedidos);
        $this->assertInstanceOf(Pedidos::class, $user->pedidos->first());
    }

    /** @test */
    public function user_has_many_comentarios()
    {
        $user = User::factory()->create();
        // Necesitas LibroFactory para crear comentarios
        $libro = \App\Models\Libros::factory()->create();
        Comentarios::factory()->count(2)->create([
            'user_id' => $user->id,
            'libro_id' => $libro->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->comentarios);
        $this->assertCount(2, $user->comentarios);
        $this->assertInstanceOf(Comentarios::class, $user->comentarios->first());
    }

    /** @test */
    public function user_rol_is_cast_to_string()
    {
        $user = User::factory()->create(['rol' => 'administrador']);
        $this->assertIsString($user->rol);
    }

    /** @test */
    public function user_password_is_hidden()
    {
        $user = User::factory()->create();
        $this->assertArrayNotHasKey('password', $user->toArray());
    }

     /** @test */
    public function user_password_is_hashed()
    {
         // No podemos probar directamente el cast 'hashed', pero sí que no es el string original
         $user = User::factory()->create(['password' => 'plain-password']);
         $this->assertNotEquals('plain-password', $user->password);
         $this->assertTrue(\Illuminate\Support\Facades\Hash::check('plain-password', $user->password));
    }
}