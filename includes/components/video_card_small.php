<?php
if (!isset($related)) return; 
?>
<a href="watch.php?id=<?php echo $related['id']; ?>" class="related-video-card">
    <div class="related-thumbnail">
        <!-- Thumbnail con fallback -->
        <?php 
             $thumb = !empty($related['thumbnail_url']) ? $related['thumbnail_url'] : "https://img.youtube.com/vi/{$related['youtube_id']}/mqdefault.jpg";
        ?>
        <img src="<?php echo $thumb; ?>" alt="Thumbnail">
        
        <!-- Duración formateada -->
        <span class="duration-badge"><?php echo formatDuration($related['duration']); ?></span>
    </div>
    <div class="related-info">
        <span class="related-title"><?php echo $related['title']; ?></span>
        
        <!-- channel_name en lugar de username -->
        <span class="related-channel"><?php echo $related['channel_name']; ?></span>
        
        <span class="related-meta">
            <?php echo number_format($related['views']); ?> vistas • <?php echo timeAgo($related['created_at']); ?>
        </span>
    </div>
</a>