<?php
// actions/upload_video.php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Error: Debes iniciar sesión.");
}

$user_id = $_SESSION['user_id'];

// 1. Obtener ID del canal del usuario actual
$sql = "SELECT id FROM channels WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    // Si no tiene canal, lo mandamos a crear uno
    header("Location: ../studio.php");
    exit();
}
$channel = $res->fetch_assoc();
$channel_id = $channel['id'];

// 2. Recibir Datos del Formulario
$title = trim($_POST['title'] ?? '');
$video_url = trim($_POST['video_url'] ?? '');
$description = trim($_POST['description'] ?? '');
// Duración por defecto (5 min) ya que no la pedimos al usuario
$duration = 300; 

// 3. Extraer ID de YouTube de la URL
$youtube_id = '';
// Expresión regular para detectar IDs de YouTube en varios formatos de URL
$pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
if (preg_match($pattern, $video_url, $match)) {
    $youtube_id = $match[1];
} else {
    // Si el usuario pegó solo el ID (11 caracteres), lo usamos directamente
    if (strlen($video_url) == 11) {
        $youtube_id = $video_url;
    }
}

// Validaciones básicas
if (empty($title) || empty($youtube_id)) {
    die("Error: Título inválido o URL de YouTube no reconocida.");
}

// 4. Generar URL de Miniatura Automática
$thumbnail_url = "https://img.youtube.com/vi/{$youtube_id}/mqdefault.jpg";

// 5. Insertar Video en la Base de Datos
$stmt = $conn->prepare("INSERT INTO videos (channel_id, title, description, youtube_id, thumbnail_url, duration, views) VALUES (?, ?, ?, ?, ?, ?, 0)");
$stmt->bind_param("issssi", $channel_id, $title, $description, $youtube_id, $thumbnail_url, $duration);

if ($stmt->execute()) {
    $new_video_id = $stmt->insert_id;
    
    // Actualizar contador de videos del canal
    $conn->query("UPDATE channels SET total_videos = total_videos + 1 WHERE id = $channel_id");

    // Redirigir al video recién publicado
    header("Location: ../watch.php?id=" . $new_video_id);
    exit();
} else {
    die("Error en base de datos: " . $conn->error);
}
?>