# Estructura del proyecto

```plaintext
proyecto-final-web/
│
├── assets/                 <-- "Archivos estáticos" (Lo que el navegador descarga)
│   ├── css/                (Tu style.css)
│   ├── js/                 (Tus scripts de Materialize y lógica de UI)
│   ├── img/                (Logos, iconos fijos del sitio)
│   └── libs/               (Aquí puedes guardar materialize si no usas CDN)
│
├── config/                 <-- "Configuración Global"
│   └── db.php              (Conexión a Base de Datos y constantes globales)
│
├── includes/               <-- "Fragmentos de HTML" (Reutilizables)
│   ├── header.php          (Inicio del HTML y Navbar)
│   ├── footer.php          (Cierre del HTML y Scripts)
│   └── sidebar.php         (Barra lateral)
│
├── actions/                <-- "Lógica Pura" (Backend)
│   ├── login_user.php      (Recibe el POST del login y redirige)
│   ├── register_user.php   (Recibe el POST del registro)
│   └── upload_video.php    (Procesa la subida del video)
│
├── uploads/                <-- "Contenido generado por el usuario"
│   ├── thumbnails/         (Imágenes de los videos)
│   └── avatars/            (Fotos de perfil de usuarios)
│
├── index.php               <-- Vista Principal (Home)
├── watch.php               <-- Vista del Reproductor
├── login.php               <-- Vista del Formulario de Login
├── register.php            <-- Vista del Formulario de Registro
└── upload.php              <-- Vista del Formulario de Subida
```
