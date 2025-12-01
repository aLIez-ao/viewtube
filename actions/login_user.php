<?php
require_once '../config/db.php';

// Verificar si se envió el formulario
if (isset($_POST['btn_login'])) {
    
    // Limpiar datos
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validar vacíos
    if (empty($email) || empty($password)) {
        header("Location: ../login.php?error=empty");
        exit();
    }

    // Buscar usuario por email y evitar SQL Injection
    $stmt = $conn->prepare("SELECT id, username, password, avatar FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // El usuario existe, verifiquemos contraseña
    if ($row = $result->fetch_assoc()) {
        
        // Verificación Estándar (Hash)
        $check_password = password_verify($password, $row['password']);

        // Verificación de Rescate (Solo para el usuario Admin inicial)
        // Si la contraseña en BD es '123456' (texto plano) y coincide con lo escrito:
        if ($password === $row['password']) {
            $check_password = true; 
            // Opcional: Encriptarla ahora para el futuro
            // $new_hash = password_hash($password, PASSWORD_DEFAULT);
            // $conn->query("UPDATE users SET password = '$new_hash' WHERE id = " . $row['id']);
        }

        // LOGIN EXITOSO
        if ($check_password) {
            // Guardar datos en sesión
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['avatar'] = $row['avatar'];

            // Redirigir al inicio
            header("Location: ../index.php");
            exit();

        } else {
            // Contraseña incorrecta
            header("Location: ../login.php?error=invalid");
            exit();
        }

    } else {
        // Usuario no encontrado
        header("Location: ../login.php?error=invalid");
        exit();
    }

} else {
    // Si intenta entrar directo al archivo sin formulario
    header("Location: ../login.php");
    exit();
}
?>