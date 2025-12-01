<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Error: Debes iniciar sesión.");
}

$user_id = $_SESSION['user_id'];

// Obtener ID del canal del usuario actual
$sql = "SELECT id FROM channels WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

// Si no tiene canal
if ($res->num_rows === 0) {
    header("Location: ../studio.php");
    exit();
}

$channel = $res->fetch_assoc();
$channel_id = $channel['id'];

// Recibir Datos del Formulario
$title = trim($_POST['title'] ?? '');
$video_url = trim($_POST['video_url'] ?? '');
$description = trim($_POST['description'] ?? '');
$duration = 300; 

// Extraer ID de YouTube de la URL. Regex/Directo
$youtube_id = '';
$pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

if (preg_match($pattern, $video_url, $match)) {
    $youtube_id = $match[1];
}
if (strlen($video_url) == 11) {
    $youtube_id = $video_url;
}

// Validaciones básicas
if (empty($title) || empty($youtube_id)) {
    die("Error: Título inválido o URL de YouTube no reconocida.");
}

// Generar URL de Miniatura Automática
$thumbnail_url = "https://img.youtube.com/vi/{$youtube_id}/mqdefault.jpg";

// Insertar Video en la Base de Datos
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