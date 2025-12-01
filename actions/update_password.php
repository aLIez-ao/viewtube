<?php
require_once '../config/db.php';

if (isset($_POST['btn_reset'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validar coincidencia
    if ($password !== $confirm) {
        die("Las contraseñas no coinciden. <a href='javascript:history.back()'>Volver</a>");
    }

    // Hash nueva contraseña
    $new_hash = password_hash($password, PASSWORD_DEFAULT);

    // Actualizar usuario y BORRAR el token (un solo uso)
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $new_hash, $token);

    if ($stmt->execute()) {
        // Éxito: Redirigir al login con mensaje
        header("Location: ../login.php");
    } else {
        echo "Error al actualizar.";
    }
}
?>