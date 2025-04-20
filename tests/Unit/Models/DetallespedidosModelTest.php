<?php

// tests/Unit/Models/DetallespedidosModelTest.php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Detallespedidos;
use App\Models\Pedidos;
use App\Models\Libros;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DetallespedidosModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function detallepedido_belongs_to_pedido()
    {
        $pedido = Pedidos::factory()->create();
        $detalle = Detallespedidos::factory()->create(['pedido_id' => $pedido->id]);

        $this->assertInstanceOf(Pedidos::class, $detalle->pedido);
        $this->assertEquals($pedido->id, $detalle->pedido->id);
    }

    /** @test */
    public function detallepedido_belongs_to_libro()
    {
        $libro = Libros::factory()->create();
        $detalle = Detallespedidos::factory()->create(['libro_id' => $libro->id]);

        $this->assertInstanceOf(Libros::class, $detalle->libro);
        $this->assertEquals($libro->id, $detalle->libro->id);
    }

    /** @test */
    public function detallepedido_fillable_attributes_are_correct()
    {
        $fillable = (new Detallespedidos())->getFillable();
        $expected = ['pedido_id', 'libro_id', 'cantidad', 'precio'];
        $this->assertEquals($expected, $fillable);
    }

    /** @test */
    public function detallepedido_uses_hasfactory_trait()
    {
        $uses = class_uses(Detallespedidos::class);
        $this->assertArrayHasKey(\Illuminate\Database\Eloquent\Factories\HasFactory::class, $uses);
    }
}
