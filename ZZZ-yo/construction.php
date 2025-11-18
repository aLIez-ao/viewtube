<?php 
// construction.php
require_once 'config/db.php';
require_once 'includes/header.php'; 
?>

<div class="container">
    <div class="row">
        <div class="col s12 m8 offset-m2 l6 offset-l3 center-align" style="margin-top: 10vh;">
            
            <i class="material-icons grey-text text-lighten-1" style="font-size: 120px;">engineering</i>
            
            <h4 style="font-weight: bold; color: #0f0f0f;">En construcción</h4>
            
            <p class="grey-text text-darken-1" style="font-size: 18px; margin-bottom: 30px;">
                Esta funcionalidad aún no está disponible en ViewTube. <br>
                Estamos trabajando duro para implementarla pronto.
            </p>

            <a href="<?php echo BASE_URL; ?>index.php" class="btn blue darken-3 z-depth-0" style="border-radius: 2px; text-transform: none; font-weight: 500;">
                Volver al Inicio
            </a>
            
            </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>