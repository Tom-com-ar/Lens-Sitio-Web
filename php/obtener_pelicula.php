<?php
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere ID de la película.']);
    exit;
}

$id_pelicula = intval($_GET['id']);

try {
    $sql = "SELECT 
                p.id_pelicula as id,
                p.origen as tipo,
                p.tmdb_id,
                p.titulo as title,
                p.descripcion as overview,
                p.genero,
                p.fecha_estreno as release_date,
                p.imagen_portada as poster_path,
                p.trailer_url,
                p.actores,
                p.clasificacion,
                p.duracion as runtime,
                COALESCE(AVG(c.valoracion), 0) as valoracion_promedio,
                COUNT(c.id_comentario) as total_valoraciones
            FROM peliculas p
            LEFT JOIN comentarios c ON p.id_pelicula = c.id_pelicula
            WHERE p.id_pelicula = :id_pelicula AND p.estado = 'activa'
            GROUP BY p.id_pelicula";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_pelicula', $id_pelicula, PDO::PARAM_INT);
    $stmt->execute();
    $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pelicula) {
        // Formatear géneros para que coincida con la estructura esperada
        $generos_array = [];
        if (!empty($pelicula['genero'])) {
            $generos = explode(',', $pelicula['genero']);
            foreach ($generos as $genero) {
                $generos_array[] = ['name' => trim($genero)];
            }
        }
        $pelicula['genres'] = $generos_array;
        
        // Formatear fecha de estreno
        if (!empty($pelicula['release_date'])) {
            $pelicula['release_date'] = $pelicula['release_date'] . '-01-01'; // Agregar mes y día ficticio
        }
        
        // Para películas manuales, asegurar que la imagen tenga la ruta correcta
        if ($pelicula['tipo'] === 'manual' && !empty($pelicula['poster_path'])) {
            // Si ya es una URL completa, no hacer nada
            if (str_starts_with($pelicula['poster_path'], 'http')) {
                // Ya es una URL completa, no modificar
            } else {
                // Es una ruta relativa, agregar la ruta correcta según el contexto
                $pelicula['poster_path'] = '../../' . $pelicula['poster_path'];
            }
        }
        
        // Mantener compatibilidad con vote_average para películas de la API
        if ($pelicula['tipo'] === 'api' && $pelicula['tmdb_id']) {
            // Para películas de la API, mantener vote_average como estaba
            $pelicula['vote_average'] = $pelicula['valoracion_promedio'] * 2; // Convertir de escala 1-5 a 1-10
        } else {
            // Para películas manuales, usar la valoración promedio
            $pelicula['vote_average'] = $pelicula['valoracion_promedio'] * 2; // Convertir de escala 1-5 a 1-10
        }
        
        // Asegurar que todos los campos estén presentes para mantener consistencia
        $pelicula['total_valoraciones'] = $pelicula['total_valoraciones'] ?? 0;
        
        // Para películas manuales, asegurar que los campos opcionales estén presentes
        if ($pelicula['tipo'] === 'manual') {
            $pelicula['trailer_url'] = $pelicula['trailer_url'] ?? '';
            $pelicula['actores'] = $pelicula['actores'] ?? '';
            $pelicula['clasificacion'] = $pelicula['clasificacion'] ?? 'G';
        }
        
        echo json_encode($pelicula);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Película no encontrada o inactiva.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los detalles de la película: ' . $e->getMessage()]);
}
?> 