# Spherework - Librería Online 📚

Spherework es una aplicación web desarrollada con el framework Laravel que simula una librería online. Permite a los usuarios explorar un catálogo de libros, gestionar un carrito de compras y realizar pedidos. La aplicación cuenta con roles diferenciados para clientes y administradores, ofreciendo un panel de gestión completo para estos últimos.

## ✨ Características Principales

**Para Todos los Usuarios:**
*   Navegación por el catálogo de libros.
*   Visualización de detalles de cada libro.
*   Registro e inicio de sesión de usuarios.

**Para Clientes (Rol: `cliente`):**
*   Gestión de perfil de usuario (ver y editar).
*   Añadir/actualizar/eliminar libros del carrito de compras.
*   Proceso de checkout para realizar pedidos.
*   Visualización del historial de pedidos propios.
*   (Potencialmente) Añadir comentarios y valoraciones a los libros.

**Para Administradores (Rol: `administrador`):**
*   Acceso a un panel de administración (`/admin/dashboard`) con estadísticas clave (libros más vendidos, clientes recientes, totales).
*   Gestión completa (CRUD) de Libros.
*   Gestión completa (CRUD) de Autores.
*   Gestión completa (CRUD) de Editoriales.
*   Visualización de la lista de todos los Clientes registrados y sus detalles.
*   Visualización de todos los Pedidos realizados en la plataforma.
*   Opción para Eliminar (Cancelar) pedidos.
*   (Potencialmente) Moderación de comentarios.

## 💻 Tecnologías Utilizadas

*   **Backend:** PHP 8+, Laravel Framework
*   **Frontend:** HTML, Tailwind CSS, JavaScript, Vite
*   **Base de Datos:** MySQL (o compatible con Laravel Eloquent)
*   **Autenticación:** Laravel Breeze (presumiblemente)
*   **Gestión de Dependencias:** Composer (PHP), npm (JS)
*   **Servidor Local:** XAMPP (Apache, MySQL, PHP)

## 🚀 Guía de Instalación y Configuración Local (Usando XAMPP)

Sigue estos pasos para poner en marcha el proyecto en tu entorno local:

**1. Prerrequisitos:**
    *   **XAMPP:** Instala XAMPP (https://www.apachefriends.org/). Asegúrate de que los servicios Apache y MySQL estén iniciados.
    *   **Composer:** Instala el gestor de dependencias de PHP globalmente (https://getcomposer.org/).
    *   **Node.js y npm:** Instala Node.js (incluye npm) (https://nodejs.org/).
    *   **Git (Opcional):** Para clonar el repositorio.

**2. Clonar el Repositorio:**
    *   Abre tu terminal o Git Bash.
    *   Navega a la carpeta `htdocs` de tu instalación de XAMPP (ej. `cd C:\xampp\htdocs`).
    *   Clona el proyecto:
        ```bash
        git clone <URL_DEL_REPOSITORIO_GITHUB> spherework
        ```
    *   Entra en la carpeta del proyecto:
        ```bash
        cd spherework
        ```

**3. Instalar Dependencias:**
    *   Instala las dependencias de PHP:
        ```bash
        composer install
        ```
    *   Instala las dependencias de JavaScript:
        ```bash
        npm install
        ```

**4. Configuración del Entorno:**
    *   Copia el archivo de configuración de ejemplo:
        ```bash
        copy .env.example .env
        ```
        *(En Linux/Mac: `cp .env.example .env`)*
    *   Genera la clave única de la aplicación:
        ```bash
        php artisan key:generate
        ```

**5. Configuración de la Base de Datos:**
    *   Abre **phpMyAdmin** (desde el panel de XAMPP o `http://localhost/phpmyadmin`).
    *   Crea una nueva base de datos (ej. `spherework`) con cotejamiento `utf8mb4_unicode_ci` (puedes hacerlo ejecutando el archivo create_database.sql).
    *   Edita el archivo `.env` en la raíz de tu proyecto y configura las variables de base de datos:
        ```dotenv
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=spherework  # Nombre de tu BD
        DB_USERNAME=root        # Usuario de MySQL (por defecto en XAMPP)
        DB_PASSWORD=            # Contraseña de MySQL (vacía por defecto en XAMPP)
        ```

**6. Migraciones de Base de Datos:**
    *   Ejecuta las migraciones para crear la estructura de tablas:
        ```bash
        php artisan migrate
        ```
    *   *(Opcional)* Si tienes seeders para datos de prueba:
        ```bash
        php artisan db:seed
        ```

**7. Compilar Assets:**
    *   Compila los archivos CSS y JS para el frontend:
        ```bash
        npm run dev
        ```
        *(Deja esta terminal abierta durante el desarrollo para recompilación automática, o usa `npm run build` para una compilación única para producción).*

**8. Servir la Aplicación:**
    *   Abre **otra** terminal en la carpeta del proyecto.
    *   Inicia el servidor de desarrollo de Laravel:
        ```bash
        php artisan serve
        ```
    *   Accede a la aplicación en tu navegador, normalmente en: `http://127.0.0.1:8000`

**9. Usuarios:**

    Usuarios existentes:
    Los seeders introducen dos usuarios:

    *   Rol: administrador
    *   Nombre de usuario: admin
    *   Email: admin@spherework.com
    *   Password: adminpassword
    
    *   Rol: cliente
    *   Nombre de usuario: cliente
    *   Email: cliente@spherework.com
    *   Password: clientepassword

    Crear Usuario Administrador:
    *   Ve a la URL de registro de tu aplicación (ej. `http://127.0.0.1:8000/register`) y crea un usuario normal.
    *   Abre **phpMyAdmin**, selecciona tu base de datos (`spherework`).
    *   Busca la tabla `users`.
    *   Encuentra el usuario que acabas de crear y edita su fila.
    *   Cambia el valor de la columna `rol` a `administrador`.
    *   Guarda los cambios.
    *   Ahora puedes iniciar sesión con este usuario para acceder a las funciones de administrador.

    Crear Usuario Cliente:
    *   Registra un nuevo usuario en la aplicacion.

## 🛠️ Uso

Una vez instalado y servido (`php artisan serve`), puedes acceder a la aplicación en `http://127.0.0.1:8000`.
*   Navega por el catálogo como visitante.
*   Regístrate como cliente para usar el carrito y hacer pedidos.
*   Inicia sesión con el usuario administrador creado manualmente para acceder al panel de administración y gestionar la tienda.

## ⚙️ Configuración Adicional (.env)

El archivo `.env` contiene variables de entorno importantes. Además de la base de datos, revisa y ajusta si es necesario:
*   `APP_NAME`: Nombre de la aplicación.
*   `APP_URL`: URL base de tu aplicación (importante para generar enlaces correctos).
*   Configuraciones de correo (si implementas envío de emails).



