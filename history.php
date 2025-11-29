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

// SQL: Historial sin duplicados (última vista)
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

<div class="container-fluid history-page-layout">
    
    <!-- COLUMNA PRINCIPAL (IZQUIERDA) -->
    <div class="history-main-content">
        
        <h1 class="history-page-title">Historial de reproducciones</h1>
        
        <!-- Chips de Filtros (Estáticos por ahora, decorativos) -->
        <div class="history-filters">
            <button class="filter-chip active">Todo</button>
            <button class="filter-chip">Videos</button>
            <button class="filter-chip">Shorts</button>
        </div>

        <!-- Encabezado de Fecha (Simulado "Hoy" o resultado de búsqueda) -->
        <h6 class="history-date-header">
            <?php echo !empty($search_query) ? 'Resultados de búsqueda' : 'Hoy'; ?>
        </h6>

        <div class="history-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($item = $result->fetch_assoc()): ?>
                    <div class="history-item">
                        <!-- Miniatura -->
                        <a href="watch.php?id=<?php echo $item['video_id']; ?>" class="history-thumbnail-wrapper">
                            <?php 
                                $thumb = !empty($item['thumbnail_url']) ? $item['thumbnail_url'] : "https://img.youtube.com/vi/placeholder/mqdefault.jpg";
                            ?>
                            <img src="<?php echo $thumb; ?>" alt="Miniatura">
                            <span class="duration-badge"><?php echo formatDuration($item['duration']); ?></span>
                        </a>

                        <!-- Info -->
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
                            
                            <!-- Botón X -->
                            <button class="btn-remove-history tooltipped" data-position="left" data-tooltip="Quitar del historial">
                                <i class="material-icons">close</i>
                            </button>
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

    <!-- COLUMNA LATERAL (DERECHA) - Buscador y Gestión -->
    <div class="history-sidebar">
        
        <!-- Buscador -->
        <form action="history.php" method="GET" class="history-search-box">
            <i class="material-icons">search</i>
            <input type="text" name="q" placeholder="Buscar en el historial" value="<?php echo htmlspecialchars($search_query); ?>" autocomplete="off">
        </form>

        <!-- Botones de Gestión -->
        <div class="history-manage-actions">
            <!-- Borrar Todo -->
            <button id="btnClearHistory" class="btn-manage-action">
                <i class="material-icons">delete_outline</i>
                <span>Borrar todo el historial de reproducciones</span>
            </button>
            
            <!-- Pausar (Texto dinámico via JS) -->
            <button id="btnPauseHistory" class="btn-manage-action">
                <i class="material-icons">pause_circle_outline</i>
                <span>Pausar el historial de reproducciones</span>
            </button>
            
            <button class="btn-manage-action" onclick="alert('Funcionalidad Próximamente')">
                <i class="material-icons">settings</i>
                <span>Gestionar todo el historial</span>
            </button>
        </div>

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