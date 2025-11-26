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

// 1. QUERY PRINCIPAL (VIDEO)
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
    if ($check_sub && $check_sub->num_rows > 0) $is_subscribed = true;

    $check_rate = $conn->query("SELECT type FROM likes WHERE user_id = $current_user_id AND video_id = $video_id");
    if ($check_rate && $row = $check_rate->fetch_assoc()) $user_rating = $row['type'];
}

// 3. CONTEO DE LIKES
$likes_query = $conn->query("SELECT COUNT(*) as c FROM likes WHERE video_id = $video_id AND type = 'like'");
$likes_count = $likes_query ? $likes_query->fetch_assoc()['c'] : 0;

// 4. QUERY COMENTARIOS (NUEVO)
$sql_comments = "SELECT c.*, u.username, u.avatar 
                 FROM comments c 
                 JOIN users u ON c.user_id = u.id 
                 WHERE c.video_id = $video_id 
                 ORDER BY c.created_at DESC";
$comments_result = $conn->query($sql_comments);
$total_comments = $comments_result ? $comments_result->num_rows : 0;

// 5. QUERY RELACIONADOS
$sql_related = "SELECT v.*, c.name AS channel_name 
                FROM videos v 
                JOIN channels c ON v.channel_id = c.id 
                WHERE v.id != $video_id 
                ORDER BY RAND() LIMIT 10";
$related_result = $conn->query($sql_related);

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/watch.css">

<div class="container-fluid" style="padding-top: 24px; max-width: 1850px;">
    <div class="row">
        
        <!-- COLUMNA IZQUIERDA (VIDEO + COMENTARIOS) -->
        <div class="col s12 l9">
            <div class="video-container-wrapper">
                <iframe id="mainVideoPlayer" src="https://www.youtube.com/embed/<?php echo $video['youtube_id']; ?>?autoplay=1&rel=0&enablejsapi=1" title="Video player" allowfullscreen></iframe>
            </div>

            <h1 class="video-watch-title"><?php echo $video['title']; ?></h1>

            <!-- ACCIONES DEL VIDEO -->
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
                    <button id="shareBtn" class="btn-action waves-effect"><i class="material-icons">share</i> Compartir</button>
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
            
            <div class="divider" style="margin: 24px 0;"></div>
            
            <!-- SECCIÓN DE COMENTARIOS -->
            <div class="comments-section">
                
                <h5 class="comments-count-title"><?php echo number_format($total_comments); ?> comentarios</h5>
                
                <!-- Input para nuevo comentario -->
                <div class="add-comment-row">
                    <div class="current-user-avatar">
                        <?php 
                            $myAvatar = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : 'default.png';
                            if ($myAvatar === 'default.png' && isset($_SESSION['username'])) {
                                $myAvatarSrc = "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['username']) . "&background=random&color=fff&size=64";
                            } else {
                                $myAvatarSrc = ($myAvatar === 'default.png') ? BASE_URL . 'assets/img/default-user.png' : BASE_URL . 'uploads/avatars/' . $myAvatar;
                            }
                        ?>
                        <img src="<?php echo $myAvatarSrc; ?>" alt="Mi Avatar">
                    </div>
                    <div class="input-field-comment">
                        <input type="text" placeholder="Agrega un comentario..." id="commentInput">
                        <div class="comment-form-actions">
                            <button class="btn-flat waves-effect">Cancelar</button>
                            <button class="btn-flat waves-effect" disabled id="submitCommentBtn">Comentar</button>
                        </div>
                    </div>
                </div>

                <!-- Lista de Comentarios -->
                <div class="comments-list">
                    <?php if ($total_comments > 0): ?>
                        <?php while($comment = $comments_result->fetch_assoc()): ?>
                            <?php 
                                // Preparar avatar del comentarista
                                $cAvatar = $comment['avatar'];
                                if ($cAvatar === 'default.png') {
                                    $cAvatarSrc = "https://ui-avatars.com/api/?name=" . urlencode($comment['username']) . "&background=random&color=fff&size=64";
                                } else {
                                    $cAvatarSrc = BASE_URL . 'uploads/avatars/' . $cAvatar;
                                }
                            ?>
                            <div class="comment-item">
                                <a href="#!" class="comment-avatar-link">
                                    <img src="<?php echo $cAvatarSrc; ?>" alt="<?php echo $comment['username']; ?>">
                                </a>
                                <div class="comment-body">
                                    <div class="comment-header">
                                        <span class="author-name"><?php echo $comment['username']; ?></span>
                                        <span class="comment-time"><?php echo timeAgo($comment['created_at']); ?></span>
                                    </div>
                                    <div class="comment-content">
                                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                    </div>
                                    <div class="comment-actions-toolbar">
                                        <button class="btn-icon-comment like-comment">
                                            <i class="material-icons">thumb_up_alt</i>
                                            <span class="count-text"><?php echo ($comment['likes'] > 0) ? $comment['likes'] : ''; ?></span>
                                        </button>
                                        <button class="btn-icon-comment dislike-comment">
                                            <i class="material-icons">thumb_down_alt</i>
                                        </button>
                                        <button class="btn-reply-text">Responder</button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="grey-text center-align" style="margin-top: 40px;">Sé el primero en comentar.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- COLUMNA DERECHA (RELACIONADOS) -->
        <div class="col s12 l3">
            <?php while($related = $related_result->fetch_assoc()): ?>
                <?php include 'includes/components/video_card_small.php'; ?>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- MODALES (Suscribirse, Compartir) -->
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

<div id="shareModal" class="modal share-modal">
    <div class="modal-content">
        <div class="share-header">
            <h5>Compartir</h5>
            <i class="material-icons modal-close">close</i>
        </div>
        <!-- (Contenido del modal de compartir igual al anterior...) -->
        <div class="copy-link-wrapper">
            <div class="input-container">
                <input type="text" id="shareUrlInput" value="<?php echo BASE_URL . 'watch.php?id=' . $video_id; ?>" readonly>
                <button id="copyLinkBtn">Copiar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/watch.js"></script>

<?php require_once 'includes/footer.php'; ?>