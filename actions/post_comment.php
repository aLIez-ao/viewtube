<?php
// actions/post_comment.php
require_once '../config/db.php';
require_once '../includes/functions.php'; 

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
    
    $user_query = $conn->query("SELECT username, avatar FROM users WHERE id = $user_id");
    $user_data = $user_query->fetch_assoc();
    
    $avatarUrl = $user_data['avatar'];
    if ($avatarUrl === 'default.png') {
        $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($user_data['username']) . "&background=random&color=fff&size=64";
    } else {
        $avatarUrl = BASE_URL . 'uploads/avatars/' . $avatarUrl; 
    }

    echo json_encode([
        'success' => true,
        'comment' => [
            'id' => $new_comment_id,
            'username' => $user_data['username'],
            'avatar' => $avatarUrl,
            // CAMBIO: Solo escapamos el HTML, no convertimos newlines a <br>
            'content' => htmlspecialchars($content), 
            'date' => 'hace unos segundos' 
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'db_error']);
}
?>