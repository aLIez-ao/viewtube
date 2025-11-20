<?php
require_once 'config/db.php';
require_once 'includes/functions.php'; // Agregamos funciones de tiempo/formato

// Validar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$video_id = (int)$_GET['id'];
$page_layout = 'watch';

// 1. QUERY PRINCIPAL (Actualizada para la nueva DB)
// Videos -> Canales -> Usuarios
$sql = "SELECT 
            v.*, 
            c.id AS channel_id,
            c.name AS channel_name,
            c.subscribers_count,
            u.avatar,
            u.username
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

// 2. QUERY RELACIONADOS (Actualizada para la nueva DB)
// Traemos videos distintos al actual, uniendo con canales
$sql_related = "SELECT 
                    v.*, 
                    c.name AS channel_name 
                FROM videos v 
                JOIN channels c ON v.channel_id = c.id 
                WHERE v.id != $video_id 
                ORDER BY RAND() LIMIT 10";

$related_result = $conn->query($sql_related);

// --- INICIO DEL HTML ---
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/watch.css">

<div class="container-fluid" style="padding-top: 24px; max-width: 1600px;">
    <div class="row">
        
        <!-- COLUMNA IZQUIERDA: REPRODUCTOR -->
        <div class="col s12 l8">
            
            <div class="video-container-wrapper">
                <iframe 
                    src="https://www.youtube.com/embed/<?php echo $video['youtube_id']; ?>?autoplay=1&rel=0" 
                    title="YouTube video player" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            </div>

            <h1 class="video-watch-title"><?php echo $video['title']; ?></h1>

            <div class="video-actions-row">
                
                <div class="channel-info-block">
                    <div class="channel-avatar-watch">
                         <!-- Usamos el avatar del usuario dueño del canal -->
                         <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($video['channel_name']); ?>&background=random&color=fff&size=64" alt="Avatar">
                    </div>
                    <div class="channel-text">
                        <a href="#!" style="color: inherit;">
                            <!-- IMPORTANTE: Ahora mostramos channel_name, no username -->
                            <h6><?php echo $video['channel_name']; ?></h6>
                        </a>
                        <!-- Mostramos suscriptores reales de la BD -->
                        <span><?php echo number_format($video['subscribers_count']); ?> suscriptores</span>
                    </div>
                    <button class="btn btn-subscribe waves-effect waves-light">Suscribirse</button>
                </div>

                <div class="actions-buttons">
                    <button class="btn-action waves-effect">
                        <i class="material-icons">thumb_up_alt</i> 
                        <span style="padding-right: 8px; border-right: 1px solid #ccc; margin-right: 8px;">0</span>
                        <i class="material-icons">thumb_down_alt</i>
                    </button>
                    
                    <button class="btn-action waves-effect">
                        <i class="material-icons">share</i> Compartir
                    </button>
                    
                    <button class="btn-action waves-effect hide-on-small-only">
                        <i class="material-icons">playlist_add</i> Guardar
                    </button>
                    
                    <button class="btn-action btn-circle waves-effect">
                        <i class="material-icons">more_horiz</i>
                    </button>
                </div>
            </div>

            <div class="description-box">
                <div class="row" style="margin-bottom: 0;">
                    <div class="col s12">
                        <!-- Usamos las funciones de tiempo -->
                        <strong><?php echo number_format($video['views']); ?> vistas • <?php echo timeAgo($video['created_at']); ?></strong>
                        <div class="description-text">
                            <?php echo nl2br($video['description']); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="divider" style="margin: 20px 0;"></div>
            <h5>Comentarios</h5>
            <p class="grey-text">Los comentarios se están cargando...</p>

        </div>

        <!-- COLUMNA DERECHA: VIDEOS RELACIONADOS -->
        <div class="col s12 l4">
            <?php while($related = $related_result->fetch_assoc()): ?>
                <?php include 'includes/components/video_card_small.php'; ?>
            <?php endwhile; ?>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>