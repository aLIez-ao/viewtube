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
$page_layout = 'guide'; 

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// SQL: Historial sin duplicados
$sql = "SELECT h.id as history_id, h.last_watched_at, 
               v.id as video_id, v.title, v.description, v.thumbnail_url, v.views, v.duration,
               c.name as channel_name, c.id as channel_id
        FROM history h
        JOIN videos v ON h.video_id = v.id
        JOIN channels c ON v.channel_id = c.id
        WHERE h.id IN (
            SELECT MAX(id) 
            FROM history 
            WHERE user_id = ? 
            GROUP BY video_id
        )";

if (!empty($search_query)) {
    $sql .= " AND (v.title LIKE ? OR c.name LIKE ?)";
}

$sql .= " ORDER BY h.last_watched_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($search_query)) {
    $param = "%$search_query%";
    $stmt->bind_param("iss", $user_id, $param, $param);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/history.css">
<!-- Necesitamos estilos del modal de compartir (reutilizamos watch.css o los incluimos aquí) -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/watch.css"> 
<!-- Nota: Si watch.css afecta otros estilos, lo ideal sería extraer los estilos del modal a un global.css, 
     pero por ahora lo incluimos para tener el diseño del modal. -->

<div class="container-fluid history-page-layout">
    
    <!-- COLUMNA PRINCIPAL (IZQUIERDA) -->
    <div class="history-main-content">
        
        <h1 class="history-page-title">Historial de reproducciones</h1>
        
        <div class="history-filters">
            <button class="filter-chip active">Todo</button>
            <button class="filter-chip">Videos</button>
            <button class="filter-chip">Shorts</button>
        </div>

        <h6 class="history-date-header">
            <?php echo !empty($search_query) ? 'Resultados de búsqueda' : 'Hoy'; ?>
        </h6>

        <div class="history-list" id="historyListContainer">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($item = $result->fetch_assoc()): ?>
                    <div class="history-item" id="history-item-<?php echo $item['video_id']; ?>">
                        
                        <a href="watch.php?id=<?php echo $item['video_id']; ?>" class="history-thumbnail-wrapper">
                            <?php 
                                $thumb = !empty($item['thumbnail_url']) ? $item['thumbnail_url'] : "https://img.youtube.com/vi/placeholder/mqdefault.jpg";
                            ?>
                            <img src="<?php echo $thumb; ?>" alt="Miniatura">
                            <span class="duration-badge"><?php echo formatDuration($item['duration']); ?></span>
                        </a>

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
                            
                            <!-- MENÚ DE OPCIONES -->
                            <div class="history-menu-wrapper">
                                <button class="btn-history-menu">
                                    <i class="material-icons">more_vert</i>
                                </button>
                                
                                <div class="history-dropdown">
                                    <div class="menu-option" onclick="M.toast({html: 'Añadido a la cola'})">
                                        <i class="material-icons">playlist_play</i> Añadir a la cola
                                    </div>
                                    <div class="menu-option" onclick="M.toast({html: 'Guardado en Ver más tarde'})">
                                        <i class="material-icons">watch_later</i> Guardar para ver más tarde
                                    </div>
                                    <div class="menu-option" onclick="M.toast({html: 'Guardado en lista'})">
                                        <i class="material-icons">playlist_add</i> Añadir a lista de reproducción
                                    </div>
                                    <div class="menu-option" onclick="M.toast({html: 'Iniciando descarga...'})">
                                        <i class="material-icons">download</i> Descargar
                                    </div>
                                    
                                    <!-- BOTÓN COMPARTIR CON DATA ID -->
                                    <div class="menu-option btn-share-item" data-video-id="<?php echo $item['video_id']; ?>">
                                        <i class="material-icons">share</i> Compartir
                                    </div>
                                    
                                    <div class="divider"></div>
                                    <div class="menu-option btn-remove-item" data-video-id="<?php echo $item['video_id']; ?>">
                                        <i class="material-icons">delete</i> Borrar del historial
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-history">
                    <p>No se encontraron videos en el historial.</p>
                    <?php if(!empty($search_query)): ?>
                        <a href="history.php">Borrar búsqueda</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- COLUMNA LATERAL -->
    <div class="history-sidebar">
        <form action="history.php" method="GET" class="history-search-box">
            <i class="material-icons">search</i>
            <input type="text" name="q" placeholder="Buscar en el historial" value="<?php echo htmlspecialchars($search_query); ?>" autocomplete="off">
        </form>
        <div class="history-manage-actions">
            <button id="btnClearHistory" class="btn-manage-action">
                <i class="material-icons">delete_outline</i>
                <span>Borrar todo el historial de reproducciones</span>
            </button>
            <button id="btnPauseHistory" class="btn-manage-action">
                <i class="material-icons">pause_circle_outline</i>
                <span>Pausar el historial de reproducciones</span>
            </button>
        </div>
    </div>

</div>

<!-- MODAL DE COMPARTIR (Reutilizado) -->
<div id="shareModal" class="modal share-modal">
    <div class="modal-content">
        <div class="share-header">
            <h5>Compartir</h5>
            <i class="material-icons modal-close">close</i>
        </div>
        
        <div class="social-row">
            <a href="#" target="_blank" class="social-icon" id="shareWhatsapp" title="WhatsApp">
                <div class="icon-circle whatsapp"><svg viewBox="0 0 24 24" width="24" height="24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></div>
                <span>WhatsApp</span>
            </a>
            
            <a href="#" target="_blank" class="social-icon" id="shareFacebook" title="Facebook">
                <div class="icon-circle facebook"><svg viewBox="0 0 24 24" width="24" height="24" fill="white"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></div>
                <span>Facebook</span>
            </a>

            <a href="#" target="_blank" class="social-icon" id="shareTwitter" title="X / Twitter">
                <div class="icon-circle twitter"><svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></div>
                <span>X</span>
            </a>

            <a href="#" target="_blank" class="social-icon" id="shareEmail" title="Email">
                <div class="icon-circle email"><i class="material-icons" style="font-size: 24px; color: white;">email</i></div>
                <span>Email</span>
            </a>
        </div>

        <div class="copy-link-wrapper">
            <div class="input-container">
                <input type="text" id="shareUrlInput" value="" readonly>
                <button id="copyLinkBtn">Copiar</button>
            </div>
        </div>
        
        <!-- Checkbox tiempo quitado en historial porque no tiene sentido sin player -->
    </div>
</div>

<!-- CARGAR JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/history.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('.tooltipped');
        var instances = M.Tooltip.init(elems);
    });
</script>

<?php require_once 'includes/footer.php'; ?>