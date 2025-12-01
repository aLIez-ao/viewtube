<?php
require_once 'config/db.php';

// Seguridad
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar Canal
$sql = "SELECT c.id, c.name, u.avatar 
        FROM channels c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header("Location: studio.php");
    exit();
}
$channel = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar video - YouTube Studio</title>
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/upload.css">
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>assets/img/favicon.svg">
</head>
<body>

    <div class="upload-container">
        <div class="upload-modal" style="height: auto; max-height: 90vh;">
            
            <div class="modal-header">
                <h3>Publicar video</h3>
                <div class="header-actions">
                    <a href="studio.php" class="close-icon"><i class="material-icons">close</i></a>
                </div>
            </div>

            <form id="uploadForm" action="actions/upload_video.php" method="POST" class="step-content">
                
                <div class="details-grid">
                    <!-- Columna Izquierda: Datos -->
                    <div class="details-left">
                        
                        <div class="form-group">
                            <div class="input-wrapper">
                                <label class="floating-label">Título (obligatorio)</label>
                                <input type="text" name="title" id="videoTitle" required placeholder="Ej. Mi viaje a Japón">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-wrapper">
                                <label class="floating-label">Enlace del video (YouTube)</label>
                                <!-- CAMBIO: Ahora pedimos la URL completa -->
                                <input type="text" name="video_url" id="videoUrl" required placeholder="Ej. https://www.youtube.com/watch?v=dQw4w9WgXcQ" autocomplete="off">
                                <small style="color: #606060; font-size: 12px; margin-top: 4px; display:block;">
                                    Pega el enlace completo del video.
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-wrapper">
                                <label class="floating-label">Descripción</label>
                                <textarea name="description" id="videoDescription" rows="4" placeholder="Cuéntales a los usuarios sobre tu video"></textarea>
                            </div>
                        </div>
                        
                        <!-- ELIMINADO: Campo de duración -->

                    </div>

                    <!-- Columna Derecha: Preview -->
                    <div class="details-right">
                        <div class="video-preview-card">
                            <div class="preview-player" id="previewContainer">
                                <div class="placeholder-preview" style="color: #aaa; text-align: center;">
                                    <i class="material-icons" style="font-size: 48px;">play_circle_outline</i>
                                    <p>Pega un enlace para previsualizar</p>
                                </div>
                            </div>
                            <div class="preview-info">
                                <div class="info-row">
                                    <span class="label">Enlace del video</span>
                                    <span class="value link-blue" id="previewLink">youtu.be/...</span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Estado</span>
                                    <span class="value">Listo para publicar</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="upload-status"></div>
                    <div class="footer-buttons">
                        <button type="submit" class="btn-publish" id="btnPublish">PUBLICAR</button>
                    </div>
                </div>

            </form>

        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/upload.js"></script>
</body>
</html>