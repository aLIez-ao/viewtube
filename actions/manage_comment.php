<?php
// actions/manage_comment.php
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : ''; // 'edit' o 'delete'
$comment_id = isset($data['comment_id']) ? (int)$data['comment_id'] : 0;

if ($comment_id <= 0 || !in_array($action, ['edit', 'delete'])) {
    echo json_encode(['success' => false, 'error' => 'invalid_data']);
    exit();
}

// 1. Verificar propiedad del comentario
$check = $conn->prepare("SELECT id, user_id FROM comments WHERE id = ?");
$check->bind_param("i", $comment_id);
$check->execute();
$comment = $check->get_result()->fetch_assoc();
$check->close();

if (!$comment) {
    echo json_encode(['success' => false, 'error' => 'not_found']);
    exit();
}

if ($comment['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'error' => 'forbidden']);
    exit();
}

// 2. Ejecutar acción
if ($action === 'delete') {
    // Eliminar (ON DELETE CASCADE en la BD debería borrar likes y respuestas, 
    // pero si no, deberíamos borrarlas manualmente. Asumimos integridad referencial).
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'action' => 'deleted']);
    } else {
        echo json_encode(['success' => false, 'error' => 'db_error']);
    }
} 
elseif ($action === 'edit') {
    $new_content = isset($data['content']) ? trim($data['content']) : '';
    
    if (empty($new_content)) {
        echo json_encode(['success' => false, 'error' => 'empty_content']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE comments SET content = ? WHERE id = ?");
    $stmt->bind_param("si", $new_content, $comment_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'action' => 'edited', 
            'content' => htmlspecialchars($new_content) // Devolvemos seguro para JS
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'db_error']);
    }
}
?>