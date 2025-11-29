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

// 5. QUERY COMENTARIOS (JERÁRQUICOS)
$sql_comments = "SELECT c.*, u.username, u.avatar 
                 FROM comments c 
                 JOIN users u ON c.user_id = u.id 
                 WHERE c.video_id = $video_id 
                 ORDER BY c.created_at DESC";
$comments_result = $conn->query($sql_comments);
$total_comments = $comments_result ? $comments_result->num_rows : 0;

$comments_by_parent = [];
if ($total_comments > 0) {
    while ($row = $comments_result->fetch_assoc()) {
        $parentId = $row['parent_id'] ? $row['parent_id'] : 0;
        $comments_by_parent[$parentId][] = $row;
    }
}

// FUNCIÓN RECURSIVA PARA RENDERIZAR
function renderComments($parentId = 0, $level = 0, $comments_by_parent) {
    global $current_user_id;

    if (!isset($comments_by_parent[$parentId])) return;

    foreach ($comments_by_parent[$parentId] as $comment) {
        $cAvatar = $comment['avatar'];
        if ($cAvatar === 'default.png') {
            $cAvatarSrc = "https://ui-avatars.com/api/?name=" . urlencode($comment['username']) . "&background=random&color=fff&size=64";
        } else {
            $cAvatarSrc = BASE_URL . 'uploads/avatars/' . $cAvatar;
        }
        
        $marginLeft = ($level > 0) ? '48px' : '0'; 
        
        // Verificar si el usuario actual es el dueño del comentario
        $isOwner = ($current_user_id > 0 && $current_user_id == $comment['user_id']);
        ?>
        <div class="comment-item" id="comment-<?php echo $comment['id']; ?>" style="margin-left: <?php echo $marginLeft; ?>;">
            <a href="#!" class="comment-avatar-link">
                <img src="<?php echo $cAvatarSrc; ?>" alt="<?php echo $comment['username']; ?>">
            </a>
            <div class="comment-body">
                <!-- Header con flex para el botón de menú -->
                <div class="comment-header-row">
                    <div class="comment-meta">
                        <span class="author-name"><?php echo $comment['username']; ?></span>
                        <span class="comment-time"><?php echo timeAgo($comment['created_at']); ?></span>
                    </div>
                    
                    <!-- MENÚ DE OPCIONES (Solo si es dueño) -->
                    <?php if ($isOwner): ?>
                        <div class="comment-menu-wrapper">
                            <button class="btn-icon-comment btn-comment-menu">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <!-- Dropdown oculto -->
                            <div class="comment-dropdown-menu">
                                <div class="menu-option btn-edit-comment" data-id="<?php echo $comment['id']; ?>">
                                    <i class="material-icons">edit</i> Editar
                                </div>
                                <div class="menu-option btn-delete-comment" data-id="<?php echo $comment['id']; ?>">
                                    <i class="material-icons">delete</i> Eliminar
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- CONTENIDO DEL COMENTARIO -->
                <!-- IMPORTANTE: Todo pegado para evitar espacios extra. Sin nl2br() para evitar doble salto -->
                <div class="comment-content" id="comment-content-<?php echo $comment['id']; ?>"><?php echo htmlspecialchars($comment['content']); ?></div>
                
                <div class="comment-actions-toolbar">
                    <button class="btn-icon-comment like-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">
                        <i class="material-icons">thumb_up_alt</i>
                        <span class="count-text"><?php echo ($comment['likes'] > 0) ? $comment['likes'] : ''; ?></span>
                    </button>
                    <button class="btn-icon-comment dislike-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">
                        <i class="material-icons">thumb_down_alt</i>
                    </button>
                    
                    <?php if ($level < 2): ?>
                        <button class="btn-reply-text" data-parent-id="<?php echo $comment['id']; ?>">Responder</button>
                    <?php endif; ?>
                </div>

                <div class="reply-form-container" id="reply-form-<?php echo $comment['id']; ?>"></div>
            </div>
        </div>
        <?php
        renderComments($comment['id'], $level + 1, $comments_by_parent);
    }
}

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/watch.css">

<div class="container-fluid" style="padding-top: 24px; max-width: 1850px;">
    <div class="row">
        <!-- COLUMNA IZQUIERDA -->
        <div class="col s12 l9">
            <div class="video-container-wrapper">
                <iframe id="mainVideoPlayer" src="https://www.youtube.com/embed/<?php echo $video['youtube_id']; ?>?autoplay=1&rel=0&enablejsapi=1" title="Video player" allowfullscreen></iframe>
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
                        <span id="likeCount" style="padding-right: 8px; border-right: 1px solid #ccc; margin-right: 8px;"><?php echo $likes_count; ?></span>
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
                        <!-- Descripción también sin nl2br() para coherencia con pre-wrap -->
                        <div class="description-text"><?php echo htmlspecialchars($video['description']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="divider" style="margin: 24px 0;"></div>
            
            <div class="comments-section">
                <h5 class="comments-count-title"><?php echo number_format($total_comments); ?> comentarios</h5>
                
                <div class="add-comment-row main-comment-form">
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
                        <textarea id="commentInput" class="materialize-textarea" placeholder="Agrega un comentario..."></textarea>
                        <div class="comment-form-actions">
                            <button class="btn-flat waves-effect">Cancelar</button>
                            <button class="btn-flat waves-effect" disabled id="submitCommentBtn">Comentar</button>
                        </div>
                    </div>
                </div>

                <div class="comments-list">
                    <?php if ($total_comments > 0): ?>
                        <?php renderComments(0, 0, $comments_by_parent); ?>
                    <?php else: ?>
                        <p class="grey-text center-align" style="margin-top: 40px;">Sé el primero en comentar.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div class="col s12 l3">
            <?php while($related = $related_result->fetch_assoc()): ?>
                <?php include 'includes/components/video_card_small.php'; ?>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php // Modales (Suscribir, Compartir) ?>
<div id="unsubscribeModal" class="modal"> <div class="modal-content"><h4>¿Quieres anular tu suscripción?</h4><p>¿Seguro que quieres anular tu suscripción a <strong><?php echo $video['channel_name']; ?></strong>?</p></div><div class="modal-footer"><a href="#!" class="modal-close waves-effect btn-flat">Cancelar</a><a href="#!" id="confirmUnsubscribe" class="waves-effect btn-flat blue-text text-darken-3">Anular suscripción</a></div></div>
<div id="shareModal" class="modal share-modal"><div class="modal-content"><div class="share-header"><h5>Compartir</h5><i class="material-icons modal-close">close</i></div><div class="social-row"><a href="https://wa.me/?text=<?php echo urlencode(BASE_URL . 'watch.php?id=' . $video_id); ?>" target="_blank" class="social-icon" title="WhatsApp"><div class="icon-circle whatsapp"><svg viewBox="0 0 24 24" width="24" height="24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></div><span>WhatsApp</span></a><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BASE_URL . 'watch.php?id=' . $video_id); ?>" target="_blank" class="social-icon" title="Facebook"><div class="icon-circle facebook"><svg viewBox="0 0 24 24" width="24" height="24" fill="white"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></div><span>Facebook</span></a><a href="https://twitter.com/intent/tweet?text=Mira este video&url=<?php echo urlencode(BASE_URL . 'watch.php?id=' . $video_id); ?>" target="_blank" class="social-icon" title="X / Twitter"><div class="icon-circle twitter"><svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></div><span>X</span></a><a href="mailto:?subject=Mira este video&body=<?php echo urlencode(BASE_URL . 'watch.php?id=' . $video_id); ?>" target="_blank" class="social-icon" title="Email"><div class="icon-circle email"><i class="material-icons" style="font-size: 24px; color: white;">email</i></div><span>Email</span></a></div><div class="copy-link-wrapper"><div class="input-container"><input type="text" id="shareUrlInput" value="<?php echo BASE_URL . 'watch.php?id=' . $video_id; ?>" readonly><button id="copyLinkBtn">Copiar</button></div></div><div class="start-at-wrapper"><label><input type="checkbox" id="startAtCheckbox" /><span>Empezar en <span id="currentTimeDisplay">0:00</span></span></label></div></div></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/watch.js"></script>

<?php require_once 'includes/footer.php'; ?>