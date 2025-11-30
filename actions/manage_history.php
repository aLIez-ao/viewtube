<?php
// actions/manage_history.php
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : '';

// Validar acciones permitidas
if (!in_array($action, ['clear_all', 'toggle_pause', 'get_status', 'remove_item'])) {
    echo json_encode(['success' => false, 'error' => 'invalid_action']);
    exit();
}

// 1. BORRAR TODO EL HISTORIAL
if ($action === 'clear_all') {
    $stmt = $conn->prepare("DELETE FROM history WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Historial borrado']);
    } else {
        echo json_encode(['success' => false, 'error' => 'db_error']);
    }
}

// 2. PAUSAR / REANUDAR (Toggle)
elseif ($action === 'toggle_pause') {
    $check = $conn->query("SELECT history_paused FROM users WHERE id = $user_id");
    $current = $check->fetch_assoc()['history_paused'];
    $newState = $current ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE users SET history_paused = ? WHERE id = ?");
    $stmt->bind_param("ii", $newState, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'paused' => (bool)$newState,
            'message' => $newState ? 'Historial pausado' : 'Historial activado'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'db_error']);
    }
}

// 3. OBTENER ESTADO ACTUAL
elseif ($action === 'get_status') {
    $check = $conn->query("SELECT history_paused FROM users WHERE id = $user_id");
    $paused = (bool)$check->fetch_assoc()['history_paused'];
    echo json_encode(['success' => true, 'paused' => $paused]);
}

// 4. BORRAR UN SOLO ITEM (NUEVO)
elseif ($action === 'remove_item') {
    $video_id = isset($data['video_id']) ? (int)$data['video_id'] : 0;
    
    if ($video_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'invalid_id']);
        exit();
    }

    // Borramos todas las entradas de ese video para este usuario (por si hubiera duplicados antiguos)
    $stmt = $conn->prepare("DELETE FROM history WHERE user_id = ? AND video_id = ?");
    $stmt->bind_param("ii", $user_id, $video_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'db_error']);
    }
}
?>