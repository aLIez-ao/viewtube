<?php
require_once '../config/db.php';

// Buffer para limpieza
ob_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'auth_required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$channel_id = isset($data['channel_id']) ? (int)$data['channel_id'] : 0;

if ($channel_id <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'invalid_channel']);
    exit();
}

// Evitar auto-suscripción
$stmt = $conn->prepare("SELECT user_id FROM channels WHERE id = ?");
$stmt->bind_param("i", $channel_id);
$stmt->execute();
$res = $stmt->get_result();
$channel = $res->fetch_assoc();

if (!$channel || $channel['user_id'] == $user_id) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'own_channel']);
    exit();
}

$check = $conn->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND channel_id = ?");
$check->bind_param("ii", $user_id, $channel_id);
$check->execute();
$is_subscribed = $check->get_result()->num_rows > 0;
$check->close();

$conn->begin_transaction();

try {
    if ($is_subscribed) {
        $delete = $conn->prepare("DELETE FROM subscriptions WHERE user_id = ? AND channel_id = ?");
        $delete->bind_param("ii", $user_id, $channel_id);
        $delete->execute();
        
        $update = $conn->prepare("UPDATE channels SET subscribers_count = subscribers_count - 1 WHERE id = ?");
        $update->bind_param("i", $channel_id);
        $update->execute();
        
        $status = 'unsubscribed';
    } else {
        $insert = $conn->prepare("INSERT INTO subscriptions (user_id, channel_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $channel_id);
        $insert->execute();
        
        $update = $conn->prepare("UPDATE channels SET subscribers_count = subscribers_count + 1 WHERE id = ?");
        $update->bind_param("i", $channel_id);
        $update->execute();
        
        $status = 'subscribed';
    }

    $count_query = $conn->query("SELECT subscribers_count FROM channels WHERE id = $channel_id");
    $new_count = $count_query->fetch_assoc()['subscribers_count'];

    $conn->commit();

    // limpieza
    ob_end_clean();

    echo json_encode([
        'success' => true, 
        'status' => $status, 
        'count' => number_format($new_count)
    ]);

} catch (Exception $e) {
    $conn->rollback();
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'db_error']);
}
?>