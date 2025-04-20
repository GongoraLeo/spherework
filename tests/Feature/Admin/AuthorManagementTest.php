<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Autores;
use App\Models\Libros; // Para probar restricción de borrado

/**
 * @group admin
 */

class AuthorManagementTest extends TestCase
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
    public function admin_can_view_author_list()
    {
        Autores::factory()->count(3)->create();
        $response = $this->actingAs($this->admin)->get(route('admin.autores.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.autores.index');
        $response->assertViewHas('autores');
        $response->assertSee(Autores::first()->nombre);
    }

    /** @test */
    public function client_cannot_view_author_list()
    {
        $response = $this->actingAs($this->client)->get(route('admin.autores.index'));
        $response->assertRedirect(route('profile.entry')); // Según AutoresController@index
        $response->assertSessionHas('error', 'Acceso no autorizado.');
    }

    /** @test */
    public function admin_can_view_create_author_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.autores.create'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.autores.create');
    }

    /** @test */
    public function admin_can_store_new_author()
    {
        $authorData = ['nombre' => 'Autor Nuevo Test', 'pais' => 'País Inventado'];
        $response = $this->actingAs($this->admin)->post(route('admin.autores.store'), $authorData);

        $response->assertRedirect(route('admin.autores.index'));
        $response->assertSessionHas('success', 'Autor creado correctamente.');
        $this->assertDatabaseHas('autores', $authorData);
    }

    /** @test */
    public function store_author_fails_with_invalid_data()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.autores.store'), ['nombre' => '']);
        $response->assertSessionHasErrors(['nombre', 'pais']);
    }

    /** @test */
    public function store_author_fails_with_duplicate_name()
    {
        $existing = Autores::factory()->create(['nombre' => 'Autor Duplicado']);
        $response = $this->actingAs($this->admin)->post(route('admin.autores.store'), [
            'nombre' => 'Autor Duplicado',
            'pais' => 'Otro Pais'
        ]);
        $response->assertSessionHasErrors('nombre');
    }

    /** @test */
    public function admin_can_view_show_author_page()
    {
        $autor = Autores::factory()->create();
        $response = $this->actingAs($this->admin)->get(route('admin.autores.show', $autor));
        $response->assertStatus(200);
        $response->assertViewIs('admin.autores.show');
        $response->assertViewHas('autores', $autor);
        $response->assertSee($autor->nombre);
    }

    /** @test */
    public function admin_can_view_edit_author_form()
    {
        $autor = Autores::factory()->create();
        $response = $this->actingAs($this->admin)->get(route('admin.autores.edit', $autor));
        $response->assertStatus(200);
        $response->assertViewIs('admin.autores.edit');
        $response->assertViewHas('autores', $autor);
        $response->assertSee($autor->nombre);
    }

    /** @test */
    public function admin_can_update_author()
    {
        $autor = Autores::factory()->create();
        $updateData = ['nombre' => 'Nombre Actualizado', 'pais' => 'Pais Actualizado'];
        $response = $this->actingAs($this->admin)->put(route('admin.autores.update', $autor), $updateData);

        $response->assertRedirect(route('admin.autores.index'));
        $response->assertSessionHas('success', 'Autor actualizado correctamente.');
        $this->assertDatabaseHas('autores', ['id' => $autor->id] + $updateData);
    }

    /** @test */
    public function update_author_fails_with_duplicate_name_ignoring_self()
    {
        $autor1 = Autores::factory()->create(['nombre' => 'Nombre Uno']);
        $autor2 = Autores::factory()->create(['nombre' => 'Nombre Dos']);

        // Intentar cambiar autor2 para que tenga el nombre de autor1 (debe fallar)
        $response = $this->actingAs($this->admin)->put(route('admin.autores.update', $autor2), [
            'nombre' => 'Nombre Uno',
            'pais' => $autor2->pais,
        ]);
        $response->assertSessionHasErrors('nombre');

        // Intentar guardar autor2 con su propio nombre (debe funcionar)
         $response = $this->actingAs($this->admin)->put(route('admin.autores.update', $autor2), [
            'nombre' => 'Nombre Dos',
            'pais' => 'Pais Nuevo',
        ]);
        $response->assertSessionDoesntHaveErrors('nombre');
        $response->assertRedirect(route('admin.autores.index'));
    }

    /** @test */
    public function admin_can_delete_author_without_books()
    {
        $autor = Autores::factory()->create();
        $response = $this->actingAs($this->admin)->delete(route('admin.autores.destroy', $autor));

        $response->assertRedirect(route('admin.autores.index'));
        $response->assertSessionHas('success', 'Autor eliminado correctamente.');
        $this->assertDatabaseMissing('autores', ['id' => $autor->id]);
    }

    /** @test */
    public function admin_cannot_delete_author_with_books()
    {
        $autor = Autores::factory()->create();
        Libros::factory()->create(['autor_id' => $autor->id]); // Libro asociado

        $response = $this->actingAs($this->admin)->delete(route('admin.autores.destroy', $autor));

        $response->assertRedirect(route('admin.autores.index'));
        $response->assertSessionHas('error', 'No se puede eliminar el autor porque tiene libros asociados.');
        $this->assertDatabaseHas('autores', ['id' => $autor->id]); // No se borró
    }
}
