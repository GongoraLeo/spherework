<?php
// filepath: app/Http/Controllers/ProfileEntryController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class ProfileEntryController extends Controller
{
    /**
     * Handle the incoming request.
     * Redirige al dashboard apropiado según el rol del usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login'); // Si por alguna razón llega aquí sin loguear
        }

        $user = Auth::user();

        if ($user->rol === 'administrador') {
            return redirect()->route('admin.dashboard');
        } else {
            // Para 'cliente' u otros roles, ir al perfil estándar
            return redirect()->route('profile.show');
        }
    }
}
