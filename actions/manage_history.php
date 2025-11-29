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

if (!in_array($action, ['clear_all', 'toggle_pause', 'get_status'])) {
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
    // Primero obtenemos estado actual para asegurarnos
    $check = $conn->query("SELECT history_paused FROM users WHERE id = $user_id");
    if ($check) {
        $row = $check->fetch_assoc();
        $current = $row['history_paused']; // 0 o 1
        
        // Invertimos el estado: Si es 0 pasa a 1, si es 1 pasa a 0
        $newState = ($current == 1) ? 0 : 1;
        
        $stmt = $conn->prepare("UPDATE users SET history_paused = ? WHERE id = ?");
        $stmt->bind_param("ii", $newState, $user_id);
        
        if ($stmt->execute()) {
            // Actualizamos la sesión para evitar consultas extra en otras páginas
            $_SESSION['history_paused'] = (bool)$newState;

            echo json_encode([
                'success' => true, 
                'paused' => (bool)$newState,
                'message' => $newState ? 'Historial pausado' : 'Historial activado'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'db_update_error']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'db_read_error']);
    }
}

// 3. OBTENER ESTADO ACTUAL
elseif ($action === 'get_status') {
    $check = $conn->query("SELECT history_paused FROM users WHERE id = $user_id");
    if ($check) {
        $paused = (bool)$check->fetch_assoc()['history_paused'];
        // Sincronizar sesión
        $_SESSION['history_paused'] = $paused;
        
        echo json_encode(['success' => true, 'paused' => $paused]);
    } else {
        echo json_encode(['success' => false, 'error' => 'db_error']);
    }
}
?>