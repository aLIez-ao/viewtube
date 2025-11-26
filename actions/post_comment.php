<?php
// actions/post_comment.php
require_once '../config/db.php';
require_once '../includes/functions.php'; // Para usar timeAgo() si se necesitara, aunque aquí devolveremos la fecha raw o formateada

header('Content-Type: application/json');

// 1. Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit();
}

// 2. Recibir datos JSON
$data = json_decode(file_get_contents('php://input'), true);
$video_id = isset($data['video_id']) ? (int)$data['video_id'] : 0;
$content = isset($data['content']) ? trim($data['content']) : '';

// 3. Validaciones
if ($video_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'invalid_video']);
    exit();
}

if (empty($content)) {
    echo json_encode(['success' => false, 'error' => 'empty_content']);
    exit();
}

$user_id = $_SESSION['user_id'];

// 4. Insertar en BD
$stmt = $conn->prepare("INSERT INTO comments (user_id, video_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $video_id, $content);

if ($stmt->execute()) {
    $new_comment_id = $stmt->insert_id;
    
    // Obtener datos del usuario para devolver al frontend (Avatar, Nombre)
    // Asumimos que los datos de sesión están frescos, pero mejor consultar DB para asegurar avatar actualizado
    $user_query = $conn->query("SELECT username, avatar FROM users WHERE id = $user_id");
    $user_data = $user_query->fetch_assoc();
    
    // Preparar URL del avatar
    $avatarUrl = $user_data['avatar'];
    if ($avatarUrl === 'default.png') {
        $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($user_data['username']) . "&background=random&color=fff&size=64";
    } else {
        // Ajusta la ruta según tu estructura real si es absoluta o relativa
        $avatarUrl = BASE_URL . 'uploads/avatars/' . $avatarUrl; 
    }

    // Devolver el comentario creado
    echo json_encode([
        'success' => true,
        'comment' => [
            'id' => $new_comment_id,
            'username' => $user_data['username'],
            'avatar' => $avatarUrl,
            'content' => nl2br(htmlspecialchars($content)), // Convertir saltos de línea y escapar HTML
            'date' => 'hace unos segundos' // Texto estático inmediato
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'db_error']);
}
?>