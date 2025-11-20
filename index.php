<?php 
// 1. Configuración (Base de datos y constantes)
require_once 'config/db.php';

// 2. Cabecera (HTML, CSS, Navbar)
require_once 'includes/header.php'; 
?>

<div class="section">
    <h4>Bienvenido a ViewTube</h4>
    <p>Si ves la barra roja arriba y el footer rojo abajo, ¡la estructura funciona!</p>
    
    <div class="col s12 m6">
    <div class="card blue-grey darken-1">
        <div class="card-content white-text">
            <span class="card-title">Estado del Sistema</span>
            <p>
                <?php 
                if(isset($conn) && $conn->connect_error == null) {
                    $resultado = $conn->query("SELECT DATABASE()");
                    $nombre_db = $resultado->fetch_row()[0];
                    
                    echo "✅ Conexión exitosa a: <strong>" . $nombre_db . "</strong>";
                } else {
                    echo "❌ Error: No hay conexión.";
                }
                ?>
            </p>
        </div>
    </div>
</div>

<?php 
// 3. Pie de página (Cierre HTML, JS)
require_once 'includes/footer.php'; 
?>