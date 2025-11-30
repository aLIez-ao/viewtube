<?php
// actions/studio_actions.php
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

// CREAR CANAL
if ($action === 'create_channel') {
    $name = trim($data['name'] ?? '');
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Nombre requerido']);
        exit();
    }

    // Verificar si ya tiene canal
    $check = $conn->query("SELECT id FROM channels WHERE user_id = $user_id");
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Ya tienes un canal']);
        exit();
    }

    // Insertar canal
    $stmt = $conn->prepare("INSERT INTO channels (user_id, name, description) VALUES (?, ?, '')");
    $stmt->bind_param("is", $user_id, $name);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al crear canal']);
    }
}
?>