<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Pedidos;
use App\Models\Comentarios;


class ProfileController extends Controller
{

    /**
     * Display the user's profile dashboard.
     * Muestra el panel del perfil del usuario con sus datos recientes.
     */
    public function show(Request $request): View
    {
        $user = $request->user(); // Obtiene el usuario autenticado

        // Cargar Pedidos Recientes (que NO estén pendientes)
        // Usamos la relación 'pedidos' definida en el modelo User
        $pedidos = $user->pedidos()
                        // Filtramos para mostrar solo pedidos que ya no están pendientes
                        // (Ajusta los estados si tienes otros que signifiquen "compra realizada")
                        ->whereIn('status', [
                            Pedidos::STATUS_PROCESANDO,
                            Pedidos::STATUS_COMPLETADO,
                            Pedidos::STATUS_ENVIADO,
                            Pedidos::STATUS_ENTREGADO,
                            // Podrías incluir CANCELADO si quieres mostrarlo aquí
                            // Pedidos::STATUS_CANCELADO,
                         ])
                         ->latest('fecha_pedido') // Ordena por fecha de pedido más reciente
                         ->take(5) // Limita a los 5 más recientes
                         ->get();

        // Cargar Comentarios Recientes (incluyendo su libro asociado)
        // Usamos la relación 'comentarios' definida en el modelo User
        $comentarios = $user->comentarios()
                            ->with('libro') // Carga la relación 'libro' del modelo Comentarios
                            ->latest()      // Ordena por fecha de creación más reciente
                            ->take(10)      // Tomamos 10 por si algunos no tienen puntuación
                            ->get();

        // No necesitamos una consulta separada para 'valoraciones',
        // ya que 'puntuacion' está dentro de 'comentarios'.
        // La vista se encargará de mostrar la puntuación si existe en un comentario.

        // Pasamos las variables a la vista (usando nombres en plural)
        return view('profile.show', [
            'user' => $user,
            'pedidos' => $pedidos,
            'comentarios' => $comentarios,
            // Ya no pasamos $valoraciones
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
