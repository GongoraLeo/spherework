<?php
// filepath: app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

// Importa la clase Form Request para validar la actualización del perfil.
use App\Http\Requests\ProfileUpdateRequest;
// Para especificar el tipo de retorno de redirecciones.
use Illuminate\Http\RedirectResponse;
// Objeto para manejar la solicitud HTTP entrante.
use Illuminate\Http\Request;
// Fachada para acceder a los servicios de autenticación (obtener usuario, logout).
use Illuminate\Support\Facades\Auth;
// Fachada para generar redirecciones de forma conveniente.
use Illuminate\Support\Facades\Redirect;
// Para especificar el tipo de retorno de vistas.
use Illuminate\View\View;
// Modelo Pedidos, necesario para cargar los pedidos del usuario en show().
use App\Models\Pedidos;
// Modelo Comentarios, necesario para cargar los comentarios del usuario en show().
use App\Models\Comentarios;
// Modelo User (implícitamente usado a través de Auth y relaciones).
use App\Models\User;

/**
 * Class ProfileController
 *
 * Gestiona el perfil del usuario autenticado. Permite visualizar el perfil
 * (con un resumen de actividad reciente como pedidos y comentarios),
 * mostrar el formulario de edición, actualizar la información del perfil
 * y eliminar la cuenta del usuario.
 * Basado en el controlador generado por Laravel Breeze, con personalizaciones.
 *
 * @package App\Http\Controllers
 */
class ProfileController extends Controller
{

    /**
     * Muestra el panel del perfil del usuario autenticado.
     *
     * Obtiene el usuario autenticado y carga sus pedidos recientes (excluyendo los pendientes)
     * y sus comentarios recientes (incluyendo el libro asociado a cada comentario).
     * Pasa estos datos a la vista 'profile.show' para su visualización.
     *
     * @param Request $request La solicitud HTTP entrante.
     * @return View Retorna la vista 'profile.show' con los datos del usuario, pedidos y comentarios.
     */
    public function show(Request $request): View
    {
        // 1. Obtener Usuario Autenticado:
        // `$request->user()` devuelve la instancia del modelo User autenticado.
        $user = $request->user();

        // 2. Cargar Pedidos Recientes (No Pendientes):
        // Se accede a la relación 'pedidos' definida en el modelo User.
        $pedidos = $user->pedidos()
                        // Filtra los pedidos para incluir solo aquellos con estados que indican una compra realizada o en proceso.
                        // Se excluye STATUS_PENDIENTE. Las constantes vienen del modelo Pedidos.
                        ->whereIn('status', [
                            Pedidos::STATUS_PROCESANDO,
                            Pedidos::STATUS_COMPLETADO,
                            Pedidos::STATUS_ENVIADO,
                            Pedidos::STATUS_ENTREGADO,
                            // Pedidos::STATUS_CANCELADO, // Opcionalmente se podría incluir.
                         ])
                         // Ordena los pedidos por 'fecha_pedido' descendente (más recientes primero).
                         ->latest('fecha_pedido')
                         // Limita la consulta a los 5 pedidos más recientes que cumplen las condiciones.
                         ->take(5)
                         // Ejecuta la consulta y obtiene los resultados como una colección.
                         ->get();

        // 3. Cargar Comentarios Recientes (con Libro):
        // Se accede a la relación 'comentarios' definida en el modelo User.
        $comentarios = $user->comentarios()
                            // Utiliza Eager Loading para cargar la relación 'libro' de cada comentario.
                            // Esto evita consultas N+1 si la vista necesita mostrar el título del libro.
                            ->with('libro')
                            // Ordena los comentarios por 'created_at' descendente (más recientes primero).
                            ->latest()
                            // Limita la consulta a los 10 comentarios más recientes.
                            ->take(10)
                            // Ejecuta la consulta y obtiene los resultados.
                            ->get();

        // 4. Retornar la Vista del Perfil:
        // Renderiza 'resources/views/profile/show.blade.php'.
        // Pasa el usuario (`$user`), la colección de pedidos (`$pedidos`) y la colección
        // de comentarios (`$comentarios`) a la vista.
        return view('profile.show', [
            'user' => $user,
            'pedidos' => $pedidos,
            'comentarios' => $comentarios,
        ]);
    }

    /**
     * Muestra el formulario para editar la información del perfil del usuario.
     *
     * Simplemente obtiene el usuario autenticado y lo pasa a la vista
     * 'profile.edit', que contiene los campos del formulario.
     *
     * @param Request $request La solicitud HTTP entrante.
     * @return View Retorna la vista 'profile.edit' con los datos del usuario.
     */
    public function edit(Request $request): View
    {
        // Renderiza 'resources/views/profile/edit.blade.php'.
        // Pasa la instancia del usuario autenticado (`$request->user()`) a la vista.
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Actualiza la información del perfil del usuario autenticado.
     *
     * Utiliza `ProfileUpdateRequest` (un Form Request) para validar automáticamente
     * los datos de la solicitud antes de que se ejecute este método.
     * Rellena el modelo del usuario con los datos validados.
     * Si el email ha cambiado, marca el email como no verificado.
     * Guarda los cambios en la base de datos.
     * Redirige de vuelta al formulario de edición con un mensaje de estado.
     *
     * @param ProfileUpdateRequest $request La solicitud HTTP validada.
     * @return RedirectResponse Redirige a la ruta 'profile.edit'.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // 1. Rellenar Modelo con Datos Validados:
        // `$request->validated()` devuelve un array con los datos que pasaron la validación
        // definida en `app/Http/Requests/ProfileUpdateRequest.php`.
        // `fill()` actualiza los atributos del modelo `$request->user()` con estos datos.
        $request->user()->fill($request->validated());

        // 2. Manejo de Verificación de Email:
        // `isDirty('email')` comprueba si el atributo 'email' ha sido modificado
        // antes de guardar (`save()`).
        if ($request->user()->isDirty('email')) {
            // Si el email cambió, se establece `email_verified_at` a null,
            // requiriendo que el usuario vuelva a verificar su nuevo email.
            $request->user()->email_verified_at = null;
        }

        // 3. Guardar Cambios:
        // Persiste los cambios realizados en el modelo del usuario en la base de datos.
        $request->user()->save();

        // 4. Redirección con Mensaje de Estado:
        // Redirige a la ruta nombrada 'profile.edit'.
        // `with('status', 'profile-updated')` añade un mensaje flash a la sesión,
        // que la vista 'profile.edit' puede usar para mostrar una confirmación al usuario.
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Elimina la cuenta del usuario autenticado.
     *
     * Valida que la contraseña proporcionada por el usuario coincida con la actual.
     * Si la validación es correcta, desloguea al usuario, elimina su registro
     * de la base de datos, invalida la sesión y regenera el token de sesión
     * por seguridad. Finalmente, redirige al usuario a la página principal.
     *
     * @param Request $request La solicitud HTTP entrante (que contiene la contraseña).
     * @return RedirectResponse Redirige a la ruta raíz ('/').
     */
    public function destroy(Request $request): RedirectResponse
    {
        // 1. Validación de Contraseña:
        // `validateWithBag('userDeletion', ...)` realiza la validación, pero si falla,
        // los errores se almacenan en un ErrorBag llamado 'userDeletion'. Esto es útil
        // si el formulario de eliminación está en una modal o sección separada en la vista.
        $request->validateWithBag('userDeletion', [
            // La contraseña es obligatoria y debe coincidir con la contraseña actual del usuario ('current_password').
            'password' => ['required', 'current_password'],
        ]);

        // 2. Obtener Usuario:
        $user = $request->user(); // Obtiene la instancia del usuario a eliminar.

        // 3. Desloguear Usuario:
        // Es importante desloguear antes de eliminar para evitar problemas de sesión.
        Auth::logout();

        // 4. Eliminar Usuario:
        // Elimina el registro del usuario de la base de datos.
        $user->delete();

        // 5. Invalidar Sesión y Regenerar Token:
        // `invalidate()` elimina los datos de la sesión actual.
        $request->session()->invalidate();
        // `regenerateToken()` crea un nuevo token CSRF para prevenir ataques.
        $request->session()->regenerateToken();

        // 6. Redirección Final:
        // Redirige al usuario a la página principal de la aplicación ('/').
        return Redirect::to('/');
    }
} // Fin de la clase ProfileController
