<?php
if (!isset($video)) return;
?>

<div class="video-card">
    <a href="watch.php?id=<?php echo $video['id']; ?>">
        <div class="thumbnail-container">
            <!-- Usamos la URL guardada o generamos una fallback -->
            <?php
            $thumb = !empty($video['thumbnail_url']) ? $video['thumbnail_url'] : "https://img.youtube.com/vi/{$video['youtube_id']}/mqdefault.jpg";
            ?>
            <img src="<?php echo $thumb; ?>" alt="Miniatura" loading="lazy">

            <!-- Duración formateada (ej. 10:30) -->
            <span class="duration-badge"><?php echo formatDuration($video['duration']); ?></span>
        </div>
    </a>

    <div class="video-info">
        <div class="channel-avatar">
            <a href="#!">
                <!-- Avatar del usuario dueño del canal -->
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($video['channel_name']); ?>&background=random&color=fff&size=64" alt="Avatar">
            </a>
            <a href="channel.php?id=<?php echo $video['channel_id']; ?>" style="color: inherit;">
                <h6><?php echo $video['channel_name']; ?></h6>
            </a>
            <span id="subscribersCount"><?php echo number_format($video['subscribers_count']); ?> suscriptores</span>
        </div>

        <div class="video-details">
            <a href="watch.php?id=<?php echo $video['id']; ?>" class="video-title">
                <?php echo $video['title']; ?>
            </a>

            <a href="#!" class="channel-name">
                <?php echo $video['channel_name']; ?> <!-- Ahora usamos el nombre del CANAL -->
                <i class="material-icons tiny" style="vertical-align: middle; font-size: 14px; margin-left: 2px;">check_circle</i>
            </a>

            <div class="video-meta">
                <?php echo number_format($video['views']); ?> vistas • <?php echo timeAgo($video['created_at']); ?>
            </div>
        </div>

        <div class="video-menu">
            <i class="material-icons">more_vert</i>
        </div>
    </div>
</div>