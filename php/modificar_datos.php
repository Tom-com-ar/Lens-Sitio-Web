<?php
session_start();
require_once '../php/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: iniciar-sesion.php');
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$mensaje = '';
$error = '';

// Obtener datos actuales del usuario
try {
    $stmt = $conexion->prepare('SELECT nombre_usuario, email FROM usuarios WHERE id_usuario = ?');
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        // Esto no debería pasar si la sesión está bien, pero por seguridad
        header('Location: cerrar_sesion.php');
        exit();
    }
} catch(PDOException $e) {
    $error = "Error al cargar los datos del usuario: " . $e->getMessage();
}

// Procesar la actualización de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre_usuario = trim($_POST['nombre_usuario']);
    $nueva_password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];

    // Validaciones básicas
    if (empty($nuevo_nombre_usuario)) {
        $error = "El nombre de usuario no puede estar vacío.";
    } else if (!empty($nueva_password) && $nueva_password !== $confirmar_password) {
        $error = "La nueva contraseña y la confirmación no coinciden.";
    }

    if (empty($error)) {
        try {
            // Actualizar nombre de usuario
            $stmt = $conexion->prepare('UPDATE usuarios SET nombre_usuario = ? WHERE id_usuario = ?');
            $stmt->execute([$nuevo_nombre_usuario, $id_usuario]);

            // Actualizar contraseña si se proporcionó una nueva
            if (!empty($nueva_password)) {
                $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare('UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?');
                $stmt->execute([$password_hash, $id_usuario]);
            }

            $mensaje = "¡Datos actualizados correctamente!";
            // Actualizar nombre de usuario en la sesión si es necesario
            // $_SESSION['nombre_usuario'] = $nuevo_nombre_usuario;

        } catch(PDOException $e) {
             // Verificar si el error es por nombre de usuario duplicado
            if ($e->getCode() === '23000') { // Código de error para entrada duplicada
                 $error = "El nombre de usuario ya está en uso.";
            } else {
                $error = "Error al actualizar los datos: " . $e->getMessage();
            }
        }
    }

    // Volver a obtener los datos actualizados para mostrarlos en el formulario
     try {
        $stmt = $conexion->prepare('SELECT nombre_usuario, email FROM usuarios WHERE id_usuario = ?');
        $stmt->execute([$id_usuario]);
        $usuario = $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Error al recargar los datos del usuario después de actualizar: " . $e->getMessage());
         // No actualizamos $error para no sobrescribir el mensaje de error de la actualización si hubo uno
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lens - Modificar Datos</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="inicio-form-page"> <!-- Puedes usar una clase de estilo similar o crear una nueva -->

    <div class="login-container"> <!-- O un nuevo contenedor de estilos similar -->
         <div class="logo">
            <img src="../img/logo.png" alt="Logo Cine Lens" />
        </div>
        <h1>Modificar Datos</h1>

        <?php if ($mensaje): ?>
            <div class="mensaje-exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($usuario): ?>
            <form action="modificar_datos.php" method="POST">
                <div class="input-group">
                    <label for="nombre_usuario">Nombre de Usuario:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                     <small style="color: #555;">El email no se puede modificar.</small>
                </div>
                 <div class="input-group">
                    <label for="password">Nueva Contraseña (dejar vacío para no cambiar):</label>
                    <input type="password" id="password" name="password">
                </div>
                 <div class="input-group">
                    <label for="confirmar_password">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="confirmar_password" name="confirmar_password">
                </div>
                <button type="submit">Guardar Cambios</button>
            </form>
        <?php else: ?>
             <p>No se pudieron cargar los datos del usuario.</p>
        <?php endif; ?>

        <p style="margin-top: 20px;"><a href="perfil.php">Volver al Perfil</a></p>
    </div>

</body>
</html> 