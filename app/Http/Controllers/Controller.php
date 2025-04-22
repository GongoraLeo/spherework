<?php
// filepath: app/Http/Controllers/Controller.php

namespace App\Http\Controllers;

// Importa el trait para manejar la autorización de solicitudes (ej. Policies).
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// Importa el trait que proporciona métodos convenientes para la validación (ej. $this->validate()).
use Illuminate\Foundation\Validation\ValidatesRequests;
// Importa la clase base de controladores de Laravel y le asigna un alias 'BaseController'.
use Illuminate\Routing\Controller as BaseController;

/**
 * Class Controller
 *
 * Clase base abstracta para todos los controladores de la aplicación.
 * Extiende el controlador base proporcionado por el framework Laravel (`Illuminate\Routing\Controller`)
 * e incluye traits comunes para proporcionar funcionalidades de autorización y validación
 * a todos los controladores que hereden de esta clase.
 *
 * Al extender esta clase, los controladores hijos tienen acceso a métodos útiles
 * como `$this->authorize()` y `$this->validate()`.
 *
 * @package App\Http\Controllers
 */
abstract class Controller extends BaseController
{
    /**
     * Utiliza el trait AuthorizesRequests.
     * Este trait proporciona el método `authorize` que permite verificar si el usuario
     * autenticado tiene permiso para realizar una acción determinada, generalmente
     * interactuando con las Policies de la aplicación. Se incluye aquí para que todos
     * los controladores hijos puedan usar fácilmente la autorización basada en Policies.
     */
    use AuthorizesRequests;

    /**
     * Utiliza el trait ValidatesRequests.
     * Este trait proporciona el método `validate` (y otros relacionados) que simplifica
     * la validación de los datos de las solicitudes HTTP entrantes utilizando las
     * reglas de validación de Laravel. Si la validación falla, automáticamente
     * lanza una excepción y redirige al usuario de vuelta con los errores. Se incluye
     * para ofrecer una forma estandarizada y conveniente de validación en todos los controladores.
     */
    use ValidatesRequests;
}
