<?php
/* includes/components/video_card.php
   Requisitos: Debe existir una variable $video con los datos de la BD.
*/

// TODO: targetas genericas no procedurales
if (!isset($video)) return; 
?>

<div class="video-card">
    <a href="watch.php?id=<?php echo $video['id']; ?>">
        <div class="thumbnail-container">
            <img src="https://img.youtube.com/vi/<?php echo $video['youtube_id']; ?>/mqdefault.jpg" alt="Miniatura de video" loading="lazy">
            <span class="duration-badge">10:00</span> </div>
    </a>

    <div class="video-info">
        <div class="channel-avatar">
            <a href="#!"><img src="https://ui-avatars.com/api/?name=<?php echo urlencode($video['username']); ?>&background=random&color=fff&size=64" alt="Avatar de <?php echo $video['username']; ?>"></a>
        </div>
        
        <div class="video-details">
            <a href="watch.php?id=<?php echo $video['id']; ?>" class="video-title">
                <?php echo $video['title']; ?>
            </a>
            
            <a href="#!" class="channel-name"> <?php echo $video['username']; ?>
                <i class="material-icons tiny" style="vertical-align: middle; font-size: 14px; margin-left: 2px;">check_circle</i>
            </a>
            
            <div class="video-meta">
                <?php echo number_format($video['views']); ?> vistas • hace 1 día </div>
        </div>
        
        <div class="video-menu">
            <i class="material-icons">more_vert</i>
        </div>
    </div>
</div>