<?php
session_start();
require_once '../php/conexion.php';

// Si el usuario ya está logueado, redirigir al perfil o a la página principal
if (isset($_SESSION['usuario_id'])) {
    header('Location: perfil.php'); // O la página principal, por ejemplo index.php
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario']);
    $password = $_POST['password'];

    if (empty($nombre_usuario) || empty($password)) {
        $error = "Por favor, ingresa tu nombre de usuario/email y contraseña.";
    } else {
        try {
            // Preparar la consulta para buscar el usuario por nombre de usuario o email
            // Asumimos que el usuario puede iniciar sesión con nombre de usuario O email
            // Asegúrate de que la columna 'email' exista en tu tabla 'usuarios'
            $stmt = $conexion->prepare('SELECT id_usuario, nombre_usuario, password_hash FROM usuarios WHERE nombre_usuario = ? OR email = ?');
            $stmt->execute([$nombre_usuario, $nombre_usuario]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                // Contraseña correcta, iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

                // Redirigir al usuario después de iniciar sesión exitosamente
                header('Location: perfil.php'); // O la página a la que quieras redirigir después del login
                exit();
            } else {
                // Credenciales inválidas
                $error = "Nombre de usuario/email o contraseña incorrectos.";
            }
        } catch(PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
             error_log("Error en login: " . $e->getMessage()); // Loguear el error para depuración
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lens - Iniciar Sesión</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="../css/forms.css"> 
</head>
<body class="form-page">

    <div class="form-container">
        <a href="../index.php" class="close-button">X</a>
        <h1 class="form-title">Iniciar Sesión</h1>
        <p class="form-subtitle">¡Bienvenido de nuevo, te extrañamos!</p>

        <?php if ($error): ?>
            <div class="mensaje-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="separator">O</div>

        <form action="iniciar-sesion.php" method="POST">
            <div class="input-group">
                 <!-- Icono de email -->
                <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Nombre de usuario o Email" value="<?php echo htmlspecialchars($nombre_usuario ?? ''); ?>" required>
            </div>
            <div class="input-group">
                <!-- Icono de candado -->
                <input type="password" id="password" name="password" placeholder="Contraseña" required>
                 <!-- Icono de ojo (mostrar/ocultar contraseña) -->
            </div>

            <button type="submit" class="btn-submit">Iniciar Sesión</button>
        </form>

        <p class="signup-link">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
    </div>

</body>
</html>