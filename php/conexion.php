<?php
$host = 'localhost';
$dbname = 'lens';
$username = 'root';
$password = '';

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("Error de conexión a la base de datos. Por favor, intente más tarde.");
}

function validar_usuario_sesion($conexion) {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../pages/iniciar-sesion.php");
        exit();
    }
    $id_usuario = $_SESSION['usuario_id'];
    $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    if ($stmt->rowCount() === 0) {
        session_destroy();
        header("Location: ../pages/iniciar-sesion.php");
        exit();
    }
}

function validar_admin($conexion) {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../pages/iniciar-sesion.php");
        exit();
    }
    $id_usuario = $_SESSION['usuario_id'];
    $stmt = $conexion->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();
    
    if (!$usuario || $usuario['rol'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
}

function es_admin($conexion, $id_usuario) {
    $stmt = $conexion->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();
    return $usuario && $usuario['rol'] === 'admin';
}
?>
