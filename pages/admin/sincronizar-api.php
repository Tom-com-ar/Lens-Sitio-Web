<?php
session_start();
require_once '../../php/conexion.php';

// Verificar que el usuario sea administrador
validar_admin($conexion);

$mensaje = '';
$error = '';

// Procesar sincronización
if (isset($_POST['sincronizar'])) {
    $api_key = 'e6822a7ed386f7102b6a857ea5e3c17f';
    $categoria = $_POST['categoria'] ?? 'popular';
    $cantidad_paginas = intval($_POST['cantidad_paginas'] ?? 1);
    $solo_nuevas = isset($_POST['solo_nuevas']);
    
    try {
        $peliculas_sincronizadas = 0;
        $peliculas_actualizadas = 0;
        $peliculas_omitidas = 0;
        
        // URLs de las diferentes categorías
        $urls_categorias = [
            'popular' => 'https://api.themoviedb.org/3/movie/popular',
            'top_rated' => 'https://api.themoviedb.org/3/movie/top_rated',
            'now_playing' => 'https://api.themoviedb.org/3/movie/now_playing'
        ];
        
        $base_url = $urls_categorias[$categoria];
        
        // Procesar múltiples páginas
        $peliculas_procesadas = 0;
        $peliculas_objetivo = $cantidad_paginas * 20; // Cantidad exacta que queremos
        $pagina = 1;
        $max_paginas = $cantidad_paginas * 3; // Límite máximo de páginas para evitar bucles infinitos
        
        while ($peliculas_sincronizadas + $peliculas_actualizadas < $peliculas_objetivo && $pagina <= $max_paginas) {
            $url = "{$base_url}?api_key={$api_key}&language=es-ES&page={$pagina}";
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            if ($data && isset($data['results'])) {
                $i = 0;
                foreach ($data['results'] as $pelicula_api) {
                    // Validaciones básicas
                    if (empty($pelicula_api['title']) || 
                        empty($pelicula_api['overview']) || 
                        empty($pelicula_api['release_date']) || 
                        empty($pelicula_api['genre_ids']) || 
                        count($pelicula_api['genre_ids']) === 0 ||
                        empty($pelicula_api['poster_path']) ||
                        empty($pelicula_api['id']) ||
                        !isset($pelicula_api['popularity']) || 
                        $pelicula_api['popularity'] <= 0 ||
                        !isset($pelicula_api['vote_average']) ||
                        !isset($pelicula_api['vote_count']) || 
                        $pelicula_api['vote_count'] < 1) {
                        continue;
                    }
                    $i++;
                    $tmdb_id = $pelicula_api['id'];
                    $titulo = $pelicula_api['title'];
                    $descripcion = $pelicula_api['overview'];
                    $fecha_estreno = intval(substr($pelicula_api['release_date'], 0, 4));
                    $imagen_portada = $pelicula_api['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $pelicula_api['poster_path'] : '';
                    $clasificacion = $pelicula_api['adult'] ? 'R' : 'G';
                    // Obtener duración desde la API de detalles
                    $detalle = obtenerDetallePelicula($tmdb_id, $api_key);
                    $duracion = $detalle['runtime'] ?? null;
                    // Obtener actores principales
                    $actores = obtenerActoresPrincipales($tmdb_id, $api_key);
                    // Obtener trailer
                    $trailer_url = obtenerTrailerYoutube($tmdb_id, $api_key);
                    // Mapear géneros
                    $generos = [];
                    if (isset($pelicula_api['genre_ids'])) {
                        foreach ($pelicula_api['genre_ids'] as $genre_id) {
                            $generos[] = obtenerNombreGenero($genre_id, $api_key);
                        }
                    }
                    $genero = implode(', ', array_filter($generos));
                    if (empty($genero)) continue;
                    // Verificar si la película ya existe
                    $stmt = $conexion->prepare("SELECT id_pelicula FROM peliculas WHERE tmdb_id = ? AND origen = 'api'");
                    $stmt->execute([$tmdb_id]);
                    if ($stmt->rowCount() > 0) {
                        if ($solo_nuevas) {
                            $peliculas_omitidas++;
                            continue;
                        }
                        $stmt = $conexion->prepare("
                            UPDATE peliculas 
                            SET titulo = ?, descripcion = ?, genero = ?, fecha_estreno = ?, 
                                imagen_portada = ?, clasificacion = ?, duracion = ?, trailer_url = ?, actores = ?, fecha_actualizada = NOW()
                            WHERE tmdb_id = ? AND origen = 'api'
                        ");
                        $stmt->execute([$titulo, $descripcion, $genero, $fecha_estreno, $imagen_portada, $clasificacion, $duracion, $trailer_url, $actores, $tmdb_id]);
                        $peliculas_actualizadas++;
                    } else {
                        $stmt = $conexion->prepare("
                            INSERT INTO peliculas (origen, tmdb_id, titulo, descripcion, genero, fecha_estreno, duracion, imagen_portada, trailer_url, actores, clasificacion, estado)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute(['api', $tmdb_id, $titulo, $descripcion, $genero, $fecha_estreno, $duracion, $imagen_portada, $trailer_url, $actores, $clasificacion, 'activa']);
                        $peliculas_sincronizadas++;
                    }
                    // if ($i % 10 == 0) usleep(50000); // Eliminado para mayor velocidad
                    if ($peliculas_sincronizadas + $peliculas_actualizadas >= $peliculas_objetivo) {
                        break 2;
                    }
                }
            }
            $pagina++;
            // usleep(100000); // Eliminado para mayor velocidad
        }
        
        $total_procesadas = $peliculas_sincronizadas + $peliculas_actualizadas;
        $mensaje = "Sincronización completada: {$peliculas_sincronizadas} películas nuevas, {$peliculas_actualizadas} actualizadas, {$peliculas_omitidas} omitidas. Total procesadas: {$total_procesadas} de {$peliculas_objetivo} solicitadas.";
        
    } catch (Exception $e) {
        $error = 'Error durante la sincronización: ' . $e->getMessage();
    }
}

// Función para obtener la duración de una película específica
function obtenerDuracionPelicula($tmdb_id, $api_key) {
    try {
        $url = "https://api.themoviedb.org/3/movie/{$tmdb_id}?api_key={$api_key}&language=es-ES";
        $response = file_get_contents($url);
        $pelicula_detalle = json_decode($response, true);
        
        if ($pelicula_detalle && isset($pelicula_detalle['runtime'])) {
            return $pelicula_detalle['runtime'];
        }
    } catch (Exception $e) {
        // Si hay error, retornar null
        return null;
    }
    
    return null;
}

// Función para obtener el nombre del género
function obtenerNombreGenero($genre_id, $api_key) {
    static $generos_cache = [];
    
    if (!isset($generos_cache[$genre_id])) {
        try {
            $url = "https://api.themoviedb.org/3/genre/movie/list?api_key={$api_key}&language=es-ES";
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            if ($data && isset($data['genres'])) {
                foreach ($data['genres'] as $genero) {
                    $generos_cache[$genero['id']] = $genero['name'];
                }
            }
        } catch (Exception $e) {
            return '';
        }
    }
    
    return $generos_cache[$genre_id] ?? '';
}

// Obtener estadísticas de la tabla unificada
$stmt_api = $conexion->prepare("SELECT COUNT(*) as total FROM peliculas WHERE origen = 'api'");
$stmt_api->execute();
$total_api = $stmt_api->fetch()['total'];

$stmt_manual = $conexion->prepare("SELECT COUNT(*) as total FROM peliculas WHERE origen = 'manual'");
$stmt_manual->execute();
$total_manual = $stmt_manual->fetch()['total'];

// --- FUNCIONES AUXILIARES ---
function obtenerDetallePelicula($tmdb_id, $api_key) {
    $url = "https://api.themoviedb.org/3/movie/{$tmdb_id}?api_key={$api_key}&language=es-ES";
    $response = @file_get_contents($url);
    return $response ? json_decode($response, true) : [];
}
function obtenerActoresPrincipales($tmdb_id, $api_key) {
    $url = "https://api.themoviedb.org/3/movie/{$tmdb_id}/credits?api_key={$api_key}&language=es-ES";
    $response = @file_get_contents($url);
    $data = $response ? json_decode($response, true) : [];
    if (!empty($data['cast'])) {
        $nombres = array_column(array_slice($data['cast'], 0, 5), 'name');
        return implode(', ', $nombres);
    }
    return null;
}
function obtenerTrailerYoutube($tmdb_id, $api_key) {
    $url = "https://api.themoviedb.org/3/movie/{$tmdb_id}/videos?api_key={$api_key}&language=es-ES";
    $response = @file_get_contents($url);
    $data = $response ? json_decode($response, true) : [];
    if (!empty($data['results'])) {
        foreach ($data['results'] as $video) {
            if ($video['site'] === 'YouTube' && $video['type'] === 'Trailer') {
                return 'https://www.youtube.com/watch?v=' . $video['key'];
            }
        }
    }
    return null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronizar API - Panel de Administrador</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body class="index-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1>🔄 Sincronizar Películas de la API</h1>
            <p>Obtener y actualizar películas desde TMDB con opciones personalizadas</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-number"><?php echo $total_api; ?></p>
                <p class="stat-label">Películas de la API</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-number"><?php echo $total_manual; ?></p>
                <p class="stat-label">Películas Manuales</p>
            </div>
        </div>
        
        <div class="form-container" >
            <h3 >📡 Configuración de Sincronización</h3>

            
            <form method="POST" action="" onsubmit="return confirm('¿Estás seguro de que quieres sincronizar las películas de la API?');">
                <div class="form-group">
                    <label for="categoria"style="margin-top:20px;">Categoría de Películas:</label>
                    <select name="categoria" id="categoria" required>
                        <option value="popular">🎬 Populares</option>
                        <option value="top_rated">⭐ Mejor Valoradas</option>
                        <option value="now_playing">🎭 En Cartelera</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cantidad_paginas">Cantidad de Páginas:</label>
                    <select name="cantidad_paginas" id="cantidad_paginas" required>
                        <option value="1">20 películas</option>
                        <option value="2">40 películas</option>
                        <option value="3">60 películas</option>
                        <option value="5">100 películas</option>
                        <option value="10">200 películas</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="solo_nuevas" id="solo_nuevas">
                        Solo agregar películas nuevas (no actualizar existentes)
                    </label>
                </div>
                
                <div class="form-actions">
                    <a href="panel_admin.php" class="btn-back">← Volver al Panel</a>
                    <button type="submit" name="sincronizar" class="btn-submit">🔄 Sincronizar API</button>
                </div>
            </form>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="panel-peliculas.php" class="btn-add">📋 Ver Todas las Películas</a>
            <a href="buscar-pelicula-api.php" class="btn-add" style="background:rgb(255, 60, 0);">🔍 Buscar Película Específica</a>
        </div>
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selecciona los mensajes de éxito y error
        const mensaje = document.querySelector('.mensaje.exito');
        const error = document.querySelector('.mensaje.error');
        // Si existen, los oculta después de 3 segundos (3000 ms)
        if (mensaje) {
            setTimeout(() => mensaje.style.display = 'none', 3000);
        }
        if (error) {
            setTimeout(() => error.style.display = 'none', 3000);
        }
    });
    </script>
</html> 