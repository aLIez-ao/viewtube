<?php
// history.php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_layout = 'guide'; // Usa el sidebar flotante/fijo estándar

// QUERY: Obtener historial SIN DUPLICADOS (Solo la última vez que se vio cada video)
// Usamos una subconsulta para obtener el ID más reciente de historial para cada video de este usuario
$sql = "SELECT h.id as history_id, h.last_watched_at, 
               v.id as video_id, v.title, v.description, v.thumbnail_url, v.views, v.duration,
               c.name as channel_name, c.id as channel_id
        FROM history h
        JOIN videos v ON h.video_id = v.id
        JOIN channels c ON v.channel_id = c.id
        WHERE h.id IN (
            SELECT MAX(id) 
            FROM history 
            WHERE user_id = $user_id 
            GROUP BY video_id
        )
        ORDER BY h.last_watched_at DESC";

$result = $conn->query($sql);

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/history.css">

<div class="container-fluid history-container">
    <div class="history-header">
        <h5>Historial de reproducciones</h5>
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
                                <a href="#!" class="channel-link"><?php echo $item['channel_name']; ?></a>
                                <span class="dot">•</span>
                                <span><?php echo number_format($item['views']); ?> vistas</span>
                            </div>
                            <div class="history-description">
                                <?php echo substr(strip_tags($item['description']), 0, 160) . '...'; ?>
                            </div>
                        </div>
                        
                        <!-- Botón X para eliminar -->
                        <button class="btn-remove-history tooltipped" data-position="left" data-tooltip="Quitar del historial">
                            <i class="material-icons">close</i>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-history">
                <i class="material-icons">history</i>
                <h6>No hay videos en tu historial</h6>
                <p>Los videos que veas aparecerán aquí.</p>
                <a href="index.php" class="btn blue darken-3">Ir al Inicio</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('.tooltipped');
        var instances = M.Tooltip.init(elems);
    });
</script>

<?php require_once 'includes/footer.php'; ?>