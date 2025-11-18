<?php
/* config/db.sample.php
   Instrucciones: 
   1. Renombra este archivo a 'db.php'
   2. Cambia los valores por tus credenciales reales
*/

define('DB_HOST', 'localhost');
define('DB_USER', 'tu_usuario_aqui');
define('DB_PASS', 'tu_password_aqui');
define('DB_NAME', 'nombre_base_datos');

// Rutas del sistema
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));
define('BASE_URL', 'http://localhost/proyecto-final-web/');
?>