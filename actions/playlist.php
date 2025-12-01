<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

// Obtener listas
if ($action === 'get_playlists') {
    $video_id = (int)($data['video_id'] ?? 0);
    
    // Consultamos las playlists y verificamos si el video ya está en ellas
    $sql = "SELECT p.id, p.title, p.is_private, 
            (SELECT count(*) FROM playlist_videos pv WHERE pv.playlist_id = p.id AND pv.video_id = ?) as has_video
            FROM playlists p 
            WHERE p.user_id = ? 
            ORDER BY p.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $video_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $playlists = [];
    while($row = $res->fetch_assoc()) {
        $playlists[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'is_private' => (bool)$row['is_private'],
            'contains_video' => $row['has_video'] > 0
        ];
    }
    
    echo json_encode(['success' => true, 'playlists' => $playlists]);
}

// agregar/Quitar de lista
elseif ($action === 'toggle_video') {
    $playlist_id = (int)$data['playlist_id'];
    $video_id = (int)$data['video_id'];
    $add = (bool)$data['add']; // true = check, false = uncheck
    
    // Verificar que la playlist sea del usuario
    $check = $conn->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $check->bind_param("ii", $playlist_id, $user_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'forbidden']);
        exit();
    }
    
    if ($add) {
        // Insertar (usamos IGNORE por si acaso ya existe para no dar error)
        $stmt = $conn->prepare("INSERT IGNORE INTO playlist_videos (playlist_id, video_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $playlist_id, $video_id);
        $stmt->execute();
    } else {
        // Eliminar
        $stmt = $conn->prepare("DELETE FROM playlist_videos WHERE playlist_id = ? AND video_id = ?");
        $stmt->bind_param("ii", $playlist_id, $video_id);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true]);
}

// Crear lista
elseif ($action === 'create') {
    $title = trim($data['title'] ?? '');
    $video_id = (int)($data['video_id'] ?? 0);
    // Por defecto pública o privada según prefieras, aquí la haremos pública por defecto
    $is_private = 0; 
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'error' => 'empty_title']);
        exit();
    }
    
    // Crear la playlist
    $stmt = $conn->prepare("INSERT INTO playlists (user_id, title, is_private) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $title, $is_private);
    
    if ($stmt->execute()) {
        $new_playlist_id = $stmt->insert_id;
        
        // Si se pasó un video, agregarlo automáticamente a la nueva lista
        if ($video_id > 0) {
            $add = $conn->prepare("INSERT INTO playlist_videos (playlist_id, video_id) VALUES (?, ?)");
            $add->bind_param("ii", $new_playlist_id, $video_id);
            $add->execute();
        }
        
        echo json_encode(['success' => true, 'playlist_id' => $new_playlist_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'db_error']);
    }
}
?>