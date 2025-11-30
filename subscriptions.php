<?php
// subscriptions.php
require_once 'config/db.php';
require_once 'includes/functions.php';

// 1. Verificar sesión (Obligatorio)
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login o mostrar pantalla de "Inicia sesión"
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_layout = 'guide'; 
$APP_NAME = "Suscripciones"; // Título de la pestaña

// 2. QUERY: Videos de canales suscritos
// Unimos videos -> canales -> suscripciones
$sql = "SELECT 
            v.*, 
            c.id AS channel_id,
            c.name AS channel_name, 
            u.avatar,
            u.username
        FROM videos v 
        JOIN channels c ON v.channel_id = c.id 
        JOIN users u ON c.user_id = u.id 
        JOIN subscriptions s ON c.id = s.channel_id
        WHERE s.user_id = ?
        ORDER BY v.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

require_once 'includes/header.php';
?>

<!-- Reutilizamos los estilos del Home para la cuadrícula de videos -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/home.css">

<div class="container-fluid">
    
    <!-- Título de la sección -->
    <h5 style="font-weight: 700; margin-bottom: 24px; padding-left: 10px;">Más recientes</h5>

    <!-- Vista de Gestión (Opcional: Enlace para administrar suscripciones) -->
    <!-- 
    <div style="text-align: right; margin-bottom: 10px; padding-right: 10px;">
        <a href="manage_subscriptions.php" class="blue-text" style="font-weight: 600;">Gestionar</a>
    </div> 
    -->

    <div class="video-grid">

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($video = $result->fetch_assoc()): ?>
                <!-- Reutilizamos el componente de tarjeta de video -->
                <?php include 'includes/components/video_card.php'; ?>
            <?php endwhile; ?>
        <?php else: ?>
            
            <!-- Estado Vacío -->
            <div class="col s12 center-align" style="grid-column: 1 / -1; padding: 80px 20px; text-align: center;">
                <i class="material-icons grey-text text-lighten-2" style="font-size: 120px;">subscriptions</i>
                <h5 class="grey-text text-darken-2" style="font-weight: 600;">No hay videos nuevos</h5>
                <p class="grey-text" style="font-size: 16px; max-width: 400px; margin: 10px auto;">
                    Suscríbete a tus canales favoritos para ver sus videos más recientes aquí.
                </p>
                <a href="index.php" class="btn blue darken-3 z-depth-0" style="border-radius: 2px; text-transform: none; margin-top: 20px;">
                    Explorar canales
                </a>
            </div>

        <?php endif; ?>

    </div>
</div>

<!-- Scripts necesarios (Tooltips, etc) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('.tooltipped');
        // Verificar si M (Materialize) está cargado antes de usarlo
        if (typeof M !== 'undefined') {
            var instances = M.Tooltip.init(elems);
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>