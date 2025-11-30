<?php
// channel.php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$channel_id = (int)$_GET['id'];
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// 1. OBTENER INFORMACIÓN DEL CANAL
$sql = "SELECT c.*, u.username, u.avatar 
        FROM channels c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $channel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php"); // Canal no encontrado
    exit();
}
$channel = $result->fetch_assoc();

// 2. OBTENER VIDEOS DEL CANAL
$sql_videos = "SELECT * FROM videos WHERE channel_id = ? ORDER BY created_at DESC";
$stmt_v = $conn->prepare($sql_videos);
$stmt_v->bind_param("i", $channel_id);
$stmt_v->execute();
$videos_result = $stmt_v->get_result();

// 3. VERIFICAR SUSCRIPCIÓN
$is_subscribed = false;
if ($current_user_id > 0) {
    $check_sub = $conn->query("SELECT id FROM subscriptions WHERE user_id = $current_user_id AND channel_id = $channel_id");
    if ($check_sub->num_rows > 0) $is_subscribed = true;
}

$page_layout = 'guide';
$APP_NAME = $channel['name'];

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/home.css"> <!-- Reutilizamos grid -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/channel.css">

<div class="container-fluid channel-page-container">
    
    <!-- BANNER DEL CANAL -->
    <div class="channel-banner">
        <!-- Si hubiera banner en DB se mostraría aquí, si no, un degradado por defecto -->
    </div>

    <!-- CABECERA DEL CANAL -->
    <div class="channel-header">
        <div class="channel-header-left">
            <!-- Avatar -->
            <div class="channel-page-avatar">
                <?php 
                    $cAvatar = $channel['avatar'];
                    if ($cAvatar === 'default.png') {
                        $cAvatarSrc = "https://ui-avatars.com/api/?name=" . urlencode($channel['name']) . "&background=random&color=fff&size=128";
                    } else {
                        $cAvatarSrc = BASE_URL . 'uploads/avatars/' . $cAvatar;
                    }
                ?>
                <img src="<?php echo $cAvatarSrc; ?>" alt="<?php echo $channel['name']; ?>">
            </div>
            
            <!-- Info Texto -->
            <div class="channel-page-info">
                <h1><?php echo $channel['name']; ?></h1>
                <div class="channel-meta-text">
                    <span>@<?php echo str_replace(' ', '', $channel['name']); ?></span> • 
                    <span id="subscribersCount"><?php echo number_format($channel['subscribers_count']); ?> suscriptores</span> • 
                    <span><?php echo $channel['total_videos']; ?> videos</span>
                </div>
                <div class="channel-description">
                    <?php echo nl2br(htmlspecialchars($channel['description'])); ?>
                </div>
                
                <!-- Botón Suscribirse -->
                <div class="channel-actions">
                    <?php if ($channel['user_id'] != $current_user_id): ?>
                        <button id="subscribeBtn" 
                                class="btn-subscribe-channel <?php echo $is_subscribed ? 'subscribed' : ''; ?>" 
                                data-channel-id="<?php echo $channel['id']; ?>">
                            <?php echo $is_subscribed ? 'Suscrito' : 'Suscribirse'; ?>
                        </button>
                    <?php else: ?>
                         <button class="btn-subscribe-channel disabled" disabled>Es tu canal</button>
                         <a href="upload.php" class="btn-flat waves-effect">Gestionar videos</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑAS (Visuales) -->
    <div class="channel-tabs">
        <div class="tab active">Videos</div>
        <div class="tab">Listas</div>
        <div class="tab">Comunidad</div>
        <div class="tab">Acerca de</div>
    </div>
    <div class="divider" style="margin: 0;"></div>

    <!-- LISTA DE VIDEOS -->
    <div class="channel-content">
        <h5 style="font-weight: 600; font-size: 18px; margin-top: 24px; margin-bottom: 16px;">Videos subidos</h5>
        
        <?php if ($videos_result && $videos_result->num_rows > 0): ?>
            <div class="video-grid">
                <?php while($video = $videos_result->fetch_assoc()): ?>
                    <?php 
                        // Preparamos datos para reutilizar el componente video_card.php
                        // IMPORTANTE: video_card.php espera que $video tenga 'channel_name' y 'avatar' (del usuario)
                        // Como ya tenemos esos datos del canal, los inyectamos en el array $video
                        $video['channel_name'] = $channel['name'];
                        // Nota: video_card usa el avatar para mostrarlo abajo, aunque en la página del canal es redundante, no rompe nada.
                    ?>
                    <?php include 'includes/components/video_card.php'; ?>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-channel">
                <p>Este canal aún no tiene videos.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- MODAL DESUSCRIPCIÓN -->
<div id="unsubscribeModal" class="modal">
    <div class="modal-content">
        <h4>¿Quieres anular tu suscripción?</h4>
        <p>¿Seguro que quieres anular tu suscripción a <strong><?php echo $channel['name']; ?></strong>?</p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect btn-flat">Cancelar</a>
        <a href="#!" id="confirmUnsubscribe" class="waves-effect btn-flat blue-text text-darken-3">Anular suscripción</a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/channel.js"></script>

<?php require_once 'includes/footer.php'; ?>