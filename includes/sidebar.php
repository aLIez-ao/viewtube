<?php
// includes/sidebar.php
?>
<!-- Quitamos 'sidenav-fixed' y el id 'slide-out' para evitar conflictos con Materialize -->
<ul class="sidenav custom-sidebar">

    <li><a href="<?php echo BASE_URL; ?>index.php" class="waves-effect"><i class="material-icons">home</i><span class="mini-text">Principal</span></a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">whatshot</i><span class="mini-text">Shorts</span></a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">subscriptions</i><span class="mini-text">Suscripciones</span></a></li>

    <li>
        <div class="divider"></div>
    </li>

    <li><a class="sidebar-title"><i class="material-icons">account_circle</i><span class="mini-text">Tú</span></a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">history</i><span class="mini-text">Historial</span></a></li>

    <li>
        <div class="divider"></div>
    </li>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <li>
            <div class="sidebar-cta">
                <p>Accede para dar "Me gusta" a los videos, realizar comentarios y suscribirte.</p>
                <a href="<?php echo BASE_URL; ?>login.php" class="sidebar-login-btn">
                    <i class="material-icons">account_circle</i> Acceder
                </a>
            </div>
        </li>
        <li>
            <div class="divider"></div>
        </li>
    <?php endif; ?>

    <!-- Agregamos spans para ocultar textos en modo mini -->
    <li><span class="sidebar-title">Explorar</span></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">music_note</i><span>Música</span></a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">movie</i><span>Películas</span></a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">live_tv</i><span>En vivo</span></a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">sports_esports</i><span>Videojuegos</span></a></li>
    
    <li>
        <div class="divider"></div>
    </li>

    <li><a href="#!" class="waves-effect"><i class="material-icons">settings</i><span>Configuración</span></a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">help</i><span>Ayuda</span></a></li>

    <li style="height: 50px;"></li>
</ul>