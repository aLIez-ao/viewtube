<?php
// actions/rate_video.php
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$video_id = isset($data['video_id']) ? (int)$data['video_id'] : 0;
$type = isset($data['type']) ? $data['type'] : ''; // 'like' o 'dislike'

if ($video_id <= 0 || !in_array($type, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'error' => 'invalid_data']);
    exit();
}

// 1. Verificar si ya existe un voto
$check = $conn->prepare("SELECT id, type FROM likes WHERE user_id = ? AND video_id = ?");
$check->bind_param("ii", $user_id, $video_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();
$check->close();

$action_result = 'none';

$conn->begin_transaction();

try {
    if ($existing) {
        if ($existing['type'] === $type) {
            // Si clickea lo mismo (ej. Like sobre Like) -> Borrar voto (Toggle off)
            $del = $conn->prepare("DELETE FROM likes WHERE id = ?");
            $del->bind_param("i", $existing['id']);
            $del->execute();
            $action_result = 'removed';
        } else {
            // Si cambia de opinión (ej. Like -> Dislike) -> Actualizar
            $upd = $conn->prepare("UPDATE likes SET type = ? WHERE id = ?");
            $upd->bind_param("si", $type, $existing['id']);
            $upd->execute();
            $action_result = 'changed';
        }
    } else {
        // Voto nuevo
        $ins = $conn->prepare("INSERT INTO likes (user_id, video_id, type) VALUES (?, ?, ?)");
        $ins->bind_param("iis", $user_id, $video_id, $type);
        $ins->execute();
        $action_result = 'added';
    }

    // Contar totales actualizados para devolver
    $count_likes = $conn->query("SELECT COUNT(*) as c FROM likes WHERE video_id = $video_id AND type = 'like'")->fetch_assoc()['c'];
    $count_dislikes = $conn->query("SELECT COUNT(*) as c FROM likes WHERE video_id = $video_id AND type = 'dislike'")->fetch_assoc()['c'];

    $conn->commit();

    echo json_encode([
        'success' => true,
        'action' => $action_result,
        'likes' => $count_likes,
        'dislikes' => $count_dislikes // Opcional, YouTube ya no muestra dislikes públicos pero el backend debe saberlo
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'db_error']);
}
?>