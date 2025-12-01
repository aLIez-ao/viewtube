<?php
/* config/db.sample.php
   Instrucciones: 
   1. Renombra este archivo a 'db.php'
   2. Cambia los valores por tus credenciales reales
*/

// ========== INICIAR SESIÓN ==================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========== CONFIGURACIÓN DE BASE DE DATOS ==================

// MODO LOCAL (XAMPP)
// $DB_HOST = 'localhost';
// DB_USER = 'root';
// $DB_PASS = '';
// $DB_NAME = '';

// MODO PRODUCCIÓN (InfinityFree)
$DB_HOST = '';
$DB_USER = '';
$DB_PASS = '';
$DB_NAME = '';

// ========== CONEXIÓN A LA BASE DE DATOS ====================
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer codificación
$conn->set_charset("utf8");

// ========== CONSTANTES DE RUTAS ============================

// MODO LOCAL (XAMPP) 
// ('BASE_URL', 'http://localhost/proyecto-final-web/');

// MODO PRODUCCIÓN (InfinityFree) 
define('BASE_URL', 'URL');

// Ruta física del proyecto (útil para subir archivos)
define('ROOT_PATH', dirname(__DIR__) . '/');

// ========== CONFIGURACIÓN GLOBAL ============================
$APP_NAME = "WEB SITE";
