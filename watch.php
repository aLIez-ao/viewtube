<?php
// watch.php
require_once 'config/db.php';

// 1. Validar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$video_id = (int)$_GET['id']; // Convertir a entero por seguridad

// 2. Obtener datos del video PRINCIPAL
$sql = "SELECT v.*, u.username, u.avatar, u.id as author_id 
        FROM videos v 
        JOIN users u ON v.user_id = u.id 
        WHERE v.id = $video_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Video no encontrado -> Redirigir a 404 (construcción)
    header("Location: construction.php");
    exit();
}

$video = $result->fetch_assoc();

// 3. Obtener videos RELACIONADOS (Sidebar)
// Traemos 10 videos aleatorios que NO sean el actual
$sql_related = "SELECT v.*, u.username 
                FROM videos v 
                JOIN users u ON v.user_id = u.id 
                WHERE v.id != $video_id 
                ORDER BY RAND() LIMIT 10";
$related_result = $conn->query($sql_related);

// --- INICIO DEL HTML ---
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/watch.css">

<div class="container-fluid" style="padding-top: 24px; max-width: 1600px;">
    <div class="row">
        
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
                         <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($video['username']); ?>&background=random&color=fff&size=64" alt="Avatar">
                    </div>
                    <div class="channel-text">
                        <a href="#!" style="color: inherit;"> <h6><?php echo $video['username']; ?></h6>
                        </a>
                        <span>1.2 M suscriptores</span>
                    </div>
                    <button class="btn btn-subscribe waves-effect waves-light">Suscribirse</button>
                </div>

                <div class="actions-buttons">
                    <button class="btn-action waves-effect">
                        <i class="material-icons">thumb_up_alt</i> 
                        <span style="padding-right: 8px; border-right: 1px solid #ccc; margin-right: 8px;">12k</span>
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
                        <strong><?php echo number_format($video['views']); ?> vistas • <?php echo date("d M Y", strtotime($video['created_at'])); ?></strong>
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

        <div class="col s12 l4">
            <?php while($related = $related_result->fetch_assoc()): ?>
                <?php include 'includes/components/video_card_small.php'; ?>
            <?php endwhile; ?>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>