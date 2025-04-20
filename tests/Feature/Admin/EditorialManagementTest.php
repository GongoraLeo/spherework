<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Editoriales;
use App\Models\Libros; // Para probar restricción de borrado

/**
 * @group admin
 */

class EditorialManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->client = User::factory()->create(['rol' => 'cliente']);
    }

    /** @test */
    public function admin_can_view_editorial_list()
    {
        Editoriales::factory()->count(3)->create();
        $response = $this->actingAs($this->admin)->get(route('admin.editoriales.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.editoriales.index');
        $response->assertViewHas('editoriales');
        $response->assertSee(Editoriales::first()->nombre);
    }

    /** @test */
    public function client_cannot_view_editorial_list()
    {
        $response = $this->actingAs($this->client)->get(route('admin.editoriales.index'));
        $response->assertRedirect(route('profile.entry')); // Según EditorialesController@index
        $response->assertSessionHas('error', 'Acceso no autorizado.');
    }

    /** @test */
    public function admin_can_view_create_editorial_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.editoriales.create'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.editoriales.create');
    }

    /** @test */
    public function admin_can_store_new_editorial()
    {
        $editorialData = ['nombre' => 'Editorial Nueva Test', 'pais' => 'País Editorial'];
        $response = $this->actingAs($this->admin)->post(route('admin.editoriales.store'), $editorialData);

        $response->assertRedirect(route('admin.editoriales.index'));
        $response->assertSessionHas('success', 'Editorial creada correctamente.');
        $this->assertDatabaseHas('editoriales', $editorialData);
    }

    /** @test */
    public function store_editorial_fails_with_invalid_data()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.editoriales.store'), ['nombre' => '']);
        $response->assertSessionHasErrors(['nombre', 'pais']);
    }

     /** @test */
    public function store_editorial_fails_with_duplicate_name()
    {
        $existing = Editoriales::factory()->create(['nombre' => 'Editorial Duplicada']);
        $response = $this->actingAs($this->admin)->post(route('admin.editoriales.store'), [
            'nombre' => 'Editorial Duplicada',
            'pais' => 'Otro Pais'
        ]);
        $response->assertSessionHasErrors('nombre');
    }


    /** @test */
    public function admin_can_view_show_editorial_page()
    {
        $editorial = Editoriales::factory()->create();
        $response = $this->actingAs($this->admin)->get(route('admin.editoriales.show', $editorial));
        $response->assertStatus(200);
        $response->assertViewIs('admin.editoriales.show');
        $response->assertViewHas('editoriales', $editorial);
        $response->assertSee($editorial->nombre);
    }

    /** @test */
    public function admin_can_view_edit_editorial_form()
    {
        $editorial = Editoriales::factory()->create();
        $response = $this->actingAs($this->admin)->get(route('admin.editoriales.edit', $editorial));
        $response->assertStatus(200);
        $response->assertViewIs('admin.editoriales.edit');
        $response->assertViewHas('editoriales', $editorial);
        $response->assertSee($editorial->nombre);
    }

    /** @test */
    public function admin_can_update_editorial()
    {
        $editorial = Editoriales::factory()->create();
        $updateData = ['nombre' => 'Nombre Actualizado', 'pais' => 'Pais Actualizado'];
        $response = $this->actingAs($this->admin)->put(route('admin.editoriales.update', $editorial), $updateData);

        $response->assertRedirect(route('admin.editoriales.index'));
        $response->assertSessionHas('success', 'Editorial actualizada correctamente.');
        $this->assertDatabaseHas('editoriales', ['id' => $editorial->id] + $updateData);
    }

    /** @test */
    public function update_editorial_fails_with_duplicate_name_ignoring_self()
    {
        $editorial1 = Editoriales::factory()->create(['nombre' => 'Nombre Uno']);
        $editorial2 = Editoriales::factory()->create(['nombre' => 'Nombre Dos']);

        // Intentar cambiar editorial2 para que tenga el nombre de editorial1 (debe fallar)
        $response = $this->actingAs($this->admin)->put(route('admin.editoriales.update', $editorial2), [
            'nombre' => 'Nombre Uno',
            'pais' => $editorial2->pais,
        ]);
        $response->assertSessionHasErrors('nombre');

        // Intentar guardar editorial2 con su propio nombre (debe funcionar)
         $response = $this->actingAs($this->admin)->put(route('admin.editoriales.update', $editorial2), [
            'nombre' => 'Nombre Dos',
            'pais' => 'Pais Nuevo',
        ]);
        $response->assertSessionDoesntHaveErrors('nombre');
        $response->assertRedirect(route('admin.editoriales.index'));
    }


    /** @test */
    public function admin_can_delete_editorial_without_books()
    {
        $editorial = Editoriales::factory()->create();
        $response = $this->actingAs($this->admin)->delete(route('admin.editoriales.destroy', $editorial));

        $response->assertRedirect(route('admin.editoriales.index'));
        $response->assertSessionHas('success', 'Editorial eliminada correctamente.');
        $this->assertDatabaseMissing('editoriales', ['id' => $editorial->id]);
    }

    /** @test */
    public function admin_cannot_delete_editorial_with_books()
    {
        $editorial = Editoriales::factory()->create();
        Libros::factory()->create(['editorial_id' => $editorial->id]); // Libro asociado

        $response = $this->actingAs($this->admin)->delete(route('admin.editoriales.destroy', $editorial));

        $response->assertRedirect(route('admin.editoriales.index'));
        $response->assertSessionHas('error', 'No se puede eliminar la editorial porque tiene libros asociados.');
        $this->assertDatabaseHas('editoriales', ['id' => $editorial->id]); // No se borró
    }
}
