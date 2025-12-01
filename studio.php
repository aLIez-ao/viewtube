<?php
require_once 'config/db.php';

// Seguridad: Solo usuarios logueados
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar si el usuario YA tiene un canal
$sql = "SELECT * FROM channels WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$has_channel = ($result->num_rows > 0);
$channel = $has_channel ? $result->fetch_assoc() : null;

// Datos del usuario para el avatar
$sql_u = "SELECT username, avatar FROM users WHERE id = ?";
$stmt_u = $conn->prepare($sql_u);
$stmt_u->bind_param("i", $user_id);
$stmt_u->execute();
$user = $stmt_u->get_result()->fetch_assoc();

$user_avatar = $user['avatar'] === 'default.png' 
    ? "https://ui-avatars.com/api/?name=" . urlencode($user['username']) . "&background=random&color=fff&size=128"
    : BASE_URL . 'uploads/avatars/' . $user['avatar'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $has_channel ? 'Panel del canal - ' . $channel['name'] : 'Crear un canal'; ?> - YouTube Studio</title>
    
    <!-- Fuentes e Iconos -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>assets/img/favicon.svg">
    <!-- CSS Específico de Studio (Sin dependencias del resto del sitio) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/studio.css">
</head>
<body class="<?php echo $has_channel ? 'mode-dashboard' : 'mode-creation'; ?>">

    <!-- USUARIO SIN CANAL (CREACIÓN)       -->
    <?php if (!$has_channel): ?>
        
        <div class="creation-container">
            <div class="creation-card">
                <div class="creation-header">
                    <img src="<?php echo BASE_URL; ?>assets/img/favicon.svg" alt="Logo" class="studio-logo-small">
                    <h2>Cómo te verán los demás</h2>
                </div>

                <div class="profile-preview">
                    <div class="avatar-wrapper">
                        <img src="<?php echo $user_avatar; ?>" alt="Avatar">
                        <div class="avatar-overlay">
                            <i class="material-icons">camera_alt</i>
                        </div>
                    </div>
                    <p class="upload-hint">Sube una foto</p>
                </div>

                <form id="createChannelForm" class="creation-form">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" id="channelName" name="name" value="<?php echo $user['username']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Identificador (Handle)</label>
                        <input type="text" id="channelHandle" value="@<?php echo strtolower(str_replace(' ', '', $user['username'])); ?>" disabled style="color: #909090;">
                        <small>Al pulsar <strong>Crear canal</strong>, aceptas los Términos del Servicio de YouTube.</small>
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-create" id="btnCreateChannel">Crear canal</button>
                    </div>
                </form>
            </div>
        </div>

    <!-- DASHBOARD (USUARIO CON CANAL)      -->
    <?php else: ?>

        <!-- Header de Studio -->
        <header class="studio-header">
            <div class="header-left">
                <div class="studio-brand">
                    <img src="<?php echo BASE_URL; ?>assets/img/favicon.svg" alt="Logo">
                    <span>Studio</span>
                </div>
            </div>
            
            <div class="header-center">
                <div class="search-bar">
                    <i class="material-icons">search</i>
                    <input type="text" placeholder="Buscar en tu canal">
                </div>
            </div>

            <div class="header-right">
                <div class="action-icon"><i class="material-icons">help_outline</i></div>
                <button class="btn-create-video">
                    <i class="material-icons">video_call</i> <span>CREAR</span>
                </button>
                <div class="user-avatar-small">
                    <img src="<?php echo $user_avatar; ?>" alt="Avatar">
                </div>
            </div>
        </header>

        <div class="studio-layout">
            <!-- Sidebar de Studio -->
            <aside class="studio-sidebar">
                <div class="channel-summary">
                    <img src="<?php echo $user_avatar; ?>" class="summary-avatar">
                    <div class="summary-text">
                        <h6>Tu canal</h6>
                        <p><?php echo $channel['name']; ?></p>
                    </div>
                </div>
                
                <nav class="studio-nav">
                    <a href="<?php echo BASE_URL; ?>index.php" class="active"><i class="material-icons">home</i>ViewTube</a>
                    <a href="<?php echo BASE_URL; ?>construccion.php" class="active"><i class="material-icons">dashboard</i> Panel de control</a>
                    <a href="<?php echo BASE_URL; ?>construccion.php"><i class="material-icons">video_library</i> Contenido</a>
                    <a href="<?php echo BASE_URL; ?>construccion.php"><i class="material-icons">analytics</i> Estadísticas</a>
                    <a href="<?php echo BASE_URL; ?>construccion.php"><i class="material-icons">comment</i> Comentarios</a>
                    <a href="<?php echo BASE_URL; ?>construccion.php"><i class="material-icons">subtitles</i> Subtítulos</a>
                    <a href="<?php echo BASE_URL; ?>construccion.php"><i class="material-icons">monetization_on</i> Ingresos</a>
                    <a href="<?php echo BASE_URL; ?>construccion.php"><i class="material-icons">settings</i> Configuración</a>
                </nav>
            </aside>

            <!-- Contenido Principal -->
            <main class="studio-main">
                <h1 class="page-title">Panel de control del canal</h1>
                
                <div class="dashboard-grid">
                    <!-- Tarjeta 1: Último video -->
                    <div class="dash-card latest-video">
                        <h3>Rendimiento del último video</h3>
                        <div class="placeholder-content">
                            <div class="upload-area">
                                <i class="material-icons">file_upload</i>
                                <p>Sube un video para empezar</p>
                                <a href="upload.php" class="btn-blue">SUBIR VIDEOS</a>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Estadísticas -->
                    <div class="dash-card analytics">
                        <h3>Estadísticas del canal</h3>
                        <div class="stats-summary">
                            <p>Suscriptores actuales</p>
                            <h2><?php echo number_format($channel['subscribers_count']); ?></h2>
                            <div class="stat-change">
                                <span>+0</span> <span class="text-gray">en los últimos 28 días</span>
                            </div>
                            <div class="divider"></div>
                            <div class="stat-row">
                                <span>Vistas</span>
                                <span><?php echo number_format($channel['total_views']); ?></span>
                            </div>
                            <div class="stat-row">
                                <span>Tiempo de visualización (horas)</span>
                                <span>0.0</span>
                            </div>
                        </div>
                        <a href="#" class="card-link">IR A LAS ESTADÍSTICAS DEL CANAL</a>
                    </div>

                    <!-- Tarjeta 3: Novedades -->
                    <div class="dash-card news">
                        <h3>Novedades de Studio</h3>
                        <div class="news-item">
                            <div class="news-img"></div>
                            <h4>Nuevas herramientas de edición</h4>
                            <p>Descubre cómo editar tus Shorts directamente desde el móvil.</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>

    <?php endif; ?>

    <script src="<?php echo BASE_URL; ?>assets/js/studio.js"></script>
</body>
</html>