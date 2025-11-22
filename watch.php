<?php
// watch.php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$video_id = (int)$_GET['id'];
$page_layout = 'watch';
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// 1. QUERY PRINCIPAL
$sql = "SELECT v.*, c.id AS channel_id, c.name AS channel_name, c.subscribers_count, u.avatar, u.username 
        FROM videos v 
        JOIN channels c ON v.channel_id = c.id 
        JOIN users u ON c.user_id = u.id 
        WHERE v.id = $video_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: construction.php");
    exit();
}
$video = $result->fetch_assoc();

// 2. QUERY ESTADOS DEL USUARIO
$is_subscribed = false;
$user_rating = 'none';

if ($current_user_id > 0) {
    $check_sub = $conn->query("SELECT id FROM subscriptions WHERE user_id = $current_user_id AND channel_id = {$video['channel_id']}");
    if ($check_sub->num_rows > 0) $is_subscribed = true;

    $check_rate = $conn->query("SELECT type FROM likes WHERE user_id = $current_user_id AND video_id = $video_id");
    if ($row = $check_rate->fetch_assoc()) $user_rating = $row['type'];
}

// 3. CONTEO DE LIKES
$likes_count = $conn->query("SELECT COUNT(*) as c FROM likes WHERE video_id = $video_id AND type = 'like'")->fetch_assoc()['c'];

// 4. QUERY RELACIONADOS
$sql_related = "SELECT v.*, c.name AS channel_name 
                FROM videos v 
                JOIN channels c ON v.channel_id = c.id 
                WHERE v.id != $video_id 
                ORDER BY RAND() LIMIT 10";
$related_result = $conn->query($sql_related);

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/watch.css">

<!-- Aumentamos max-width a 1850px para aprovechar pantallas grandes -->
<div class="container-fluid" style="padding-top: 24px; max-width: 1850px;">
    <div class="row">
        
        <!-- COLUMNA IZQUIERDA (VIDEO) - AUMENTADA A l9 (75%) -->
        <div class="col s12 l9">
            <div class="video-container-wrapper">
                <iframe src="https://www.youtube.com/embed/<?php echo $video['youtube_id']; ?>?autoplay=1&rel=0" title="Video player" allowfullscreen></iframe>
            </div>

            <h1 class="video-watch-title"><?php echo $video['title']; ?></h1>

            <div class="video-actions-row">
                
                <div class="channel-info-block">
                    <div class="channel-avatar-watch">
                         <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($video['channel_name']); ?>&background=random&color=fff&size=64" alt="Avatar">
                    </div>
                    <div class="channel-text">
                        <a href="#!" style="color: inherit;"><h6><?php echo $video['channel_name']; ?></h6></a>
                        <span id="subscribersCount"><?php echo number_format($video['subscribers_count']); ?> suscriptores</span>
                    </div>
                    
                    <?php if ($video['channel_id'] != $current_user_id): ?>
                        <button id="subscribeBtn" 
                                class="btn btn-subscribe waves-effect waves-light <?php echo $is_subscribed ? 'subscribed' : ''; ?>" 
                                data-channel-id="<?php echo $video['channel_id']; ?>">
                            <?php echo $is_subscribed ? 'Suscrito' : 'Suscribirse'; ?>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="actions-buttons">
                    <button id="likeBtn" class="btn-action waves-effect <?php echo ($user_rating == 'like') ? 'active' : ''; ?>" data-video-id="<?php echo $video_id; ?>">
                        <i class="material-icons"><?php echo ($user_rating == 'like') ? 'thumb_up' : 'thumb_up_alt'; ?></i> 
                        <span id="likeCount" style="padding-right: 8px; border-right: 1px solid #ccc; margin-right: 8px;">
                            <?php echo $likes_count; ?>
                        </span>
                    </button>
                    
                    <button id="dislikeBtn" class="btn-action waves-effect <?php echo ($user_rating == 'dislike') ? 'active' : ''; ?>" data-video-id="<?php echo $video_id; ?>">
                        <i class="material-icons"><?php echo ($user_rating == 'dislike') ? 'thumb_down' : 'thumb_down_alt'; ?></i>
                    </button>
                    
                    <button class="btn-action waves-effect"><i class="material-icons">share</i> Compartir</button>
                    <button class="btn-action waves-effect hide-on-small-only"><i class="material-icons">playlist_add</i> Guardar</button>
                    <button class="btn-action btn-circle waves-effect"><i class="material-icons">more_horiz</i></button>
                </div>
            </div>

            <div class="description-box">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12">
                        <strong><?php echo number_format($video['views']); ?> vistas • <?php echo timeAgo($video['created_at']); ?></strong>
                        <div class="description-text"><?php echo nl2br($video['description']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="divider" style="margin: 20px 0;"></div>
            <h5>Comentarios</h5>
            <p class="grey-text">Los comentarios se están cargando...</p>
        </div>

        <!-- COLUMNA DERECHA (RELACIONADOS) - REDUCIDA A l3 (25%) -->
        <div class="col s12 l3">
            <?php while($related = $related_result->fetch_assoc()): ?>
                <?php include 'includes/components/video_card_small.php'; ?>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div id="unsubscribeModal" class="modal">
    <div class="modal-content">
        <h4>¿Quieres anular tu suscripción?</h4>
        <p>¿Seguro que quieres anular tu suscripción a <strong><?php echo $video['channel_name']; ?></strong>?</p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect btn-flat">Cancelar</a>
        <a href="#!" id="confirmUnsubscribe" class="waves-effect btn-flat blue-text text-darken-3">Anular suscripción</a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/watch.js"></script>

<?php require_once 'includes/footer.php'; ?>