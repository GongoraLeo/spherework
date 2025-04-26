# Spherework - Tienda de libros online 📚

Spherework es una aplicación web desarrollada con el framework Laravel que simula una tienda de libros online. Permite a los usuarios explorar un catálogo de libros, gestionar un carrito de compras, realizar pedidos y ver estadísticas de su uso. La aplicación cuenta con roles diferenciados para clientes y administradores, ofreciendo un panel de gestión completo para estos últimos.

## ✨ Características principales

**Para todos los usuarios:**

-   Navegación por el catálogo de libros.
-   Visualización de detalles de cada libro.
-   Registro e inicio de sesión de usuarios.

**Para clientes (Rol: `cliente`):**

-   Gestión de perfil de usuario (ver y editar).
-   Añadir/actualizar/eliminar libros del carrito de compras.
-   Proceso de checkout para realizar pedidos (sin pasarela de pago implementada).
-   Visualización del historial de pedidos propios, comentarios y puntuaciones.
-   Añadir comentarios y valoraciones a los libros (no implementado).

**Para administradores (Rol: `administrador`):**

-   Acceso a un panel de administración con estadísticas clave (libros más vendidos, clientes recientes, totales).
-   Gestión completa (CRUD) de Libros.
-   Gestión completa (CRUD) de Autores.
-   Gestión completa (CRUD) de Editoriales.
-   Visualización de la lista de todos los clientes registrados y sus detalles.
-   Visualización de todos los pedidos realizados en la plataforma.

## 💻 Tecnologías utilizadas

-   **Backend:** PHP 8+, Laravel Framework
-   **Frontend:** HTML, Tailwind CSS, JavaScript, Vite
-   **Base de Datos:** MySQL
-   **Autenticación:** Laravel Breeze
-   **Gestión de Dependencias:** Composer, npm
-   **Testing:** PHPUnit (Pruebas unitarias y de integración)
-   **Documentación:** phpDocumentor
-   **Servidor Local:** XAMPP (Apache, MySQL, PHP)
-   **Despliegue:** Railway, Docker

## 🚀 Guía de instalación y configuración local (usando XAMPP)

Sigue estos pasos para poner en marcha el proyecto Spherework en tu entorno local utilizando XAMPP. Es **obligatorio** seguir estos pasos para poder evaluar la aplicación correctamente.

**1. Prerrequisitos:**

-   **XAMPP:** Debes tener XAMPP instalado (disponible en https://www.apachefriends.org/). Asegúrate de que los servicios **Apache** y **MySQL** estén iniciados desde el panel de control de XAMPP.
-   **Composer:** Necesitas Composer, el gestor de dependencias de PHP, instalado globalmente en tu sistema (instrucciones en https://getcomposer.org/). Puedes verificarlo abriendo una terminal y ejecutando `composer --version`.
-   **Node.js y npm:** Necesitas Node.js (que incluye npm, el gestor de paquetes de Node) instalado (disponible en https://nodejs.org/). Puedes verificarlo ejecutando `node -v` y `npm -v` en la terminal.
-   **Git (Opcional pero recomendado):** Git es útil para clonar el repositorio fácilmente. Si no lo tienes, puedes usar el archivo ZIP proporcionado o descargar el código fuente como un archivo ZIP desde GitHub.

**2. Obtener el código fuente:**

-   Navega hasta la carpeta `htdocs` dentro de tu directorio de instalación de XAMPP. Por ejemplo:
    `bash
    cd C:\xampp\htdocs
    `
-   **Opción A (Usando el ZIP proporcionado):** Si has recibido el proyecto como un archivo `spherework.zip`, descomprímelo directamente dentro de la carpeta `htdocs`. Asegúrate de que la carpeta se llame `spherework`.
-   **Opción B (Clonando o descargando el ZIP con Git):** Clona el repositorio del proyecto desde GitHub:
    `bash
    git clone https://github.com/GongoraLeo/spherework spherework
    `
-   Descarga el archivo ZIP desde GitHub, descomprímelo dentro de `htdocs` y asegúrate de que la carpeta resultante se llame `spherework`.
-   Entra en la carpeta del proyecto que acabas de obtener:
    `bash
    cd spherework
    `

**3. Instalar dependencias (si es necesario):**
* **Nota importante si usas el ZIP proporcionado (Opción A):** Si estás instalando desde el archivo `spherework.zip` que incluye las dependencias, los siguientes comandos (`composer install`, `npm install`) *pueden no ser estrictamente necesarios* inicialmente, ya que las carpetas `vendor` y `node_modules` deberían estar presentes. Puedes intentar continuar con el paso 4. Si encuentras problemas más adelante, puedes intentar ejecutar estos comandos.
* Si has clonado o descargado el ZIP de GitHub (Opción B), o si tienes problemas con la Opción A, ejecuta:
- Instala las dependencias de PHP:
`bash
            composer install --ignore-platform-reqs
            `
_(El flag `--ignore-platform-reqs` puede ser útil si hay pequeñas diferencias de versión de PHP, pero idealmente tu PHP de XAMPP debería ser compatible)_.
- Instala las dependencias de JavaScript:
`bash
            npm install
            `

**4. Configuración del entorno:**
- Laravel utiliza un archivo `.env` para la configuración específica del entorno. Copia el archivo de ejemplo:
- En Windows:
`bash
            copy .env.example .env
            `
- En Linux/Mac:
`bash
            cp .env.example .env
            `
- Genera la clave única de la aplicación necesaria para Laravel:
`bash
        php artisan key:generate
        `

**5. Configuración de la base de datos:**
- Abre **phpMyAdmin** desde el panel de control de XAMPP o accediendo a `http://localhost/phpmyadmin` en tu navegador.
- Crea una nueva base de datos. El nombre recomendado es `spherework`. Asegúrate de que el cotejamiento (collation) sea `utf8mb4_unicode_ci`. Ahora, edita el archivo `.env` que creaste en el paso anterior. Busca las siguientes líneas y configúralas para que coincidan con tu configuración de MySQL en XAMPP (los valores por defecto de XAMPP suelen ser los mostrados):
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spherework # Asegúrate que es el nombre de la BD que creaste
DB_USERNAME=root # Usuario por defecto de MySQL en XAMPP
DB_PASSWORD= # Contraseña por defecto en XAMPP (vacía)

        # Asegúrate también de que estas variables estén así para desarrollo local:
        APP_ENV=local
        APP_DEBUG=true
        APP_URL=http://localhost:8000 # O http://127.0.0.1:8000
        ```
- Guarda los cambios en el archivo `.env`.

**6. Migraciones y seeders:**
_ Vuelve a tu terminal, asegurándote de estar en la carpeta del proyecto (`C:\xampp\htdocs\spherework`).
_ Ejecuta las migraciones para crear todas las tablas en la base de datos `spherework`:
`bash
        php artisan migrate
        `
* Ejecuta los *seeders\*. Estos poblarán la base de datos con datos iniciales, incluyendo categorías, autores, libros de ejemplo y los usuarios de prueba (admin y cliente):
`bash
        php artisan db:seed
        `

**7. Compilar assets frontend:**
_ Para compilar los archivos CSS (Tailwind) y JavaScript necesarios para la interfaz:
`bash
        npm run dev
        `
_ Este comando iniciará un proceso de Vite que vigilará los cambios en los archivos fuente (CSS, JS, Blade) y los recompilará automáticamente. **Debes dejar esta terminal abierta mientras trabajas con la aplicación.** Si solo necesitas una compilación única para producción (no para desarrollo local activo), puedes usar `npm run build`. _(Nota: Si usaste el ZIP proporcionado con dependencias, los assets podrían estar ya compilados. Si la interfaz no se ve correctamente, ejecuta `npm run build` una vez o `npm run dev`)_.

**8. Servir la aplicación:**
_ Abre **una nueva terminal** (deja la de `npm run dev` ejecutándose si la iniciaste).
_ Navega de nuevo a la carpeta del proyecto (`cd C:\xampp\htdocs\spherework`).
_ Inicia el servidor de desarrollo incorporado de Laravel:
`bash
        php artisan serve
        `
_ Este comando te indicará la dirección en la que la aplicación está corriendo, normalmente `http://127.0.0.1:8000`.

**9. Acceder a la aplicación:**
_ Abre tu navegador web y ve a la dirección indicada por el comando `php artisan serve` (ej. `http://127.0.0.1:8000`).
_ ¡Deberías ver la página de inicio de Spherework! Puedes registrar un nuevo usuario o usar los usuarios de prueba creados por los seeders (ver sección "Usuarios de prueba").

## 🌐 Acceso a la aplicación desplegada (producción)

La aplicación se encuentra desplegada y accesible públicamente en la siguiente URL:

**https://spherework-production.up.railway.app**

Puedes interactuar con la aplicación directamente en esa dirección.

_(Nota: El despliegue utiliza un plan gratuito de Railway y puede "dormir" si no recibe tráfico. Si la aplicación no carga inicialmente, espera unos segundos y vuelve a intentarlo para "despertarla")._

## 👤 Usuarios de prueba (producción y local)

La base de datos se inicializa con los siguientes usuarios de prueba gracias a los _seeders_:

-   **Rol:** `administrador`
    -   **Email:** `admin@spherework.com`
    -   **Password:** `adminpassword`
-   **Rol:** `cliente`
    -   **Email:** `cliente@spherework.com`
    -   **Password:** `clientepassword`

Puedes usar estas credenciales para iniciar sesión y probar las funcionalidades de cada rol tanto en el entorno local como en la versión desplegada en Railway.

## 🧪 Testing y documentación

-   **Pruebas:** Se han realizado pruebas utilizando **PHPUnit** para asegurar la calidad y el correcto funcionamiento del código backend. Todas las pruebas han sido superadas satisfactoriamente.
    -   Puedes consultar el reporte de cobertura de las pruebas en la carpeta `coverage-report/index.html`.
-   **Documentación del código:** Se ha generado documentación automática del código fuente utilizando **phpDocumentor**. Esta documentación detalla las clases, métodos y propiedades del proyecto.
    -   Puedes explorar la documentación de la API en la carpeta `docs/api/index.html`.

## ⚙️ Configuración adicional (.env - solo local)

El archivo `.env` se usa principalmente para la configuración del **entorno local**. Contiene variables de entorno importantes. Además de la base de datos, revisa y ajusta si es necesario para tu configuración local:

-   `APP_NAME`: Nombre de la aplicación (ej. "Spherework").
-   `APP_URL`: URL base de tu aplicación local (ej. `http://localhost:8000`).
-   Configuraciones de correo (MAIL\_...) si necesitas probar el envío de emails localmente (requiere configuración adicional como Mailtrap o similar).
