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
*   Proceso de checkout para realizar pedidos (sin pasarela de pago implementada).
*   Visualización del historial de pedidos propios.
*   (Potencialmente) Añadir comentarios y valoraciones a los libros (no implementado).

**Para Administradores (Rol: `administrador`):**
*   Acceso a un panel de administración (`/admin/dashboard`) con estadísticas clave (libros más vendidos, clientes recientes, totales).
*   Gestión completa (CRUD) de Libros.
*   Gestión completa (CRUD) de Autores.
*   Gestión completa (CRUD) de Editoriales.
*   Visualización de la lista de todos los Clientes registrados y sus detalles.
*   Visualización de todos los Pedidos realizados en la plataforma.
*   Opción para Eliminar (Cancelar) pedidos.
*   (Potencialmente) Moderación de comentarios (no implementado).

## 💻 Tecnologías Utilizadas

*   **Backend:** PHP 8+, Laravel Framework
*   **Frontend:** HTML, Tailwind CSS, JavaScript, Vite
*   **Base de Datos:** MySQL (o compatible con Laravel Eloquent)
*   **Autenticación:** Laravel Breeze
*   **Gestión de Dependencias:** Composer (PHP), npm (JS)
*   **Testing:** PHPUnit (Pruebas Unitarias y de Integración)
*   **Documentación:** phpDocumentor (Documentación de Código Fuente)
*   **Servidor Local:** XAMPP (Apache, MySQL, PHP)
*   **Despliegue:** Railway, Docker

## 🚀 Guía de Instalación y Configuración Local (Usando XAMPP)

Sigue estos pasos para poner en marcha el proyecto Spherework en tu entorno local utilizando XAMPP. Es **obligatorio** seguir estos pasos para poder evaluar la aplicación correctamente.

**1. Prerrequisitos:**
    *   **XAMPP:** Debes tener XAMPP instalado (disponible en https://www.apachefriends.org/). Asegúrate de que los servicios **Apache** y **MySQL** estén iniciados desde el panel de control de XAMPP.
    *   **Composer:** Necesitas Composer, el gestor de dependencias de PHP, instalado globalmente en tu sistema (instrucciones en https://getcomposer.org/). Puedes verificarlo abriendo una terminal y ejecutando `composer --version`.
    *   **Node.js y npm:** Necesitas Node.js (que incluye npm, el gestor de paquetes de Node) instalado (disponible en https://nodejs.org/). Puedes verificarlo ejecutando `node -v` y `npm -v` en la terminal.
    *   **Git (Opcional pero recomendado):** Git es útil para clonar el repositorio fácilmente. Si no lo tienes, puedes descargar el código fuente como un archivo ZIP desde GitHub.

**2. Clonar u Obtener el Repositorio:**
    *   Abre una terminal (como CMD, PowerShell o Git Bash en Windows).
    *   Navega hasta la carpeta `htdocs` dentro de tu directorio de instalación de XAMPP. Por ejemplo:
        ```bash
        cd C:\xampp\htdocs
        ```
    *   Clona el repositorio del proyecto desde GitHub:
        ```bash
        git clone https://github.com/GongoraLeo/spherework spherework
        ```
        *(Si descargaste un ZIP, descomprímelo dentro de `htdocs` y asegúrate de que la carpeta resultante se llame `spherework`)*.
    *   Entra en la carpeta del proyecto que acabas de clonar/descomprimir:
        ```bash
        cd spherework
        ```

**3. Instalar Dependencias:**
    *   Instala las dependencias de PHP (Laravel y otras librerías):
        ```bash
        composer install --ignore-platform-reqs
        ```
        *(El flag `--ignore-platform-reqs` puede ser útil si hay pequeñas diferencias de versión de PHP, pero idealmente tu PHP de XAMPP debería ser compatible)*.
    *   Instala las dependencias de JavaScript (Tailwind, etc.):
        ```bash
        npm install
        ```

**4. Configuración del Entorno:**
    *   Laravel utiliza un archivo `.env` para la configuración específica del entorno. Copia el archivo de ejemplo:
        *   En Windows:
            ```bash
            copy .env.example .env
            ```
        *   En Linux/Mac:
            ```bash
            cp .env.example .env
            ```
    *   Genera la clave única de la aplicación necesaria para Laravel:
        ```bash
        php artisan key:generate
        ```

**5. Configuración de la Base de Datos:**
    *   Abre **phpMyAdmin** desde el panel de control de XAMPP o accediendo a `http://localhost/phpmyadmin` en tu navegador.
    *   Crea una nueva base de datos. El nombre recomendado es `spherework`. Asegúrate de que el cotejamiento (collation) sea `utf8mb4_unicode_ci`.
    *   Ahora, edita el archivo `.env` que creaste en el paso anterior (puedes usar un editor de texto como VS Code, Sublime Text, Notepad++). Busca las siguientes líneas y configúralas para que coincidan con tu configuración de MySQL en XAMPP (los valores por defecto de XAMPP suelen ser los mostrados):
        ```dotenv
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=spherework  # Asegúrate que es el nombre de la BD que creaste
        DB_USERNAME=root        # Usuario por defecto de MySQL en XAMPP
        DB_PASSWORD=            # Contraseña por defecto en XAMPP (vacía)

        # Asegúrate también de que estas variables estén así para desarrollo local:
        APP_ENV=local
        APP_DEBUG=true
        APP_URL=http://localhost:8000 # O http://127.0.0.1:8000
        ```
    *   Guarda los cambios en el archivo `.env`.

**6. Migraciones y Seeders (Crear Tablas y Datos Iniciales):**
    *   Vuelve a tu terminal, asegurándote de estar en la carpeta del proyecto (`C:\xampp\htdocs\spherework`).
    *   Ejecuta las migraciones para crear todas las tablas en la base de datos `spherework`:
        ```bash
        php artisan migrate
        ```
    *   Ejecuta los *seeders*. Estos poblarán la base de datos con datos iniciales, incluyendo categorías, autores, libros de ejemplo y los usuarios de prueba (admin y cliente):
        ```bash
        php artisan db:seed
        ```

**7. Compilar Assets Frontend:**
    *   Para compilar los archivos CSS (Tailwind) y JavaScript necesarios para la interfaz:
        ```bash
        npm run dev
        ```
    *   Este comando iniciará un proceso de Vite que vigilará los cambios en los archivos fuente (CSS, JS, Blade) y los recompilará automáticamente. **Debes dejar esta terminal abierta mientras trabajas con la aplicación.** Si solo necesitas una compilación única para producción (no para desarrollo local activo), puedes usar `npm run build`.

**8. Servir la Aplicación:**
    *   Abre **una nueva terminal** (deja la de `npm run dev` ejecutándose).
    *   Navega de nuevo a la carpeta del proyecto (`cd C:\xampp\htdocs\spherework`).
    *   Inicia el servidor de desarrollo incorporado de Laravel:
        ```bash
        php artisan serve
        ```
    *   Este comando te indicará la dirección en la que la aplicación está corriendo, normalmente `http://127.0.0.1:8000`.

**9. Acceder a la Aplicación:**
    *   Abre tu navegador web y ve a la dirección indicada por el comando `php artisan serve` (ej. `http://127.0.0.1:8000`).
    *   ¡Deberías ver la página de inicio de Spherework! Puedes registrar un nuevo usuario o usar los usuarios de prueba creados por los seeders (ver sección "Usuarios de Prueba").

## 🌐 Acceso a la Aplicación Desplegada (Producción)

La aplicación se encuentra desplegada y accesible públicamente en la siguiente URL:

**https://spherework-production.up.railway.app**

Puedes interactuar con la aplicación directamente en esa dirección.

*(Nota: El despliegue utiliza un plan gratuito de Railway y puede "dormir" si no recibe tráfico. Si la aplicación no carga inicialmente, espera unos segundos y vuelve a intentarlo para "despertarla").*

## 👤 Usuarios de Prueba (Producción y Local)

La base de datos se inicializa con los siguientes usuarios de prueba gracias a los *seeders*:

*   **Rol:** `administrador`
    *   **Email:** `admin@spherework.com`
    *   **Password:** `adminpassword`
*   **Rol:** `cliente`
    *   **Email:** `cliente@spherework.com`
    *   **Password:** `clientepassword`

Puedes usar estas credenciales para iniciar sesión y probar las funcionalidades de cada rol tanto en el entorno local como en la versión desplegada en Railway.

## 🧪 Testing y Documentación

*   **Pruebas Unitarias:** Se han realizado pruebas unitarias utilizando **PHPUnit** para asegurar la calidad y el correcto funcionamiento del código backend. Todas las pruebas han sido superadas satisfactoriamente.
    *   Puedes consultar el reporte de cobertura de las pruebas (si se ha generado) en la carpeta `coverage-report/index.html` (esta carpeta puede no estar versionada en Git).
*   **Documentación del Código:** Se ha generado documentación automática del código fuente utilizando **phpDocumentor**. Esta documentación detalla las clases, métodos y propiedades del proyecto.
    *   Puedes explorar la documentación de la API en la carpeta `docs/api/index.html` (esta carpeta puede no estar versionada en Git).

## ⚙️ Configuración Adicional (.env - Solo Local)

El archivo `.env` se usa principalmente para la configuración del **entorno local**. Contiene variables de entorno importantes. Además de la base de datos, revisa y ajusta si es necesario para tu configuración local:
*   `APP_NAME`: Nombre de la aplicación (ej. "Spherework").
*   `APP_URL`: URL base de tu aplicación local (ej. `http://localhost:8000`).
*   Configuraciones de correo (MAIL_...) si necesitas probar el envío de emails localmente (requiere configuración adicional como Mailtrap o similar).

*(La configuración para el entorno de producción en Railway se gestiona directamente en las variables de entorno del servicio en la plataforma Railway).*
