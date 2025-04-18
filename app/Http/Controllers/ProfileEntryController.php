<?php
// filepath: app/Http/Controllers/ProfileEntryController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Objeto para manejar la solicitud HTTP entrante (no se usa directamente aquí).
use Illuminate\Support\Facades\Auth; // Fachada para verificar la autenticación y obtener el usuario logueado.
use Illuminate\Http\RedirectResponse; // Para especificar el tipo de retorno de redirecciones.

/**
 * Class ProfileEntryController
 *
 * Controlador de Acción Única (Invokable Controller) que actúa como punto de entrada
 * centralizado después del login o al acceder a una ruta de perfil genérica.
 * Su única responsabilidad es determinar el rol del usuario autenticado y redirigirlo
 * a la vista de perfil o dashboard correspondiente (administrador o cliente).
 *
 * @package App\Http\Controllers
 */
class ProfileEntryController extends Controller
{
    /**
     * Maneja la solicitud entrante para este controlador de acción única.
     *
     * Este método se ejecuta automáticamente cuando se invoca la ruta asociada
     * a este controlador. Verifica si el usuario está autenticado. Si no lo está,
     * lo redirige al login. Si está autenticado, obtiene el usuario, comprueba
     * su rol ('administrador' o 'cliente') y lo redirige a la ruta del dashboard
     * de administrador ('admin.dashboard') o al perfil estándar del cliente
     * ('profile.show') respectivamente.
     *
     * @param  \Illuminate\Http\Request  $request La solicitud HTTP entrante.
     * @return \Illuminate\Http\RedirectResponse Siempre retorna una redirección a la ruta apropiada.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // 1. Verificación de Autenticación:
        // Comprueba si hay un usuario autenticado en la sesión actual.
        // Es una medida de seguridad por si se accede a esta ruta directamente sin estar logueado.
        if (!Auth::check()) {
            // Si no hay usuario autenticado, redirige a la página de login.
            return redirect()->route('login');
        }

        // 2. Obtener Usuario Autenticado:
        // Si la verificación anterior pasa, obtiene la instancia del modelo User autenticado.
        $user = Auth::user();

        // 3. Redirección Basada en Rol:
        // Comprueba el valor del atributo 'rol' del usuario.
        if ($user->rol === 'administrador') {
            // Si el rol es 'administrador', redirige a la ruta nombrada 'admin.dashboard'.
            return redirect()->route('admin.dashboard');
        } else {
            // Si el rol no es 'administrador' (asumiendo que es 'cliente' u otro rol estándar),
            // redirige a la ruta nombrada 'profile.show', que muestra el perfil del cliente.
            return redirect()->route('profile.show');
        }
    }
}
