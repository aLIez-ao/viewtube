<?php
// login.php
require_once 'config/db.php';

// Si el usuario ya inició sesión, lo mandamos al inicio automáticamente
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceder - <?php echo $APP_NAME; ?></title>
    
    <!-- Iconos de Google (para el símbolo de alerta en errores) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Nuestro CSS específico -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>
<body>

    <div class="login-card">
        
        <!-- 1. LOGO SVG -->
        <div class="logo-section">
            <img src="<?php echo BASE_URL; ?>assets/img/favicon.svg" alt="Logo ViewTube">
        </div>
        
        <!-- Cabecera -->
        <div class="login-header">
            <h5>Acceder</h5>
            <p>Ir a ViewTube</p>
        </div>

        <!-- 2. MENSAJES DE ERROR (Si vienen en la URL) -->
        <?php if(isset($_GET['error'])): ?>
            <div class="error-msg">
                <i class="material-icons tiny" style="margin-right: 5px;">error</i>
                <?php 
                    if($_GET['error'] == 'invalid') echo "El correo o la contraseña no son correctos.";
                    else if($_GET['error'] == 'empty') echo "Ingresa tu correo y contraseña.";
                    else echo "Ocurrió un error inesperado.";
                ?>
            </div>
        <?php endif; ?>

        <!-- 3. FORMULARIO -->
        <form action="actions/login_user.php" method="POST" style="width: 100%;">
            
            <div class="input-field">
                <input type="email" name="email" placeholder="Correo electrónico" required autofocus>
            </div>

            <div class="input-field">
                <input type="password" name="password" placeholder="Introduce tu contraseña" required>
            </div>

            <!-- Enlace "Olvidé contraseña" -->
            <div style="width: 100%; display: flex;">
                <a href="forgot_password.php" class="forgot-link">¿Olvidaste tu contraseña?</a>
            </div>

            <!-- Botones de Acción -->
            <div class="action-row">
                <!-- Enlace "Crear cuenta" -->
                <a href="register.php" class="create-account-link">Crear cuenta</a>
                
                <!-- Botón Submit -->
                <button type="submit" name="btn_login" class="btn-submit">Siguiente</button>
            </div>

        </form>
    </div>

</body>
</html>