<?php
// actions/rate_video.php
require_once '../config/db.php';

// Iniciamos el buffer de salida para atrapar cualquier espacio en blanco accidental
ob_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    ob_clean(); // Limpiar antes de enviar
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$video_id = isset($data['video_id']) ? (int)$data['video_id'] : 0;
$type = isset($data['type']) ? $data['type'] : ''; 

if ($video_id <= 0 || !in_array($type, ['like', 'dislike'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'invalid_data']);
    exit();
}

// Verificar voto existente
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
            // Quitar voto
            $del = $conn->prepare("DELETE FROM likes WHERE id = ?");
            $del->bind_param("i", $existing['id']);
            $del->execute();
            $action_result = 'removed';
        } else {
            // Cambiar voto
            $upd = $conn->prepare("UPDATE likes SET type = ? WHERE id = ?");
            $upd->bind_param("si", $type, $existing['id']);
            $upd->execute();
            $action_result = 'changed';
        }
    } else {
        // Nuevo voto
        $ins = $conn->prepare("INSERT INTO likes (user_id, video_id, type) VALUES (?, ?, ?)");
        $ins->bind_param("iis", $user_id, $video_id, $type);
        $ins->execute();
        $action_result = 'added';
    }

    // CONTAR
    $res_likes = $conn->query("SELECT COUNT(*) as c FROM likes WHERE video_id = $video_id AND type = 'like'");
    $res_dislikes = $conn->query("SELECT COUNT(*) as c FROM likes WHERE video_id = $video_id AND type = 'dislike'");

    $count_likes = ($res_likes) ? intval($res_likes->fetch_assoc()['c']) : 0;
    $count_dislikes = ($res_dislikes) ? intval($res_dislikes->fetch_assoc()['c']) : 0;

    $conn->commit();

    // --- LIMPIEZA FINAL ---
    // Borramos cualquier texto/html generado antes por error
    ob_end_clean(); 
    
    echo json_encode([
        'success' => true,
        'action' => $action_result,
        'likes' => $count_likes,
        'dislikes' => $count_dislikes
    ]);

} catch (Exception $e) {
    $conn->rollback();
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'db_error', 'msg' => $e->getMessage()]);
}
?>