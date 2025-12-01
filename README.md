# ViewTube - Clon de YouTube

ViewTube es una aplicación web dinámica desarrollada en PHP que simula las funcionalidades principales de una plataforma de video como YouTube. Permite a los usuarios registrarse, iniciar sesión, gestionar su propio canal, ver videos, suscribirse, dar "Me gusta", comentar y administrar listas de reproducción.

## Características Principales

### Gestión de Usuarios

**Registro e Inicio de Sesión:** Sistema seguro con hash de contraseñas.

**Perfiles de Usuario:** Cada usuario tiene un avatar (personalizado o generado automáticamente con sus iniciales).

**Sesiones:** Manejo de sesiones para mantener al usuario conectado.

### Gestión de Canales

**Creación Automática:** Al registrarse, se crea un canal básico para el usuario.

**YouTube Studio:** Panel de control independiente (studio.php) para gestionar el canal.

**Página de Canal:** Vista pública (`channel.php`) con banner, avatar, estadísticas y todos los videos subidos.

### Gestión de Videos

**Publicación (Simulada):** Sistema para "subir" videos pegando un enlace de YouTube. El sistema extrae automáticamente el ID y la miniatura.

**Reproductor:** Página dedicada (`watch.php`) con reproductor de video, información detallada y estadísticas.

**Videos Relacionados:** Barra lateral que sugiere contenido similar o aleatorio.

### Interacción Social

**Suscripciones:** Botón funcional para suscribirse/desuscribirse a canales.

**Likes/Dislikes:** Sistema de valoración en tiempo real para videos y comentarios.

**Comentarios Anidados:** Sistema de comentarios recursivo que permite responder a otros comentarios (hasta 3 niveles visuales).

**Compartir:** Modal para compartir el video en redes sociales o copiar el enlace con marca de tiempo.

### Bibliotecas Personales

**Historial:** Registro automático de videos vistos con opción de "Pausar" y "Borrar historial".

**Mis Listas:** Creación y gestión de listas de reproducción personalizadas.

**Ver más tarde:** Lista especial integrada para guardar videos.

**Videos que me gustan:** Lista automática de todos los videos a los que el usuario dio "Like".

## Tecnologías Utilizadas

**Backend:** PHP (Nativo, sin frameworks)

**Base de Datos:** MySQL

**Frontend:** HTML5, CSS3, JavaScript (Vanilla JS)

**Estilos:** CSS personalizado + Materialize CSS (para iconos y algunos componentes base)

**Librerías Externas:**

**Fuentes e Iconos:** Google Fonts / Material Icons

**Avatares por defecto:** UI Avatars API

## Estructura del Proyecto

El proyecto ViewTube se diseñó con una arquitectura deliberada que busca el equilibrio perfecto entre la simplicidad del PHP nativo y la experiencia de usuario moderna y fluida de las grandes plataformas como YouTube. No fue un accidente, sino una decisión de ingeniería para crear un sistema escalable, mantenible y profesional.

### La Arquitectura

Para ViewTube, adoptamos un enfoque más limpio inspirado en el patrón Modelo-Vista-Controlador (MVC), pero adaptado a PHP puro:

**Las Vistas (Views):** Archivos como `index.php`, `watch.php` o `channel.php` son la cara visible. Su única responsabilidad es mostrar la interfaz. No procesan formularios ni guardan datos directamente; solo piden información y la pintan.

**Los Controladores (Actions):** La carpeta `actions/` actúa como el cerebro oculto. Archivos como `login_user.php` o `rate_video.php` reciben las peticiones del usuario, hablan con la base de datos y devuelven una respuesta (ya sea redirigiendo o enviando datos JSON). Esto mantiene tus archivos visuales limpios y seguros.

**Componentización (Includes):** Para respetar el principio DRY (Don't Repeat Yourself), fragmentamos la interfaz. El menú lateral (`sidebar.php`), la cabecera (`header.php`) y las tarjetas de video (`video_card.php`) son piezas de LEGO. Si mañana quieres cambiar el logo, lo haces en un solo lugar y se actualiza en las 20 páginas del sitio automáticamente.

### La Ilusión de una SPA (Single Page Application)

Aquí es donde el proyecto brilla. YouTube es una Single Page Application (SPA), lo que significa que nunca sientes que la página se "recarga" por completo al dar un like o comentar; todo es instantáneo.

Lograr eso en PHP tradicional es difícil porque PHP trabaja por recargas. Sin embargo, emulamos este comportamiento utilizando JavaScript moderno (`Fetch API`) para crear una experiencia híbrida:

**Navegación Tradicional, Interacción Moderna:** Cuando cambias de `index.php` a `watch.php`, el navegador recarga.

**Comunicación** Asíncrona (AJAX):** Al hacer clic en "Suscribirse", el archivo `watch.js` envía una señal silenciosa a `subscribe.php`. El usuario no ve la página parpadear.

**Optimistic UI (Interfaz Optimista):** Cuando das clic, el botón cambia de color inmediatamente vía JavaScript, sin esperar a que el servidor responda. Esto hace que la aplicación se sienta instantánea, eliminando la latencia de red de la percepción del usuario. Si luego el servidor dice que hubo un error, revertimos el cambio, pero la mayoria de las veces el usuario siente una velocidad nativa.

```plaintext
proyecto-final-web/
│
├── actions/                 # Lógica de backend (API endpoints)
│   ├── login_user.php       # Procesa el login
│   ├── register_user.php    # Procesa el registro
│   ├── upload_video.php     # Guarda nuevos videos
│   ├── subscribe.php        # Maneja suscripciones
│   ├── rate_video.php       # Maneja likes de videos
│   ├── post_comment.php     # Publica comentarios
│   ├── rate_comment.php     # Maneja likes de comentarios
│   ├── manage_history.php   # Borrar/Pausar historial
│   ├── manage_comment.php   # Editar/Borrar comentarios
│   └── playlist.php         # CRUD de listas de reproducción
│
├── assets/                  # Recursos estáticos
│   ├── css/                 # Hojas de estilo (home.css, watch.css, etc.)
│   ├── js/                  # Lógica frontend (app.js, watch.js, etc.)
│   └── img/                 # Imágenes fijas (favicon, logo)
│
├── config/                  # Configuración
│   └── db.sample.php               # Conexión a BD y constantes globales
│
├── includes/                # Fragmentos reutilizables
│   ├── header.php           # Cabecera y navegación
│   ├── footer.php           # Cierre de página y scripts
│   ├── sidebar.php          # Barra lateral de navegación
│   ├── functions.php        # Funciones auxiliares (timeAgo, formatDuration)
│   └── components/          # Pequeños componentes UI (tarjetas de video)
│
├── uploads/                 # Archivos de usuario (Avatares, Miniaturas)
│
├── index.php                # Página de Inicio
├── watch.php                # Página de Reproducción
├── login.php                # Login
├── register.php             # Registro
├── studio.php               # Panel de creador
├── channel.php              # Perfil público del canal
├── history.php              # Historial de visualización
├── subscriptions.php        # Feed de suscripciones
├── playlists.php            # Biblioteca de listas
├── liked.php                # Lista "Videos que me gustan"
└── watch_later.php          # Lista "Ver más tarde"
```

## Configuración e Instalación

### Base de Datos

Crea una base de datos en MySQL llamada `viewtube_db`.

Importa el archivo `DB/viewtube_db/sql/registro.sql`. Esto creará todas las tablas y poblará la base de datos con usuarios y videos de prueba reales.

### Conexión

Abre `config/db.php`.

Configura las credenciales de tu servidor (Local o Producción):

```plaintext
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'viewtube_db';

define('BASE_URL', 'URL');
```

### Permisos

Asegúrate de que la carpeta `uploads/` y sus subcarpetas tengan permisos de escritura si planeas implementar subida de imágenes reales en el futuro.

## Desarrollo

El proyecto ViewTube se desarrolló siguiendo un enfoque arquitectónico moderno y escalable, utilizando PHP puro (Vanilla PHP) y MySQL, pero emulando las características de una Single Page Application (SPA) para ofrecer una experiencia de usuario fluida y profesional.

### Arquitectura y Diseño

**Modelo MVC Simplificado:** Aunque no se utilizó un framework pesado, el proyecto sigue una estructura inspirada en el patrón Modelo-Vista-Controlador (MVC).

**Vistas (Views):** Archivos como index.php, watch.php o history.php actúan como las vistas principales que el usuario ve. Son responsables de la estructura HTML y de incluir componentes reutilizables.

**Controladores (Actions):** La carpeta `actions/` contiene la lógica de negocio pura (`login_user.php`, `rate_video.php`, `subscribe.php`). Estos scripts reciben datos (generalmente vía POST o AJAX), interactúan con la base de datos y devuelven una respuesta (JSON o redirección), separando claramente la lógica de la presentación.

**Modelos (Implícitos):** La interacción con la base de datos se centraliza en consultas SQL directas y seguras dentro de los controladores y vistas, apoyadas por un archivo de configuración común (`config/db.php`).

**Componentización:** Para evitar la repetición de código y facilitar el mantenimiento, se dividió la interfaz en componentes modulares.

El `header.php` y `sidebar.php` se incluyen en casi todas las páginas, garantizando consistencia.

Las tarjetas de video (`video_card.php` y `video_card_small.php`) son fragmentos aislados que se reutilizan en el Inicio, en el Historial, en las Suscripciones y en la barra lateral del reproductor. Esto permite que si cambias el diseño de una tarjeta, el cambio se refleje en todo el sitio.

### Emulación de SPA (Single Page Application)

Uno de los objetivos principales fue que la aplicación se sintiera rápida y reactiva, similar a cómo funciona YouTube real (que es una SPA compleja).

**AJAX y Fetch API:** En lugar de recargar la página completa cada vez que el usuario realiza una acción (como dar "Me gusta", suscribirse o comentar), utilizamos JavaScript moderno (fetch) para enviar peticiones al servidor en segundo plano.

**Interactividad Inmediata (Optimistic UI):** Cuando das clic en "Me gusta", el icono cambia de color al instante y el contador sube antes de que el servidor responda. Esto crea una sensación de velocidad instantánea. Si luego el servidor devuelve un error, revertimos el cambio visualmente y mostramos un mensaje.

**Comentarios Dinámicos:** Los nuevos comentarios se inyectan directamente en el DOM (la estructura de la página) sin necesidad de refrescar.

**Navegación Inteligente:** Aunque técnicamente navegamos entre archivos PHP distintos (`index.php` -> `watch.php`), la estructura compartida del header y sidebar, junto con un CSS, hace que la transición se sienta natural. Además, implementamos lógica como abrir los canales en pestañas nuevas (`target="_blank"`) para no interrumpir la reproducción del video actual, imitando el flujo de consumo de contenido continuo.

### Base de Datos y Datos Reales

Para que el proyecto no se sintiera como un esqueleto vacío, se puso mucho esfuerzo en la capa de datos.

**Integridad Referencial:** El diseño de la base de datos es relacional y robusto. Un video no puede existir sin un canal, un comentario no puede existir sin un usuario, y el historial depende de ambos. Usamos claves foráneas (FOREIGN KEY) para garantizar que los datos siempre sean consistentes.

**Datos de Prueba Realistas:** En lugar de usar "Lorem Ipsum", creamos un script de población (`registro.sql`) que inyecta canales reales (como Marques Brownlee o Lofi Girl), videos con títulos y descripciones auténticas, y utiliza IDs reales de YouTube. Esto permite que las miniaturas se generen automáticamente desde los servidores de Google (`img.youtube.com`) y que el reproductor iframe cargue contenido verdadero, haciendo que la demo sea funcional y atractiva.

### Experiencia de Usuario (UX/UI)

El diseño visual (CSS) se inspiró fuertemente en la estética limpia y funcional de YouTube (Material Design).

**Detalles Visuales:** Se cuidaron detalles como los bordes redondeados, las sombras suaves en los menús desplegables, los estados hover en los botones y el uso de iconos estándar (Material Icons) para que la interfaz fuera intuitiva para cualquier usuario familiarizado con plataformas de video.

**Feedback al Usuario:** Implementamos "Toasts" (mensajes emergentes) para confirmar acciones como "Añadido al historial" o "Suscripción eliminada", guiando al usuario en todo momento.
