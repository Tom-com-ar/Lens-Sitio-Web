<?php
include '../../php/conexion.php'; // Ajusta la ruta si es necesario

// Consulta para borrar funciones cuyo horario ya pasó
$sql = "DELETE FROM funciones WHERE fecha_hora < NOW()";
$result = $conn->query($sql);

if ($result) {
    echo "Funciones pasadas eliminadas correctamente.";
} else {
    echo "Error al eliminar funciones: " . $conn->error;
}

$conn->close();
?>
