<?php 
// index.php
require_once 'config/db.php';
require_once 'includes/header.php'; 

// 1. Consulta de videos
$sql = "SELECT v.*, u.username, u.avatar 
        FROM videos v 
        LEFT JOIN users u ON v.user_id = u.id 
        ORDER BY v.created_at DESC";

$result = $conn->query($sql);
?>

<div class="container-fluid">
    
    <div class="categories-bar hide-on-small-only" style="margin-bottom: 20px; display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px;">
        <div class="chip black white-text">Todos</div>
        <div class="chip">Videojuegos</div>
        <div class="chip">Música</div>
        <div class="chip">Programación</div>
        <div class="chip">En vivo</div>
    </div>

    <h5 style="font-weight: 700; margin-bottom: 20px;">Recomendados</h5>

    <div class="video-grid">

        <?php if ($result->num_rows > 0): ?>
            <?php while($video = $result->fetch_assoc()): ?>
                
                <?php 
                    include 'includes/components/video_card.php'; 
                ?>

            <?php endwhile; ?>
        <?php else: ?>
            
            <div class="col s12 center-align" style="padding: 50px;">
                <i class="material-icons grey-text text-lighten-1" style="font-size: 64px;">ondemand_video</i>
                <h5 class="grey-text text-darken-1">No hay videos disponibles</h5>
                <p>¡Sé el primero en subir uno!</p>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>