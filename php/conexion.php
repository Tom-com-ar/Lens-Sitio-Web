<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$baseDatos = "lens";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$baseDatos", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->exec("SET NAMES utf8mb4");
    echo "Conexión exitosa";
    
} catch(PDOException $e) {
    echo "Error en la conexión";
    die();
}
?>
