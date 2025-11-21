<?php
// reset_password.php
require_once 'config/db.php';

// Validar Token
$token = $_GET['token'] ?? '';
$error = null;
$valid_token = false;

if (empty($token)) {
    $error = "Token no proporcionado.";
} else {
    // Buscar usuario con ese token y que no haya expirado
    $now = date("Y-m-d H:i:s");
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > ?");
    $stmt->bind_param("ss", $token, $now);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $valid_token = true;
    } else {
        $error = "El enlace es inválido o ha expirado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña - <?php echo $APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h5>Cambiar contraseña</h5>
        </div>

        <?php if (!$valid_token): ?>
            <div class="error-msg" style="justify-content: center;">
                <?php echo $error; ?>
            </div>
            <div class="action-row" style="justify-content: center;">
                <a href="forgot_password.php" class="create-account-link">Solicitar nuevo enlace</a>
            </div>
        <?php else: ?>
            
            <form action="actions/update_password.php" method="POST" style="width: 100%;">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="input-field">
                    <input type="password" name="password" placeholder="Nueva contraseña" required>
                </div>
                <div class="input-field">
                    <input type="password" name="confirm_password" placeholder="Confirmar nueva contraseña" required>
                </div>

                <div class="action-row">
                    <button type="submit" name="btn_reset" class="btn-submit" style="width: 100%;">Guardar Contraseña</button>
                </div>
            </form>

        <?php endif; ?>
    </div>
</body>
</html>