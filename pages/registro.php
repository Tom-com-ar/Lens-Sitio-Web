<?php
session_start();
require_once '../php/conexion.php';


if (isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php'); 
    exit();
}

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmar_password = trim($_POST['confirmar_password']);

    if (empty($nombre_usuario) || empty($email) || empty($password) || empty($confirmar_password)) {
        $error = "Todos los campos son obligatorios.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del email no es válido.";
    } else if ($password !== $confirmar_password) {
        $error = "La contraseña y la confirmación no coinciden.";
    } else if (strlen($password) < 6) { 
        $error = "La contraseña debe tener al menos 6 caracteres.";
    }


    if (empty($error)) {
        try {
            $stmt_check = $conexion->prepare('SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = ? OR email = ?');
            $stmt_check->execute([$nombre_usuario, $email]);
            $count = $stmt_check->fetchColumn();

            if ($count > 0) {
                $error = "El nombre de usuario o el email ya están registrados.";
            }
        } catch(PDOException $e) {
            $error = "Error al verificar usuario/email: " . $e->getMessage();
             error_log("Error al verificar usuario/email en registro: " . $e->getMessage());
        }
    }

    // Insertar nuevo usuario si no hay errores
    if (empty($error)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conexion->prepare('INSERT INTO usuarios (nombre_usuario, email, password_hash, fecha_registro) VALUES (?, ?, ?, NOW())');
            $stmt_insert->execute([$nombre_usuario, $email, $password_hash]);

            $mensaje = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            header('Location: iniciar-sesion.php?registro_exitoso=true');
            exit();

        } catch(PDOException $e) {
            $error = "Error al registrar el usuario: " . $e->getMessage();
             error_log("Error al insertar usuario en registro: " . $e->getMessage());
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lens - Registro</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="../css/forms.css"> 
</head>
<body class="form-page">

    <div class="form-container">
        <a href="../index.php" class="close-button">X</a>

        <h1 class="form-title">Registrarse</h1>
        <p class="form-subtitle">Crea tu cuenta para acceder a todas las funciones</p>

         <?php if ($mensaje): ?>
            <div class="mensaje-exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mensaje-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="separator">O</div>

        <form action="registro.php" method="POST">
            <div class="input-group">
                <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Nombre de usuario" value="<?php echo htmlspecialchars($nombre_usuario ?? ''); ?>" required>
            </div>
             <div class="input-group">
                <input type="email" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Contraseña" required>
            </div>
             <div class="input-group">
                <input type="password" id="confirmar_password" name="confirmar_password" placeholder="Confirmar Contraseña" required>
            </div>

            <button type="submit" class="btn-submit">Registrarse</button>
        </form>

        <p class="signup-link">¿Ya tienes cuenta? <a href="iniciar-sesion.php">Inicia Sesión</a></p>
    </div>

</body>
</html>