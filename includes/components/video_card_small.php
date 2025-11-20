<?php
/* includes/components/video_card_small.php */
if (!isset($related)) return; 
?>
<a href="watch.php?id=<?php echo $related['id']; ?>" class="related-video-card">
    <div class="related-thumbnail">
        <img src="https://img.youtube.com/vi/<?php echo $related['youtube_id']; ?>/mqdefault.jpg" alt="Thumbnail">
        <span class="duration-badge">05:20</span>
    </div>
    <div class="related-info">
        <span class="related-title"><?php echo $related['title']; ?></span>
        <span class="related-channel"><?php echo $related['username']; ?></span>
        <span class="related-meta"><?php echo number_format($related['views']); ?> vistas • hace 2 días</span>
    </div>
</a>