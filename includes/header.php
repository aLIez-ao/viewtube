<?php
// includes/header.php
require_once dirname(__DIR__) . '/config/db.php';

if (!isset($APP_NAME)) $APP_NAME = "ViewTube";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $APP_NAME; ?></title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/global.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sidebar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/home.css">
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <nav>
        <div class="nav-wrapper">

            <!-- IZQUIERDA -->
            <div class="nav-left">
                <a href="#" data-target="slide-out" class="sidenav-trigger show-on-large menu-btn">
                    <i class="material-icons">menu</i>
                </a>
                <a href="<?php echo BASE_URL; ?>index.php" class="brand-logo-custom">
                    <img src="<?php echo BASE_URL; ?>assets/img/favicon.svg" alt="Logo">
                    <?php echo $APP_NAME; ?>
                </a>
            </div>

            <!-- CENTRO -->
            <div class="nav-center hide-on-small-only">
                <form action="<?php echo BASE_URL; ?>search.php" method="GET" class="search-box">
                    <div class="search-input-wrapper">
                        <input type="text" name="q" placeholder="Buscar" autocomplete="off">
                    </div>
                    <button type="submit" class="search-btn tooltipped" data-position="bottom" data-tooltip="Buscar">
                        <i class="material-icons">search</i>
                    </button>
                </form>
            </div>

            <!-- DERECHA -->
            <div class="nav-right">
                
                <a href="#" class="icon-btn tooltipped hide-on-small-only" data-position="bottom" data-tooltip="Crear">
                    <i class="material-icons">video_call</i>
                </a>
                <a href="#" class="icon-btn tooltipped hide-on-small-only" data-position="bottom" data-tooltip="Notificaciones">
                    <i class="material-icons">notifications</i>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    
                    <!-- AQUÍ ESTÁ EL CAMBIO: Incluimos el componente limpio -->
                    <?php include 'components/user_menu.php'; ?>

                <?php else: ?>
                    
                    <a href="#" class="icon-btn tooltipped" data-position="bottom" data-tooltip="Configuración">
                        <i class="material-icons">more_vert</i>
                    </a>
                    <a href="<?php echo BASE_URL; ?>login.php" class="login-btn">
                        <i class="material-icons">account_circle</i>
                        Acceder
                    </a>

                <?php endif; ?>
            </div>

        </div>
    </nav>

    <main>
        <div class="container-fluid" style="padding: 20px;">