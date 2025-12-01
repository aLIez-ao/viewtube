<?php 
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php'; 

// Seleccionamos datos del video, del canal (nombre) y del usuario (avatar)
$sql = "SELECT 
            v.*, 
            c.name AS channel_name, 
            c.id AS channel_id,
            u.avatar,
            u.username
        FROM videos v 
        JOIN channels c ON v.channel_id = c.id 
        JOIN users u ON c.user_id = u.id 
        ORDER BY v.created_at DESC";

$result = $conn->query($sql);
?>

<div class="container-fluid">
    
    <!-- Chips de categorías (Estáticos por ahora) -->
    <div class="categories-bar hide-on-small-only" style="margin-bottom: 20px; display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px;">
        <div class="chip black white-text">Todos</div>
        <div class="chip">Tecnología</div>
        <div class="chip">Música</div>
        <div class="chip">Gaming</div>
    </div>

    <h5 style="font-weight: 700; margin-bottom: 20px;">Recomendados</h5>

    <div class="video-grid">

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($video = $result->fetch_assoc()): ?>
                <?php include 'includes/components/video_card.php'; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col s12 center-align" style="padding: 50px;">
                <i class="material-icons grey-text text-lighten-1" style="font-size: 64px;">ondemand_video</i>
                <h5 class="grey-text text-darken-1">No hay videos disponibles</h5>
                <p>Asegúrate de haber insertado un Canal y un Video en la BD nueva.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>