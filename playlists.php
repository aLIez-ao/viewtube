<?php
// playlists.php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_layout = 'guide';
$APP_NAME = "Listas de reproducción"; 

// 1. OBTENER DATOS DE "VIDEOS QUE ME GUSTAN"
// Necesitamos el conteo y la miniatura del último video likeado
$sql_liked = "SELECT 
                (SELECT COUNT(*) FROM likes WHERE user_id = ? AND type = 'like') as count,
                (SELECT v.thumbnail_url 
                 FROM likes l 
                 JOIN videos v ON l.video_id = v.id 
                 WHERE l.user_id = ? AND l.type = 'like' 
                 ORDER BY l.created_at DESC LIMIT 1) as last_thumb";

$stmt_l = $conn->prepare($sql_liked);
$stmt_l->bind_param("ii", $user_id, $user_id);
$stmt_l->execute();
$liked_data = $stmt_l->get_result()->fetch_assoc();


// 2. OBTENER LISTAS PERSONALIZADAS DEL USUARIO
$sql = "SELECT 
            p.id, 
            p.title, 
            p.is_private,
            p.created_at,
            (SELECT COUNT(*) FROM playlist_videos pv WHERE pv.playlist_id = p.id) as video_count,
            (
                SELECT v.thumbnail_url 
                FROM playlist_videos pv 
                JOIN videos v ON pv.video_id = v.id 
                WHERE pv.playlist_id = p.id 
                ORDER BY pv.position ASC, pv.id DESC 
                LIMIT 1
            ) as cover_url
        FROM playlists p 
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/playlists.css">

<div class="container-fluid">
    
    <h5 style="font-weight: 700; margin-bottom: 24px; padding-left: 10px;">Bibliotecas y Listas</h5>

    <div class="playlists-grid">
        
        <!-- 1. TARJETA "VIDEOS QUE ME GUSTAN" (Siempre aparece si tienes likes) -->
        <?php if ($liked_data['count'] > 0): ?>
            <?php 
                $likedThumb = !empty($liked_data['last_thumb']) ? $liked_data['last_thumb'] : BASE_URL . 'assets/img/no-video.png';
            ?>
            <div class="playlist-card">
                <a href="liked.php" class="playlist-thumbnail-wrapper">
                    <img src="<?php echo $likedThumb; ?>" alt="Videos que me gustan">
                    <div class="playlist-overlay">
                        <span class="video-count"><?php echo $liked_data['count']; ?></span>
                        <i class="material-icons">thumb_up</i>
                    </div>
                </a>
                <div class="playlist-info">
                    <a href="liked.php" class="playlist-title">Videos que me gustan</a>
                    <div class="playlist-meta">
                        <span>Automática</span>
                        <span class="dot">•</span>
                        <span>Pública</span>
                    </div>
                    <a href="liked.php" class="view-playlist-link">VER LISTA</a>
                </div>
            </div>
        <?php endif; ?>


        <!-- 2. LISTAS PERSONALIZADAS -->
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($playlist = $result->fetch_assoc()): ?>
                
                <div class="playlist-card">
                    <a href="playlist.php?id=<?php echo $playlist['id']; ?>" class="playlist-thumbnail-wrapper">
                        <?php 
                            $cover = !empty($playlist['cover_url']) ? $playlist['cover_url'] : BASE_URL . 'assets/img/no-video.png';
                            
                            // Si es la lista de "Ver más tarde", podríamos ponerle un icono especial si quisieras, 
                            // pero aquí usaremos la lógica estándar de portada.
                        ?>
                        
                        <img src="<?php echo $cover; ?>" alt="<?php echo htmlspecialchars($playlist['title']); ?>" onerror="this.src='https://via.placeholder.com/320x180?text=Vacio'">
                        
                        <div class="playlist-overlay">
                            <span class="video-count"><?php echo $playlist['video_count']; ?></span>
                            <i class="material-icons">playlist_play</i>
                        </div>
                    </a>

                    <div class="playlist-info">
                        <a href="playlist.php?id=<?php echo $playlist['id']; ?>" class="playlist-title">
                            <?php echo htmlspecialchars($playlist['title']); ?>
                        </a>
                        
                        <div class="playlist-meta">
                            <span><?php echo ($playlist['is_private']) ? 'Privada' : 'Pública'; ?></span>
                            <span class="dot">•</span>
                            <span>Actualizada <?php echo timeAgo($playlist['created_at']); ?></span>
                        </div>
                        
                        <a href="playlist.php?id=<?php echo $playlist['id']; ?>" class="view-playlist-link">
                            VER LISTA
                        </a>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php endif; ?>
        
        <!-- Si no hay nada en absoluto -->
        <?php if ($liked_data['count'] == 0 && $result->num_rows == 0): ?>
            <div class="empty-playlists">
                <i class="material-icons">playlist_play</i>
                <h6>No tienes listas de reproducción</h6>
                <p>Dale "Me gusta" a videos o crea listas nuevas para verlas aquí.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>