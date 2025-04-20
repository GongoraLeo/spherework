<?php

namespace Tests\Feature\Comments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Libros;
use App\Models\Comentarios;
use PHPUnit\Framework\Attributes\Test;

class CommentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private User $admin;
    private Libros $libro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->admin = User::factory()->create(['rol' => 'administrador']);
        $this->libro = Libros::factory()->create();
    }

    #[Test]
    public function authenticated_user_can_post_a_comment(): void
    {
        $commentData = [
            'libro_id' => $this->libro->id,
            'texto' => 'Este es un comentario de prueba.',
            'puntuacion' => 4,
        ];

        $response = $this->actingAs($this->user)->post(route('comentarios.store'), $commentData);

        $response->assertRedirect(route('libros.show', $this->libro->id));
        $response->assertSessionHas('success', 'Comentario añadido correctamente.');
        $this->assertDatabaseHas('comentarios', [
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
            'comentario' => 'Este es un comentario de prueba.',
            'puntuacion' => 4,
        ]);
    }

    #[Test]
    public function guest_cannot_post_a_comment(): void
    {
        $commentData = [
            'libro_id' => $this->libro->id,
            'texto' => 'Comentario de invitado.',
            'puntuacion' => 5,
        ];

        $response = $this->post(route('comentarios.store'), $commentData);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('comentarios', 0);
    }

    #[Test]
    public function comment_store_fails_with_invalid_data(): void
    {
        $response = $this->actingAs($this->user)->post(route('comentarios.store'), [
            'libro_id' => 999, // No existe
            'texto' => '', // Vacío
            'puntuacion' => 6, // Inválido
        ]);
        $response->assertSessionHasErrors(['libro_id', 'texto', 'puntuacion']);
    }

    #[Test]
    public function comment_owner_can_view_edit_comment_form(): void
    {
        // Usar texto simple para la prueba
        $commentText = 'Texto simple para prueba de edicion.';
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
            'comentario' => $commentText,
        ]);

        $response = $this->actingAs($this->user)->get(route('comentarios.edit', $comentario));

        $response->assertStatus(200);
        $response->assertViewIs('comentarios.edit');
        $response->assertViewHas('comentarios', $comentario);

        // --- CORREGIDO ---
        // Ahora que la vista usa ->comentario (correcto), esperamos el texto dentro del textarea.
        // $response->assertSee('<textarea name="texto"', false); // ANTES
        // $response->assertSee('></textarea>', false); // ANTES
        // $response->assertDontSee($commentText); // ANTES
        $response->assertSee('>'.$commentText.'</textarea>', false); // DESPUÉS (Busca HTML exacto con el texto)
        // --- FIN CORREGIDO ---
    }

    #[Test]
    public function admin_can_view_edit_comment_form_of_any_user(): void
    {
        // Usar texto simple para la prueba
        $commentText = 'Otro texto simple para admin.';
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id, // Comentario de otro usuario
            'libro_id' => $this->libro->id,
            'comentario' => $commentText,
        ]);

        $response = $this->actingAs($this->admin)->get(route('comentarios.edit', $comentario));

        $response->assertStatus(200);

        // --- CORREGIDO ---
        // Ahora que la vista usa ->comentario (correcto), esperamos el texto dentro del textarea.
        // $response->assertSee('<textarea name="texto"', false); // ANTES
        // $response->assertSee('></textarea>', false); // ANTES
        // $response->assertDontSee($commentText); // ANTES
        $response->assertSee('>'.$commentText.'</textarea>', false); // DESPUÉS (Busca HTML exacto con el texto)
        // --- FIN CORREGIDO ---
    }

    #[Test]
    public function other_user_cannot_view_edit_comment_form(): void
    {
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);

        $response = $this->actingAs($this->otherUser)->get(route('comentarios.edit', $comentario));
        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error', 'No tienes permiso para editar este comentario.');
    }

    #[Test]
    public function comment_owner_can_update_comment(): void
    {
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);
        $updateData = ['texto' => 'Comentario actualizado', 'puntuacion' => 3];

        $response = $this->actingAs($this->user)->put(route('comentarios.update', $comentario), $updateData);

        $response->assertRedirect(route('libros.show', $this->libro->id));
        $response->assertSessionHas('success', 'Comentario actualizado correctamente.');
        $this->assertDatabaseHas('comentarios', ['id' => $comentario->id, 'comentario' => 'Comentario actualizado', 'puntuacion' => 3]);
    }

    #[Test]
    public function admin_can_update_comment_of_any_user(): void
    {
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);
        $updateData = ['texto' => 'Actualizado por admin', 'puntuacion' => 1];

        $response = $this->actingAs($this->admin)->put(route('comentarios.update', $comentario), $updateData);
        $response->assertRedirect(route('libros.show', $this->libro->id));
        $this->assertDatabaseHas('comentarios', ['id' => $comentario->id, 'comentario' => 'Actualizado por admin']);
    }

    #[Test]
    public function other_user_cannot_update_comment(): void
    {
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
            'comentario' => 'Original'
        ]);
        $updateData = ['texto' => 'Intento fallido', 'puntuacion' => 2];

        $response = $this->actingAs($this->otherUser)->put(route('comentarios.update', $comentario), $updateData);
        $response->assertStatus(403);
        $this->assertDatabaseHas('comentarios', ['id' => $comentario->id, 'comentario' => 'Original']);
    }

    #[Test]
    public function comment_owner_can_delete_comment(): void
    {
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('comentarios.destroy', $comentario));

        $response->assertRedirect(route('libros.show', $this->libro->id));
        $response->assertSessionHas('success', 'Comentario eliminado correctamente.');
        $this->assertDatabaseMissing('comentarios', ['id' => $comentario->id]);
    }

     #[Test]
    public function admin_can_delete_comment_of_any_user(): void
    {
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('comentarios.destroy', $comentario));
        $response->assertRedirect(route('libros.show', $this->libro->id));
        $this->assertDatabaseMissing('comentarios', ['id' => $comentario->id]);
    }

    #[Test]
    public function other_user_cannot_delete_comment(): void
    {
        $comentario = Comentarios::factory()->create([
            'user_id' => $this->user->id,
            'libro_id' => $this->libro->id,
        ]);

        $response = $this->actingAs($this->otherUser)->delete(route('comentarios.destroy', $comentario));
        $response->assertStatus(403);
        $this->assertDatabaseHas('comentarios', ['id' => $comentario->id]);
    }
}
