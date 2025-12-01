<?php
require_once 'config/db.php';

// Si el usuario ya inició sesión, lo mandamos al inicio
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
    <title>Crear cuenta - <?php echo $APP_NAME; ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>assets/img/favicon.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>
<body>

    <div class="login-card">
        
        <div class="logo-section">
            <img src="<?php echo BASE_URL; ?>assets/img/favicon.svg" alt="Logo">
        </div>
        
        <div class="login-header">
            <h5>Crear una cuenta</h5>
            <p>Ir a ViewTube</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-msg">
                <i class="material-icons tiny" style="margin-right: 5px;">error</i>
                <?php 
                    if($_GET['error'] == 'empty') echo "Por favor, completa todos los campos.";
                    else if($_GET['error'] == 'pass_mismatch') echo "Las contraseñas no coinciden.";
                    else if($_GET['error'] == 'exists') echo "El usuario o correo ya están registrados.";
                    else if($_GET['error'] == 'db_error') echo "Error al conectar con la base de datos.";
                    else echo "Ocurrió un error inesperado.";
                ?>
            </div>
        <?php endif; ?>

        <form action="actions/register_user.php" method="POST" style="width: 100%;">
            
            <div class="input-field">
                <input type="text" name="username" placeholder="Nombre de usuario" required autocomplete="off">
            </div>

            <div class="input-field">
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>

            <div class="input-field">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>

            <div class="input-field">
                <input type="password" name="confirm_password" placeholder="Confirmar contraseña" required>
            </div>

            <div class="action-row" style="margin-top: 20px;">
                <a href="login.php" class="create-account-link">Iniciar sesión</a>
                <button type="submit" name="btn_register" class="btn-submit">Registrarse</button>
            </div>

        </form>
    </div>

</body>
</html>