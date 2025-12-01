<?php
require_once 'config/db.php';

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
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>assets/img/favicon.svg">
    <title>Recuperar cuenta - <?php echo $APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>
<body>
    <div class="login-card">
        <div class="logo-section">
            <img src="<?php echo BASE_URL; ?>assets/img/favicon.svg" alt="Logo">
        </div>
        
        <div class="login-header">
            <h5>Recuperación de cuenta</h5>
            <p>Introduce tu correo para buscar tu cuenta</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-msg">
                <i class="material-icons tiny" style="margin-right: 5px;">error</i>
                <?php 
                    if($_GET['error'] == 'not_found') echo "No encontramos ese correo electrónico.";
                    else if($_GET['error'] == 'db_error') echo "Error del sistema.";
                ?>
            </div>
        <?php endif; ?>

        <form action="actions/send_recovery.php" method="POST" style="width: 100%;">
            <div class="input-field">
                <input type="email" name="email" placeholder="Correo electrónico" required autofocus>
            </div>

            <div class="action-row" style="justify-content: flex-end;">
                <a href="login.php" class="create-account-link" style="margin-right: auto;">Volver</a>
                <button type="submit" name="btn_recover" class="btn-submit">Buscar</button>
            </div>
        </form>
    </div>
</body>
</html>