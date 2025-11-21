<?php
/* includes/components/user_menu.php */

// Seguridad: Si no hay usuario logueado, no mostramos nada
if (!isset($_SESSION['user_id'])) return;

// 1. Lógica del Avatar
$avatarUrl = $_SESSION['avatar'];
if ($avatarUrl === 'default.png') {
    $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['username']) . "&background=random&color=fff&size=128";
} else {
    $avatarUrl = BASE_URL . 'uploads/avatars/' . $avatarUrl;
}
?>

<!-- 2. HTML del Menú -->
<div class="user-menu-container">
    
    <!-- Botón Avatar -->
    <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="user-avatar-btn" id="userMenuBtn">

    <!-- Menú Desplegable -->
    <div class="google-menu-dropdown" id="userDropdown">
        
        <div class="menu-header">
            <div class="header-avatar">
                <img src="<?php echo $avatarUrl; ?>" alt="Avatar">
            </div>
            <div class="header-info">
                <p class="user-name"><?php echo $_SESSION['username']; ?></p>
                <a href="<?php echo BASE_URL; ?>construction.php" class="view-channel-link">Ver tu canal</a>
            </div>
        </div>

        <div class="menu-divider"></div>

        <a href="<?php echo BASE_URL; ?>construction.php" class="menu-item">
            <i class="material-icons">account_box</i>
            <span>Cuenta de Google</span>
        </a>
        <a href="<?php echo BASE_URL; ?>construction.php" class="menu-item">
            <i class="material-icons">switch_account</i>
            <span>Cambiar de cuenta</span>
        </a>
        <a href="<?php echo BASE_URL; ?>actions/logout.php" class="menu-item">
            <i class="material-icons">logout</i>
            <span>Cerrar sesión</span>
        </a>

        <div class="menu-divider"></div>

        <a href="<?php echo BASE_URL; ?>construction.php" class="menu-item">
            <i class="material-icons">settings</i>
            <span>Configuración</span>
        </a>
        <a href="<?php echo BASE_URL; ?>construction.php" class="menu-item">
            <i class="material-icons">help_outline</i>
            <span>Ayuda</span>
        </a>
    </div>
</div>