<?php
require_once '../config/db.php';

if (isset($_POST['btn_recover'])) {
    $email = trim($_POST['email']);

    // Verificar si el email existe
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Generar Token Único. Expiración 1 hora
        $token = bin2hex(random_bytes(32)); 
        $expires = date("Y-m-d H:i:s", time() + 3600);

        // Guardar en BD
        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $update->bind_param("ssi", $token, $expires, $row['id']);
        
        if ($update->execute()) {
            // ========== SIMULACIÓN DE ENVÍO DE EMAIL ============
            // En un servidor real, aquí usarías mail() o PHPMailer.
            // Para localhost, mostraremos el link en pantalla.
            
            $reset_link = BASE_URL . "reset_password.php?token=" . $token;
            
            echo "<div style='font-family: sans-serif; padding: 40px; text-align: center; background: #f0f2f5; height: 100vh;'>";
            echo "<div style='background: white; padding: 30px; border-radius: 8px; max-width: 500px; margin: 0 auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
            echo "<h2 style='color: #1a73e8;'>Simulación de Correo</h2>";
            echo "<p>Hola <strong>" . $row['username'] . "</strong>,</p>";
            echo "<p>Hemos recibido una solicitud para restablecer tu contraseña.</p>";
            echo "<p>Como estás en localhost, haz clic abajo para continuar:</p>";
            echo "<a href='$reset_link' style='background: #1a73e8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;'>Restablecer Contraseña</a>";
            echo "</div></div>";
            exit();
        } else {
            header("Location: ../forgot_password.php?error=db_error");
        }
    } else {
        // Por seguridad, no dece si el correo existe o no, pero para dev:
        header("Location: ../forgot_password.php?error=not_found");
    }
}
?>