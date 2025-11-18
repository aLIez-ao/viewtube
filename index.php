<?php 
// 1. Configuración (Base de datos y constantes)
require_once 'config/db.php';

// 2. Cabecera (HTML, CSS, Navbar)
require_once 'includes/header.php'; 
?>

<div class="section">
    <h4>Bienvenido a ViewTube</h4>
    <p>Si ves la barra roja arriba y el footer rojo abajo, ¡la estructura funciona!</p>
    
    <div class="row">
        <div class="col s12 m6">
            <div class="card blue-grey darken-1">
                <div class="card-content white-text">
                    <span class="card-title">Prueba de Base de Datos</span>
                    <p>
                        <?php 
                        if(isset($conn) && $conn->ping()) {
                            echo "✅ Conexión a MySQL exitosa: " . $dbname;
                        } else {
                            echo "❌ Error de conexión.";
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// 3. Pie de página (Cierre HTML, JS)
require_once 'includes/footer.php'; 
?>