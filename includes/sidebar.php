<?php
// includes/sidebar.php
?>
<ul class="sidenav custom-sidebar">

    <!-- PRINCIPAL -->
    <li><a href="<?php echo BASE_URL; ?>index.php" class="waves-effect"><i class="material-icons">home</i><span class="mini-text">Principal</span></a></li>
    <li><a href="#!" class="waves-effect"><i class="material-icons">whatshot</i><span class="mini-text">Shorts</span></a></li>

    <!-- Esubscriptions.php -->
    <li><a href="<?php echo BASE_URL; ?>subscriptions.php" class="waves-effect"><i class="material-icons">subscriptions</i><span class="mini-text">Suscripciones</span></a></li>
    

    <!-- SECCIÓN TÚ -->
    <li><a class="sidebar-title"><i class="material-icons">account_circle</i><span class="mini-text">Tú</span></a></li>

    <?php if (isset($_SESSION['user_id'])): ?>

        <li><a href="<?php echo BASE_URL; ?>history.php" class="waves-effect"><i class="material-icons">history</i><span class="mini-text">Historial</span></a></li>
        <li><a href="<?php echo BASE_URL; ?>construction.php" class="waves-effect"><i class="material-icons">playlist_play</i><span class="mini-text">Listas de reproducción</span></a></li>
        <li><a href="<?php echo BASE_URL; ?>construction.php" class="waves-effect"><i class="material-icons">smart_display</i><span class="mini-text">Mis videos</span></a></li>
        <li><a href="<?php echo BASE_URL; ?>construction.php" class="waves-effect"><i class="material-icons">watch_later</i><span class="mini-text">Ver más tarde</span></a></li>
        <li><a href="<?php echo BASE_URL; ?>liked.php" class="waves-effect"><i class="material-icons">thumb_up</i><span class="mini-text">Videos que me gustan</span></a></li>

        <li>
            <div class="divider"></div>
        </li>

        <!-- SUSCRIPCIONES (Lista de canales) -->
        <li><span class="sidebar-title">Canales</span></li>
        
        <?php
        $current_uid = $_SESSION['user_id'];
        $subs_sql = "SELECT c.id, c.name, u.avatar 
                         FROM subscriptions s 
                         JOIN channels c ON s.channel_id = c.id 
                         JOIN users u ON c.user_id = u.id 
                         WHERE s.user_id = $current_uid 
                         ORDER BY c.name ASC";
        $subs_result = $conn->query($subs_sql);
        ?>

        <?php if ($subs_result && $subs_result->num_rows > 0): ?>
            <?php while ($sub = $subs_result->fetch_assoc()): ?>
                <?php
                $sub_img = $sub['avatar'];
                if ($sub_img === 'default.png') {
                    $sub_img = "https://ui-avatars.com/api/?name=" . urlencode($sub['name']) . "&background=random&color=fff&size=64";
                } else {
                    $sub_img = BASE_URL . 'uploads/avatars/' . $sub_img;
                }
                ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>construction.php?channel_id=<?php echo $sub['id']; ?>" class="waves-effect channel-link" style="display: flex; align-items: center; padding: 0 24px; height: 48px;">
                        <img src="<?php echo $sub_img; ?>" alt="<?php echo $sub['name']; ?>"
                            style="width: 24px; height: 24px; border-radius: 50%; margin-right: 24px; object-fit: cover;">
                        <span class="mini-text" style="font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-grow: 1;">
                            <?php echo $sub['name']; ?>
                        </span>
                    </a>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li style="padding: 12px 24px;">
                <small style="color: #606060;">No tienes suscripciones aún.</small>
            </li>
        <?php endif; ?>

    <?php else: ?>

        <li>
            <div class="sidebar-cta">
                <p>Disfruta de tus videos favoritos, crea listas de reproducción y comparte contenido con amigos y familiares.</p>
                <a href="<?php echo BASE_URL; ?>login.php" class="sidebar-login-btn">
                    <i class="material-icons">account_circle</i> Acceder
                </a>
            </div>
        </li>

    <?php endif; ?>

    <li>
        <div class="divider"></div>
    </li>

    <!-- EXPLORAR -->
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