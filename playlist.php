<?php
// playlist.php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: playlists.php");
    exit();
}

$playlist_id = (int)$_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$page_layout = 'guide'; 

// 1. OBTENER INFO DE LA PLAYLIST
// Unimos con users para saber el creador
$sql_p = "SELECT p.*, u.username, u.avatar 
          FROM playlists p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.id = ?";
$stmt_p = $conn->prepare($sql_p);
$stmt_p->bind_param("i", $playlist_id);
$stmt_p->execute();
$res_p = $stmt_p->get_result();

if ($res_p->num_rows == 0) {
    header("Location: playlists.php"); // No existe
    exit();
}
$playlist = $res_p->fetch_assoc();
$APP_NAME = $playlist['title'];

// Verificar privacidad (si es privada y no es el dueño, fuera)
if ($playlist['is_private'] && $playlist['user_id'] != $user_id) {
    header("Location: index.php"); // Acceso denegado
    exit();
}

// 2. OBTENER VIDEOS DE LA LISTA
$sql_v = "SELECT pv.id as item_id, pv.position,
                 v.id as video_id, v.title, v.description, v.thumbnail_url, v.views, v.duration, v.created_at as video_date,
                 c.name as channel_name, c.id as channel_id
          FROM playlist_videos pv
          JOIN videos v ON pv.video_id = v.id
          JOIN channels c ON v.channel_id = c.id
          WHERE pv.playlist_id = ?
          ORDER BY pv.position ASC, pv.id DESC"; // Ordenados por posición

$stmt_v = $conn->prepare($sql_v);
$stmt_v->bind_param("i", $playlist_id);
$stmt_v->execute();
$result = $stmt_v->get_result();

// Calcular total videos y miniatura de portada
$total_videos = $result->num_rows;
$cover_img = BASE_URL . 'assets/img/no-video.png'; // Default

// Guardamos los resultados en array para poder usar el primero como portada sin perder el puntero
$videos = [];
if ($total_videos > 0) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
    // Usamos el primer video como portada
    $first = $videos[0];
    $cover_img = !empty($first['thumbnail_url']) ? $first['thumbnail_url'] : "https://img.youtube.com/vi/placeholder/mqdefault.jpg";
}

require_once 'includes/header.php';
?>

<!-- Reutilizamos history.css para la lista y añadimos estilos propios -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/history.css">
<style>
    /* Estilos específicos para la cabecera de playlist (Estilo panel izquierdo) */
    .playlist-page-container {
        display: flex;
        padding: 24px 40px;
        gap: 32px;
        max-width: 1280px;
        margin: 0 auto;
    }
    
    /* Panel Izquierdo (Info Playlist) */
    .playlist-sidebar-info {
        width: 360px;
        flex-shrink: 0;
        background: linear-gradient(to bottom, rgba(0,0,0,0.05), transparent);
        padding: 24px;
        border-radius: 12px;
        height: fit-content;
        position: sticky;
        top: 80px; /* Sticky al hacer scroll */
    }
    
    .playlist-cover {
        width: 100%;
        aspect-ratio: 16/9;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .playlist-cover img { width: 100%; height: 100%; object-fit: cover; }
    
    .playlist-title-large {
        font-size: 28px;
        font-weight: 700;
        color: #0f0f0f;
        margin: 0 0 12px 0;
        line-height: 1.2;
    }
    
    .playlist-meta-large {
        font-size: 14px;
        color: #606060;
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 20px;
    }
    .playlist-author { font-weight: 500; color: #0f0f0f; text-decoration: none; }
    
    .playlist-actions {
        display: flex;
        gap: 10px;
    }
    .btn-play-all {
        flex: 1;
        background-color: #0f0f0f;
        color: white;
        border: none;
        border-radius: 18px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-play-all:hover { background-color: #333; }
    .btn-play-all i { margin-right: 8px; }

    /* Lista Derecha */
    .playlist-videos-list {
        flex-grow: 1;
        min-width: 0;
    }
    
    /* Ajuste responsive */
    @media only screen and (max-width: 900px) {
        .playlist-page-container { flex-direction: column; padding: 16px; }
        .playlist-sidebar-info { width: 100%; position: static; margin-bottom: 24px; }
        .playlist-cover { width: 200px; margin: 0 auto 20px; } /* Portada más chica en móvil */
    }
</style>

<div class="container-fluid playlist-page-container">
    
    <!-- PANEL IZQUIERDO: INFO DE LA LISTA -->
    <div class="playlist-sidebar-info">
        <div class="playlist-cover">
            <img src="<?php echo $cover_img; ?>" alt="Portada">
        </div>
        
        <h1 class="playlist-title-large"><?php echo htmlspecialchars($playlist['title']); ?></h1>
        
        <div class="playlist-meta-large">
            <span>
                <?php echo $playlist['is_private'] ? '<i class="material-icons tiny">lock</i> Privada' : 'Pública'; ?>
            </span>
            <span>
                <a href="#!" class="playlist-author"><?php echo $playlist['username']; ?></a>
                • <?php echo $total_videos; ?> videos
            </span>
            <span>Actualizada <?php echo timeAgo($playlist['created_at']); ?></span>
        </div>

        <div class="playlist-actions">
            <?php if ($total_videos > 0): ?>
                <!-- Botón Reproducir Todo (Lleva al primer video) -->
                <a href="watch.php?id=<?php echo $videos[0]['video_id']; ?>" class="btn-play-all waves-effect">
                    <i class="material-icons">play_arrow</i> Reproducir todo
                </a>
            <?php endif; ?>
            
            <!-- Si es el dueño, podría haber botón editar/borrar aquí -->
        </div>
    </div>

    <!-- PANEL DERECHO: LISTA DE VIDEOS -->
    <div class="playlist-videos-list">
        <?php if ($total_videos > 0): ?>
            <?php foreach ($videos as $index => $item): ?>
                <div class="history-item"> <!-- Reutilizamos estilo de item de historial -->
                    
                    <!-- Índice (1, 2, 3...) -->
                    <div style="display: flex; align-items: center; justify-content: center; width: 24px; margin-right: 16px; color: #606060; font-size: 14px;">
                        <?php echo $index + 1; ?>
                    </div>

                    <!-- Miniatura -->
                    <a href="watch.php?id=<?php echo $item['video_id']; ?>" class="history-thumbnail-wrapper" style="width: 160px; min-width: 160px; height: 90px;">
                        <?php 
                            $thumb = !empty($item['thumbnail_url']) ? $item['thumbnail_url'] : "https://img.youtube.com/vi/placeholder/mqdefault.jpg";
                        ?>
                        <img src="<?php echo $thumb; ?>" alt="Miniatura">
                        <span class="duration-badge"><?php echo formatDuration($item['duration']); ?></span>
                    </a>

                    <!-- Info -->
                    <div class="history-info">
                        <div class="history-meta">
                            <a href="watch.php?id=<?php echo $item['video_id']; ?>" class="history-title" style="font-size: 16px;">
                                <?php echo $item['title']; ?>
                            </a>
                            <div class="history-channel-row">
                                <a href="channel.php?id=<?php echo $item['channel_id']; ?>" class="channel-link">
                                    <?php echo $item['channel_name']; ?>
                                </a>
                                <span class="dot">•</span>
                                <span><?php echo number_format($item['views']); ?> vistas</span>
                            </div>
                        </div>
                        
                        <!-- Menú de opciones (3 puntos) -->
                        <!-- Podríamos reutilizar la lógica de history.js si queremos quitar videos -->
                        <button class="btn-remove-history tooltipped" data-position="left" data-tooltip="Opciones">
                            <i class="material-icons">more_vert</i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-history" style="padding: 40px;">
                <p>Esta lista de reproducción no tiene videos todavía.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('.tooltipped');
        if(typeof M !== 'undefined') var instances = M.Tooltip.init(elems);
    });
</script>

<?php require_once 'includes/footer.php'; ?>