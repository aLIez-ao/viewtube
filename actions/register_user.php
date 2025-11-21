<?php
// actions/register_user.php
require_once '../config/db.php';

if (isset($_POST['btn_register'])) {
    
    // Recibir y limpiar datos
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones básicas
    if (empty($username) || empty($email) || empty($password)) {
        header("Location: ../register.php?error=empty");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: ../register.php?error=pass_mismatch");
        exit();
    }

    // Verificar si usuario o email ya existen
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("Location: ../register.php?error=exists");
        exit();
    }
    $stmt->close();

    // Crear el usuario
    // Hasheamos la contraseña por seguridad
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $default_avatar = 'default.png';

    // Iniciamos transacción para asegurar que se crean Usuario Y Canal juntos
    $conn->begin_transaction();

    try {
        // Insertar Usuario
        $insert_user = $conn->prepare("INSERT INTO users (username, email, password, avatar) VALUES (?, ?, ?, ?)");
        $insert_user->bind_param("ssss", $username, $email, $password_hash, $default_avatar);
        $insert_user->execute();
        $new_user_id = $conn->insert_id;
        $insert_user->close();

        // Crear Canal por Defecto (Importante para subir videos después)
        // El nombre del canal será el mismo que el usuario inicialmente
        $channel_name = $username; 
        $channel_desc = "Bienvenido a mi canal en ViewTube";
        
        $insert_channel = $conn->prepare("INSERT INTO channels (user_id, name, description) VALUES (?, ?, ?)");
        $insert_channel->bind_param("iss", $new_user_id, $channel_name, $channel_desc);
        $insert_channel->execute();
        $insert_channel->close();

        // Si todo salió bien, guardamos los cambios
        $conn->commit();

        // Iniciar sesión automáticamente (Opcional, pero recomendado)
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['username'] = $username;
        $_SESSION['avatar'] = $default_avatar;

        header("Location: ../index.php");
        exit();

    } catch (Exception $e) {
        // Si algo falló, deshacemos todo
        $conn->rollback();
        header("Location: ../register.php?error=db_error");
        exit();
    }

} else {
    header("Location: ../register.php");
    exit();
}
?>