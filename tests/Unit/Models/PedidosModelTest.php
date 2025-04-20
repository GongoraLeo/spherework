<?php

// tests/Unit/Models/PedidosModelTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Pedidos;
use App\Models\User;
use App\Models\Detallespedidos;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PedidoModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pedido_belongs_to_cliente() // Cliente es User
    {
        $user = User::factory()->create();
        $pedido = Pedidos::factory()->create(['cliente_id' => $user->id]);

        $this->assertInstanceOf(User::class, $pedido->cliente);
        $this->assertEquals($user->id, $pedido->cliente->id);
    }

    /** @test */
    public function pedido_has_many_detallespedidos()
    {
        $pedido = Pedidos::factory()->create();
        // Necesitas LibroFactory y DetallepedidoFactory
        $libro = \App\Models\Libros::factory()->create();
        Detallespedidos::factory()->count(2)->create([
            'pedido_id' => $pedido->id,
            'libro_id' => $libro->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $pedido->detallespedidos);
        $this->assertCount(2, $pedido->detallespedidos);
        $this->assertInstanceOf(Detallespedidos::class, $pedido->detallespedidos->first());
    }

    /** @test */
    public function pedido_fecha_pedido_is_cast_to_datetime()
    {
        $pedido = Pedidos::factory()->create(['fecha_pedido' => now()]);
        // Refresh from DB to ensure cast is applied
        $pedido = $pedido->fresh();
        $this->assertInstanceOf(\Carbon\Carbon::class, $pedido->fecha_pedido);
    }

    /** @test */
    public function pedido_status_constants_exist()
    {
        $this->assertEquals('pendiente', Pedidos::STATUS_PENDIENTE);
        $this->assertEquals('completado', Pedidos::STATUS_COMPLETADO);
        // ... assert other constants ...
    }
}

