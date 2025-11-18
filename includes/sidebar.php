<ul id="slide-out" class="sidenav sidenav-fixed">

    <li><a href="<?php echo BASE_URL; ?>index.php" class="waves-effect"><i class="material-icons">home</i>Principal</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">whatshot</i>Shorts</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">subscriptions</i>Suscripciones</a></li>

    <li>
        <div class="divider"></div>
    </li>

    <li><a class="sidebar-title" style="padding-bottom: 0;"><i class="material-icons">account_circle</i>Tú</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">history</i>Historial</a></li>

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

    <li><span class="sidebar-title">Explorar</span></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">music_note</i>Música</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">movie</i>Películas</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">live_tv</i>En vivo</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">sports_esports</i>Videojuegos</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">description</i>Noticias</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">emoji_events</i>Deportes</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">lightbulb_outline</i>Aprendizaje</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">radio</i>Podcasts</a></li>

    <li>
        <div class="divider"></div>
    </li>

    <li><span class="sidebar-title">Más de YouTube</span></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons red-text">play_circle_filled</i>YouTube Premium</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons red-text">play_circle_outline</i>YouTube Music</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons red-text">child_care</i>YouTube Kids</a></li>

    <li>
        <div class="divider"></div>
    </li>

    <li><a href="#!" class="waves-effect"><i class="material-icons">settings</i>Configuración</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">flag</i>Denuncias</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">help</i>Ayuda</a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">feedback</i>Enviar comentarios</a></li>

    <li>
        <div class="divider"></div>
    </li>

    <li>
        <div class="sidebar-footer" style="text-align: center; padding: 15px;">
            <p>
                ViewTube es un proyecto estudiantil desarrollado como parte de un ejercicio académico.
                Su propósito es aprender y practicar desarrollo web con PHP, MySQL y Docker.
            </p>
            <p>© <?php echo date('Y'); ?> ViewTube - Proyecto Estudiantil</p>
        </div>
    </li>

    <li style="height: 50px;"></li>
</ul>