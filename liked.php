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
$APP_NAME = "Videos que me gustan";

// QUERY: Obtener videos con LIKE del usuario
$sql = "SELECT l.id as like_id, l.created_at as liked_at, 
               v.id as video_id, v.title, v.description, v.thumbnail_url, v.views, v.duration,
               c.name as channel_name, c.id as channel_id
        FROM likes l
        JOIN videos v ON l.video_id = v.id
        JOIN channels c ON v.channel_id = c.id
        WHERE l.user_id = ? AND l.type = 'like'
        ORDER BY l.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

require_once 'includes/header.php';
?>

<!-- Reutilizamos estilos de historial para la lista horizontal -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/history.css">

<div class="container-fluid history-page-layout">
    
    <!-- COLUMNA PRINCIPAL -->
    <div class="history-main-content">
        
        <h1 class="history-page-title">Videos que me gustan</h1>
        
        <!-- Info de la lista (simulando playlist) -->
        <div class="liked-playlist-info" style="margin-bottom: 32px; display: flex; align-items: center; gap: 16px;">
            <?php if ($result && $result->num_rows > 0): 
                // Obtenemos la primera imagen para mostrarla como portada pequeña
                $result->data_seek(0); 
                $first = $result->fetch_assoc();
                $cover = !empty($first['thumbnail_url']) ? $first['thumbnail_url'] : "https://img.youtube.com/vi/placeholder/mqdefault.jpg";
                $result->data_seek(0); // Reset pointer
            ?>
                <div style="width: 120px; height: 68px; border-radius: 8px; overflow: hidden;">
                    <img src="<?php echo $cover; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div>
                    <h6 style="margin: 0; font-weight: 600;"><?php echo $result->num_rows; ?> videos</h6>
                    <span style="color: #606060; font-size: 13px;">No listado</span>
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
                                    <!-- Mostrar fecha en que dio like en lugar de descripción larga -->
                                    Añadido el <?php echo date("d M Y", strtotime($item['liked_at'])); ?>
                                </div>
                            </div>
                            
                            <!-- Menú de opciones (Reutilizando clases de history para el botón) -->
                            <div class="history-menu-wrapper">
                                <button class="btn-history-menu">
                                    <i class="material-icons">more_vert</i>
                                </button>
                                <div class="history-dropdown">
                                    <div class="menu-option" onclick="M.toast({html: 'Añadido a la cola'})"><i class="material-icons">playlist_play</i> Añadir a la cola</div>
                                    <div class="menu-option" onclick="M.toast({html: 'Guardado para ver más tarde'})"><i class="material-icons">watch_later</i> Ver más tarde</div>
                                    <div class="divider"></div>
                                    <div class="menu-option btn-remove-like" data-video-id="<?php echo $item['video_id']; ?>">
                                        <i class="material-icons">delete</i> Quitar de videos que me gustan
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-history" style="text-align: center; padding: 60px;">
                    <i class="material-icons" style="font-size: 80px; color: #e0e0e0;">thumb_up_alt</i>
                    <h6>No te ha gustado ningún video todavía</h6>
                    <p>Los videos a los que des "Me gusta" aparecerán aquí.</p>
                    <a href="index.php" class="btn blue darken-3">Explorar videos</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Reutilizamos el sidebar derecho vacío o con info extra -->
    <div class="history-sidebar">
        <!-- Podríamos poner estadísticas aquí -->
    </div>

</div>

<!-- JS para el menú de opciones (Reutilizamos history.js o creamos uno específico si la lógica cambia mucho) -->
<!-- Para simplificar, inyectamos script inline específico para esta página -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Delegación para menú de 3 puntos (Copia de history.js simplificada)
    const list = document.querySelector('.history-list');
    if (list) {
        list.addEventListener('click', function(e) {
            const menuBtn = e.target.closest('.btn-history-menu');
            if (menuBtn) {
                e.preventDefault(); e.stopPropagation();
                const wrapper = menuBtn.closest('.history-menu-wrapper');
                document.querySelectorAll('.history-menu-wrapper.active').forEach(el => { if(el!==wrapper) el.classList.remove('active'); });
                wrapper.classList.toggle('active');
            }
            
            // Quitar Like
            const removeBtn = e.target.closest('.btn-remove-like');
            if (removeBtn) {
                e.preventDefault(); e.stopPropagation();
                const vid = removeBtn.getAttribute('data-video-id');
                removeLike(vid, removeBtn);
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.history-menu-wrapper')) {
            document.querySelectorAll('.history-menu-wrapper.active').forEach(el => el.classList.remove('active'));
        }
    });

    function removeLike(videoId, btn) {
        // Usamos rate_video.php enviando 'like' de nuevo (toggle) o podríamos crear un remove específico
        // Pero rate_video.php ya hace toggle si existe, así que al mandarlo de nuevo se borrará.
        fetch('actions/rate_video.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ video_id: videoId, type: 'like' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.action === 'removed') {
                const item = btn.closest('.history-item');
                item.style.transition = 'opacity 0.3s';
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
                M.toast({html: 'Quitado de videos que me gustan'});
            } else {
                M.toast({html: 'Error al quitar'});
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>