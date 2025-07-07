<?php
header('Content-Type: application/json');
require_once 'conexion.php';

try {
    // Consulta para obtener todos los géneros de la tabla unificada 'peliculas'
    $sql = "SELECT genero 
            FROM peliculas 
            WHERE estado = 'activa' AND genero IS NOT NULL AND genero != ''";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $generos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar los géneros para separarlos por coma y obtener valores únicos
    $todos_los_generos = [];
    foreach ($generos as $fila) {
        $generos_separados = array_map('trim', explode(',', $fila['genero']));
        $todos_los_generos = array_merge($todos_los_generos, $generos_separados);
    }
    $generos_unicos = array_unique(array_filter($todos_los_generos));
    sort($generos_unicos);

    $response = [
        'generos' => array_values($generos_unicos)
    ];
    
    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener géneros: ' . $e->getMessage()]);
}
?> 