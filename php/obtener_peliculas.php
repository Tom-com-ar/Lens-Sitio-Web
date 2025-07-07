<?php
header('Content-Type: application/json');
require_once 'conexion.php';

try {
    // Parámetros de paginación
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 20;
    $offset = ($page - 1) * $per_page;
    
    // Parámetros de filtrado
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $genero = isset($_GET['genero']) ? trim($_GET['genero']) : '';
    $ano_desde = isset($_GET['ano_desde']) ? intval($_GET['ano_desde']) : null;
    $ano_hasta = isset($_GET['ano_hasta']) ? intval($_GET['ano_hasta']) : null;
    $duracion = isset($_GET['duracion']) ? $_GET['duracion'] : '';
    
    // Parámetro de ordenamiento
    $orden = isset($_GET['orden']) ? $_GET['orden'] : 'valoracion'; // Por defecto ordenar por valoración
    
    // Construir condiciones WHERE
    $where_conditions = ["p.estado = 'activa'"];
    $params = [];
    $count_params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "p.titulo LIKE ?";
        $params[] = "%$search%";
    }
    
    if (!empty($genero)) {
        $where_conditions[] = "p.genero LIKE ?";
        $params[] = "%$genero%";
    }
    
    if ($ano_desde !== null) {
        $where_conditions[] = "p.fecha_estreno >= ?";
        $params[] = $ano_desde;
    }
    
    if ($ano_hasta !== null) {
        $where_conditions[] = "p.fecha_estreno <= ?";
        $params[] = $ano_hasta;
    }
    
    if (!empty($duracion)) {
        switch ($duracion) {
            case '1':
                $where_conditions[] = "p.duracion <= 120";
                break;
            case '2':
                $where_conditions[] = "p.duracion BETWEEN 121 AND 150";
                break;
            case '3':
                $where_conditions[] = "p.duracion > 150";
                break;
        }
    }
    
    $where_clause = count($where_conditions) > 0 ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    $count_params = $params;

    // Consulta para contar total de películas
    $count_sql = "SELECT COUNT(*) as total FROM peliculas p $where_clause";
    
    $count_stmt = $conexion->prepare($count_sql);
    $count_stmt->execute($count_params);
    $total_peliculas = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_peliculas / $per_page);
    
    // Construir ORDER BY según el parámetro
    $order_clause = '';
    switch ($orden) {
        case 'valoracion':
            $order_clause = 'ORDER BY valoracion_promedio DESC, p.fecha_agregada DESC';
            break;
        case 'fecha':
            $order_clause = 'ORDER BY p.fecha_agregada DESC';
            break;
        case 'titulo':
            $order_clause = 'ORDER BY p.titulo ASC';
            break;
        case 'ano':
            $order_clause = 'ORDER BY p.fecha_estreno DESC';
            break;
        default:
            $order_clause = 'ORDER BY valoracion_promedio DESC, p.fecha_agregada DESC';
    }
    
    // Consulta principal para obtener películas con valoración promedio
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
                p.estado,
                p.fecha_agregada,
                COALESCE(AVG(c.valoracion), 0) as valoracion_promedio,
                COUNT(c.id_comentario) as total_valoraciones
            FROM peliculas p
            LEFT JOIN comentarios c ON p.id_pelicula = c.id_pelicula
            $where_clause
            GROUP BY p.id_pelicula
            $order_clause
            LIMIT ? OFFSET ?";
    
    $params[] = $per_page;
    $params[] = $offset;
    
    $stmt = $conexion->prepare($sql);
    
    // Bind de parámetros
    $i = 1;
    foreach ($count_params as $param) {
        $stmt->bindValue($i++, $param);
    }
    $stmt->bindValue($i++, $per_page, PDO::PARAM_INT);
    $stmt->bindValue($i++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear respuesta según la estructura esperada
    $response = [
        'page' => $page,
        'results' => $peliculas,
        'total_pages' => $total_pages,
        'total_results' => $total_peliculas,
        'orden_actual' => $orden
    ];
    
    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener películas: ' . $e->getMessage()]);
}
?> 