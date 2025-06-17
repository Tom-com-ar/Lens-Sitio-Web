<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que la ruta a tu archivo de conexión sea correcta

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../pages/iniciar-sesion.php'); // Redirigir si no está logueado
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$mensaje = '';
$error = '';

// Proceso de borrado (esto es una operación delicada, considera añadir confirmación)
// Aquí asumimos que la solicitud POST confirma la acción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_borrar'])) {
    try {
        // Iniciar una transacción para asegurar que todas las operaciones se completen o ninguna
        $conexion->beginTransaction();

        // Elimina primero los comentarios
        $stmt = $conexion->prepare('DELETE FROM comentarios WHERE id_usuario = ?');
        $stmt->execute([$id_usuario]);

        // Elimina primero las entradas
        $stmt = $conexion->prepare('DELETE FROM entradas WHERE id_usuario = ?');
        $stmt->execute([$id_usuario]);

        // Elimina el usuario
        $stmt = $conexion->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
        $stmt->execute([$id_usuario]);

        // Confirmar la transacción
        $conexion->commit();

        // Destruir la sesión después de borrar la cuenta
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httplonly"]
            );
        }
        session_destroy();

        // Redirigir a una página de confirmación o a la página principal
        header('Location: ../pages/iniciar-sesion.php?cuenta_borrada=true'); // O la página que desees
        exit();

    } catch(PDOException $e) {
        // Revertir la transacción en caso de error
        $conexion->rollBack();
        error_log("Error al borrar la cuenta para usuario " . $id_usuario . ": " . $e->getMessage());
        $_SESSION['error_borrar'] = "Hubo un error al intentar borrar tu cuenta.";
        header('Location: ../pages/perfil.php');
        exit();
    }
} else {
    $_SESSION['error_borrar'] = "Solicitud inválida para borrar la cuenta.";
    header('Location: ../pages/perfil.php');
    exit();
}
?>