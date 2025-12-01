<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_layout = 'guide'; 
$APP_NAME = "Ver más tarde";

// OBTENER ID DE LA LISTA "Ver más tarde"
$playlist_query = "SELECT id, title, created_at FROM playlists WHERE user_id = ? AND title = 'Ver más tarde' LIMIT 1";
$stmt_p = $conn->prepare($playlist_query);
$stmt_p->bind_param("i", $user_id);
$stmt_p->execute();
$res_p = $stmt_p->get_result();
$playlist = $res_p->fetch_assoc();

$playlist_id = $playlist ? $playlist['id'] : 0;
$result = null;

// SI EXISTE LA LISTA, OBTENER VIDEOS
if ($playlist_id > 0) {
    $sql = "SELECT pv.id as item_id, pv.position,
                   v.id as video_id, v.title, v.description, v.thumbnail_url, v.views, v.duration, v.created_at as video_date,
                   c.name as channel_name, c.id as channel_id
            FROM playlist_videos pv
            JOIN videos v ON pv.video_id = v.id
            JOIN channels c ON v.channel_id = c.id
            WHERE pv.playlist_id = ?
            ORDER BY pv.position ASC, pv.id DESC"; // Orden por posición o agregado reciente

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $playlist_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

require_once 'includes/header.php';
?>

<!-- Reutilizamos estilos de historial -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/history.css">

<div class="container-fluid history-page-layout">
    
    <!-- COLUMNA PRINCIPAL -->
    <div class="history-main-content">
        
        <h1 class="history-page-title">Ver más tarde</h1>
        
        <!-- Info de la lista -->
        <div class="liked-playlist-info" style="margin-bottom: 32px; display: flex; align-items: center; gap: 16px;">
            <?php if ($result && $result->num_rows > 0): 
                // Mostrar miniatura del primer video
                $result->data_seek(0); 
                $first = $result->fetch_assoc();
                $cover = !empty($first['thumbnail_url']) ? $first['thumbnail_url'] : "https://img.youtube.com/vi/placeholder/mqdefault.jpg";
                $result->data_seek(0); // Resetear puntero
            ?>
                <div style="width: 120px; height: 68px; border-radius: 8px; overflow: hidden; background: #000;">
                    <img src="<?php echo $cover; ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                         <i class="material-icons white-text">watch_later</i>
                    </div>
                </div>
                <div>
                    <h6 style="margin: 0; font-weight: 600;"><?php echo $result->num_rows; ?> videos</h6>
                    <span style="color: #606060; font-size: 13px;">Actualizado hoy</span>
                </div>
            <?php else: ?>
                 <div style="width: 120px; height: 68px; border-radius: 8px; background: #e5e5e5; display: flex; align-items: center; justify-content: center;">
                     <i class="material-icons grey-text">watch_later</i>
                 </div>
                 <div>
                    <h6 style="margin: 0; font-weight: 600;">0 videos</h6>
                 </div>
            <?php endif; ?>
        </div>

        <div class="history-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($item = $result->fetch_assoc()): ?>
                    <div class="history-item">
                        <!-- Miniatura -->
                        <a href="watch.php?id=<?php echo $item['video_id']; ?>" class="history-thumbnail-wrapper">
                            <?php 
                                $thumb = !empty($item['thumbnail_url']) ? $item['thumbnail_url'] : "https://img.youtube.com/vi/placeholder/mqdefault.jpg";
                            ?>
                            <img src="<?php echo $thumb; ?>" alt="Miniatura">
                            <span class="duration-badge"><?php echo formatDuration($item['duration']); ?></span>
                        </a>

                        <!-- Info -->
                        <div class="history-info">
                            <div class="history-meta">
                                <a href="watch.php?id=<?php echo $item['video_id']; ?>" class="history-title">
                                    <?php echo $item['title']; ?>
                                </a>
                                <div class="history-channel-row">
                                    <a href="channel.php?id=<?php echo $item['channel_id']; ?>" class="channel-link">
                                        <?php echo $item['channel_name']; ?>
                                    </a>
                                    <span class="dot">•</span>
                                    <span><?php echo number_format($item['views']); ?> vistas</span>
                                </div>
                                <div class="history-description">
                                    <?php echo substr(strip_tags($item['description']), 0, 140) . '...'; ?>
                                </div>
                            </div>
                            
                            <!-- Menú de opciones -->
                            <div class="history-menu-wrapper">
                                <button class="btn-history-menu">
                                    <i class="material-icons">more_vert</i>
                                </button>
                                <div class="history-dropdown">
                                    <div class="menu-option" onclick="M.toast({html: 'Añadido a la cola'})"><i class="material-icons">playlist_play</i> Añadir a la cola</div>
                                    <div class="menu-option" onclick="M.toast({html: 'Compartir'})"><i class="material-icons">share</i> Compartir</div>
                                    <div class="divider"></div>
                                    <!-- Botón Eliminar con datos para JS -->
                                    <div class="menu-option btn-remove-wl" 
                                         data-video-id="<?php echo $item['video_id']; ?>" 
                                         data-playlist-id="<?php echo $playlist_id; ?>">
                                        <i class="material-icons">delete</i> Quitar de Ver más tarde
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-history" style="text-align: center; padding: 60px;">
                    <i class="material-icons" style="font-size: 80px; color: #e0e0e0;">watch_later</i>
                    <h6 style="margin-top: 20px;">Tu lista está vacía</h6>
                    <p>Guarda videos para verlos más tarde.</p>
                    <a href="index.php" class="btn blue darken-3">Explorar videos</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Sidebar derecho (Vacío o con sugerencias) -->
    <div class="history-sidebar"></div>

</div>

<!-- Lógica JS Inline para eliminar de la lista -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Delegación para menú
    const list = document.querySelector('.history-list');
    if (list) {
        list.addEventListener('click', function(e) {
            // Abrir menú
            const menuBtn = e.target.closest('.btn-history-menu');
            if (menuBtn) {
                e.preventDefault(); e.stopPropagation();
                const wrapper = menuBtn.closest('.history-menu-wrapper');
                document.querySelectorAll('.history-menu-wrapper.active').forEach(el => { if(el!==wrapper) el.classList.remove('active'); });
                wrapper.classList.toggle('active');
            }
            
            // Eliminar de Ver más tarde
            const removeBtn = e.target.closest('.btn-remove-wl');
            if (removeBtn) {
                e.preventDefault(); e.stopPropagation();
                const videoId = removeBtn.getAttribute('data-video-id');
                const playlistId = removeBtn.getAttribute('data-playlist-id');
                const wrapper = removeBtn.closest('.history-menu-wrapper');
                wrapper.classList.remove('active');
                
                removeFromWatchLater(playlistId, videoId, removeBtn);
            }
        });
    }

    // Cerrar al clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.history-menu-wrapper')) {
            document.querySelectorAll('.history-menu-wrapper.active').forEach(el => el.classList.remove('active'));
        }
    });

    function removeFromWatchLater(playlistId, videoId, btnElement) {
        // Reutilizamos actions/playlist.php que ya tiene lógica para quitar videos
        fetch('actions/playlist.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ 
                action: 'toggle_video', 
                playlist_id: playlistId, 
                video_id: videoId, 
                add: false // false = Eliminar
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const item = btnElement.closest('.history-item');
                item.style.transition = 'opacity 0.3s, transform 0.3s';
                item.style.opacity = '0';
                item.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    item.remove();
                    // Si no quedan items, recargar para mostrar estado vacío
                    if (!document.querySelector('.history-item')) location.reload();
                }, 300);
                M.toast({html: 'Quitado de Ver más tarde'});
            } else {
                M.toast({html: 'Error al eliminar'});
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>