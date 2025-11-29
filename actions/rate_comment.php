<?php
// actions/rate_comment.php
require_once '../config/db.php';

// Limpieza de buffer por si acaso
ob_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$comment_id = isset($data['comment_id']) ? (int)$data['comment_id'] : 0;
$type = isset($data['type']) ? $data['type'] : ''; 

if ($comment_id <= 0 || !in_array($type, ['like', 'dislike'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'invalid_data']);
    exit();
}

// 1. Verificar voto existente para este COMENTARIO
$check = $conn->prepare("SELECT id, type FROM likes WHERE user_id = ? AND comment_id = ?");
$check->bind_param("ii", $user_id, $comment_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();
$check->close();

$action_result = 'none';
$conn->begin_transaction();

try {
    if ($existing) {
        if ($existing['type'] === $type) {
            // Quitar voto (Toggle)
            $del = $conn->prepare("DELETE FROM likes WHERE id = ?");
            $del->bind_param("i", $existing['id']);
            $del->execute();
            $action_result = 'removed';
            
            // Decrementar contador en tabla comments (si quieres caché)
            if($type === 'like') {
                $conn->query("UPDATE comments SET likes = GREATEST(0, likes - 1) WHERE id = $comment_id");
            }
        } else {
            // Cambiar voto
            $upd = $conn->prepare("UPDATE likes SET type = ? WHERE id = ?");
            $upd->bind_param("si", $type, $existing['id']);
            $upd->execute();
            $action_result = 'changed';
            
            // Ajustar contador caché
            if($type === 'like') {
                $conn->query("UPDATE comments SET likes = likes + 1 WHERE id = $comment_id");
            } else {
                $conn->query("UPDATE comments SET likes = GREATEST(0, likes - 1) WHERE id = $comment_id");
            }
        }
    } else {
        // Nuevo voto
        $ins = $conn->prepare("INSERT INTO likes (user_id, comment_id, type) VALUES (?, ?, ?)");
        $ins->bind_param("iis", $user_id, $comment_id, $type);
        $ins->execute();
        $action_result = 'added';
        
        // Incrementar contador caché
        if($type === 'like') {
            $conn->query("UPDATE comments SET likes = likes + 1 WHERE id = $comment_id");
        }
    }

    // CONTAR REAL (Para asegurar consistencia)
    $res_likes = $conn->query("SELECT COUNT(*) as c FROM likes WHERE comment_id = $comment_id AND type = 'like'");
    $count_likes = ($res_likes) ? intval($res_likes->fetch_assoc()['c']) : 0;

    // Actualizar el campo 'likes' en la tabla comments para consistencia futura
    $conn->query("UPDATE comments SET likes = $count_likes WHERE id = $comment_id");

    $conn->commit();
    ob_end_clean(); 
    
    echo json_encode([
        'success' => true,
        'action' => $action_result,
        'likes' => $count_likes
    ]);

} catch (Exception $e) {
    $conn->rollback();
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'db_error', 'msg' => $e->getMessage()]);
}
?>