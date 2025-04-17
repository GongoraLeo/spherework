<?php

namespace App\Http\Controllers;

use App\Models\Editoriales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Importo Auth para la autorización
use Illuminate\View\View;             // Para type hinting
use Illuminate\Http\RedirectResponse; // Para type hinting
use Illuminate\Support\Facades\Log;    // Para logs
use Illuminate\Validation\Rule;       // Para reglas de validación avanzadas

class EditorialesController extends Controller
{
    /**
     * Muestro una lista del recurso (editoriales) para el admin.
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }

        // Obtengo editoriales paginadas
        $editoriales = Editoriales::orderBy('nombre')->paginate(15);
        // Apunto a la vista dentro de admin
        return view('admin.editoriales.index', compact('editoriales'));
    }

    /**
     * Muestro el formulario para crear un nuevo recurso (editorial).
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('admin.editoriales.index')->with('error', 'No tienes permiso para crear editoriales.');
        }
        // Apunto a la vista dentro de admin
        return view('admin.editoriales.create');
    }

    /**
     * Almaceno un nuevo recurso (editorial) creado en la base de datos.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        // Validación mejorada
        $request->validate([
            'nombre' => 'required|string|max:255|unique:editoriales,nombre', // Nombre único
            'pais'   => 'required|string|max:255'
        ]);

        try {
            Editoriales::create($request->all());
            // Redirijo a la ruta del índice de admin
            return redirect()->route('admin.editoriales.index')
                ->with('success', 'Editorial creada correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al crear editorial: " . $e->getMessage(), $request->all());
            return back()->with('error', 'Ocurrió un error al crear la editorial.')->withInput();
        }
    }

    /**
     * Muestro la editorial especificada (vista admin).
     * @param Editoriales $editoriales // Laravel inyecta la editorial basada en el ID de la ruta.
     * @return View|RedirectResponse
     */
    public function show(Editoriales $editoriales): View|RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('profile.entry')->with('error', 'Acceso no autorizado.');
        }
        // No necesito buscarla de nuevo (Route Model Binding).
        // Apunto a la vista dentro de admin, pasando la variable como 'editoriales'
        return view('admin.editoriales.show', compact('editoriales'));
    }

    /**
     * Muestro el formulario para editar la editorial especificada (vista admin).
     * @param Editoriales $editoriales // Usando el nombre plural solicitado.
     * @return View|RedirectResponse
     */
    public function edit(Editoriales $editoriales): View|RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            return redirect()->route('admin.editoriales.index')->with('error', 'No tienes permiso para editar editoriales.');
        }
        // No necesito buscarla de nuevo.
        // Apunto a la vista dentro de admin, pasando la variable como 'editoriales'
        return view('admin.editoriales.edit', compact('editoriales'));
    }

    /**
     * Actualizo la editorial especificada en la base de datos.
     * @param Request $request
     * @param Editoriales $editoriales // Usando el nombre plural solicitado.
     * @return RedirectResponse
     */
    public function update(Request $request, Editoriales $editoriales): RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        // Validación mejorada para update
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('editoriales', 'nombre')->ignore($editoriales->id), // Ignora el ID actual
            ],
            'pais'   => 'required|string|max:255'
        ]);

        try {
            // No necesito buscarla de nuevo.
            $editoriales->update($request->all()); // Usa $editoriales->update()
            // Redirijo a la ruta del índice de admin
            return redirect()->route('admin.editoriales.index')
                ->with('success', 'Editorial actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al actualizar editorial ID {$editoriales->id}: " . $e->getMessage(), $request->all());
            return back()->with('error', 'Ocurrió un error al actualizar la editorial.')->withInput();
        }
    }

    /**
     * Elimino la editorial especificada de la base de datos.
     * @param Editoriales $editoriales // Usando el nombre plural solicitado.
     * @return RedirectResponse
     */
    public function destroy(Editoriales $editoriales): RedirectResponse
    {
        // Autorización: Solo Admin
        if (Auth::user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        try {
            // No necesito buscarla de nuevo.

            // Verificar si la editorial tiene libros asociados antes de borrar.
            // Asegúrate de tener la relación 'libros' definida en el modelo Editoriales.
            if ($editoriales->libros()->count() > 0) {
                 return redirect()->route('admin.editoriales.index')
                    ->with('error', 'No se puede eliminar la editorial porque tiene libros asociados.');
            }

            $editoriales->delete(); // Usa $editoriales->delete()
            // Redirijo a la ruta del índice de admin
            return redirect()->route('admin.editoriales.index')
                ->with('success', 'Editorial eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
             // Capturar error si hay restricciones (ej. si la relación libros() falla o hay otra)
             Log::error("Error de BD al eliminar editorial ID {$editoriales->id}: " . $e->getMessage());
             return redirect()->route('admin.editoriales.index')
                ->with('error', 'No se pudo eliminar la editorial debido a restricciones de base de datos.');
        } catch (\Exception $e) {
            Log::error("Error al eliminar editorial ID {$editoriales->id}: " . $e->getMessage());
            return redirect()->route('admin.editoriales.index')
                ->with('error', 'Ocurrió un error al eliminar la editorial.');
        }
    }
}
