<?php
session_start();
require_once '../../php/conexion.php';

// Verificar que el usuario sea administrador
validar_admin($conexion);

$mensaje = '';
$error = '';
$resultados_busqueda = [];

// Procesar búsqueda
if (isset($_POST['buscar'])) {
    $api_key = 'e6822a7ed386f7102b6a857ea5e3c17f';
    $query = trim($_POST['query']);
    
    if (!empty($query)) {
        try {
            $url = "https://api.themoviedb.org/3/search/movie?api_key={$api_key}&language=es-ES&query=" . urlencode($query);
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            if ($data && isset($data['results'])) {
                $resultados_busqueda = $data['results'];
            } else {
                $error = 'No se encontraron resultados para tu búsqueda.';
            }
        } catch (Exception $e) {
            $error = 'Error al buscar en la API: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor ingresa un término de búsqueda.';
    }
}

// Procesar agregar película
if (isset($_POST['agregar_pelicula'])) {
    $api_key = 'e6822a7ed386f7102b6a857ea5e3c17f';
    $tmdb_id = $_POST['tmdb_id'];
    
    try {
        // Obtener detalles completos de la película
        $url = "https://api.themoviedb.org/3/movie/{$tmdb_id}?api_key={$api_key}&language=es-ES";
        $response = file_get_contents($url);
        $pelicula_detalle = json_decode($response, true);
        
        if ($pelicula_detalle) {
            $titulo = $pelicula_detalle['title'];
            $descripcion = $pelicula_detalle['overview'];
            $fecha_estreno = intval(substr($pelicula_detalle['release_date'], 0, 4));
            $imagen_portada = $pelicula_detalle['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $pelicula_detalle['poster_path'] : '';
            $clasificacion = $pelicula_detalle['adult'] ? 'R' : 'G';
            $duracion = $pelicula_detalle['runtime'] ?? null;
            // Obtener actores principales
            $actores = obtenerActoresPrincipales($tmdb_id, $api_key);
            // Obtener trailer
            $trailer_url = obtenerTrailerYoutube($tmdb_id, $api_key);
            
            // Obtener géneros
            $generos = [];
            if (isset($pelicula_detalle['genres'])) {
                foreach ($pelicula_detalle['genres'] as $genero) {
                    $generos[] = $genero['name'];
                }
            }
            $genero = implode(', ', $generos);
            
            // Verificar si la película ya existe en la tabla unificada
            $stmt = $conexion->prepare("SELECT id_pelicula FROM peliculas WHERE tmdb_id = ?");
            $stmt->execute([$tmdb_id]);
            
            if ($stmt->rowCount() > 0) {
                $error = "La película '{$titulo}' ya existe en la base de datos.";
            } else {
                // Insertar nueva película con origen 'api'
                $stmt = $conexion->prepare("
                    INSERT INTO peliculas (origen, tmdb_id, titulo, descripcion, genero, fecha_estreno, duracion, imagen_portada, trailer_url, actores, clasificacion, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute(['api', $tmdb_id, $titulo, $descripcion, $genero, $fecha_estreno, $duracion, $imagen_portada, $trailer_url, $actores, $clasificacion, 'activa']);
                $mensaje = "Película '{$titulo}' agregada exitosamente.";
            }
        } else {
            $error = 'Error al obtener detalles de la película desde la API.';
        }
    } catch (Exception $e) {
        $error = 'Error al agregar la película: ' . $e->getMessage();
    }
}

// Obtener estadísticas
try {
    $total_peliculas = $conexion->query("SELECT COUNT(*) FROM peliculas")->fetchColumn();
    $total_api = $conexion->query("SELECT COUNT(*) FROM peliculas WHERE origen = 'api'")->fetchColumn();
    $total_manual = $conexion->query("SELECT COUNT(*) FROM peliculas WHERE origen = 'manual'")->fetchColumn();
} catch (PDOException $e) {
    $error .= ' Error al cargar estadísticas.';
    $total_peliculas = $total_api = $total_manual = 0;
}

// --- FUNCIONES AUXILIARES ---
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
    <title>Buscar Películas - Panel de Administrador</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <style>
        .search-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .search-form {
            background: #black;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .search-input {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .search-input input[type="text"] {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .search-input input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
        }
        
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .movie-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .movie-card:hover {
            transform: translateY(-5px);
        }
        
        .movie-poster {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        .movie-info {
            padding: 20px;
        }
        
        .movie-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .movie-year {
            color: #666;
            margin-bottom: 10px;
        }
        
        .movie-overview {
            color: #555;
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .movie-genres {
            color: #007bff;
            font-size: 12px;
            margin-bottom: 15px;
        }
        
        .btn-agregar {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-agregar:hover {
            background: #218838;
        }
        
        .btn-agregar:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .no-results {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 18px;
        }
        
        .stats-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background:rgb(0, 0, 0);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color:rgb(255, 255, 255);
        }
        
        .stat-label {
            font-size: 12px;
            color: white;
            margin-top: 5px;
        }
    </style>
</head>
<body class="index-page">
    <div class="admin-container" style="max-width:100%;width:100%;padding:16px;box-sizing:border-box;">
        <div class="admin-header" style="min-width:0;">
            <h1 style="word-break:break-word;">Buscar y Agregar Películas desde la API</h1>
        </div>
        
        <?php if (isset($mensaje) && $mensaje): ?>
            <div class="mensaje exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error) && $error): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="stats-bar">
            <div class="stat-item">
                <p class="stat-number"><?= $total_peliculas ?></p>
                <p class="stat-label">Total en BD</p>
            </div>
            <div class="stat-item">
                <p class="stat-number"><?= $total_api ?></p>
                <p class="stat-label">Desde API</p>
            </div>
            <div class="stat-item">
                <p class="stat-number"><?= $total_manual ?></p>
                <p class="stat-label">Manuales</p>
            </div>
        </div>
        <div style="text-align: center;width:100%;margin-top:16px;">
            <a href="panel_admin.php" class="btn-back" style="width:100%;max-width:300px;">&larr; Volver al panel principal</a>
        </div>
        <div class="form-container" style="min-width:0;">
            <form method="POST" style="display:flex;flex-wrap:wrap;gap:12px;width:100%;max-width:600px;margin:0 auto;">
                <div class="form-group" style="flex:1 1 200px;min-width:0;width:100%;">
                    <label for="query">Buscar película</label>
                    <input type="text" name="query" id="query" placeholder="Título o palabra clave" required style="width:100%;">
                </div>
                <div class="form-actions" style="width:100%;">
                    <button type="submit" name="buscar" class="btn-submit" style="width:100%;max-width:200px;">Buscar</button>
                </div>
            </form>
        </div>
        
        <?php if (!empty($resultados_busqueda)): ?>
            <div class="results-grid">
                <?php foreach ($resultados_busqueda as $pelicula): ?>
                    <div class="movie-card">
                        <img 
                          src="<?php echo (strpos($pelicula['poster_path'], 'http') === 0 ? $pelicula['poster_path'] : 'https://image.tmdb.org/t/p/w500' . $pelicula['poster_path']); ?>"
                          alt="<?php echo htmlspecialchars($pelicula['title']); ?>"
                          class="movie-poster"
                          onerror="this.src='../../img/placeholder.png';"
                        />
                        <div class="movie-info">
                            <div class="movie-title"><?php echo htmlspecialchars($pelicula['title']); ?></div>
                            <div class="movie-year">
                                <?php echo $pelicula['release_date'] ? substr($pelicula['release_date'], 0, 4) : 'N/A'; ?>
                            </div>
                            <div class="movie-overview">
                                <?php echo htmlspecialchars($pelicula['overview'] ?: 'Sin descripción disponible'); ?>
                            </div>
                            <div class="movie-genres">
                                <?php 
                                if (isset($pelicula['genre_ids']) && !empty($pelicula['genre_ids'])) {
                                    echo 'Géneros: ' . implode(', ', array_map(function($id) {
                                        $generos = [
                                            28 => 'Acción', 12 => 'Aventura', 16 => 'Animación', 35 => 'Comedia',
                                            80 => 'Crimen', 99 => 'Documental', 18 => 'Drama', 10751 => 'Familiar',
                                            14 => 'Fantasía', 36 => 'Historia', 27 => 'Terror', 10402 => 'Música',
                                            9648 => 'Misterio', 10749 => 'Romance', 878 => 'Ciencia Ficción',
                                            10770 => 'Película de TV', 53 => 'Suspenso', 10752 => 'Guerra', 37 => 'Western'
                                        ];
                                        return $generos[$id] ?? 'Desconocido';
                                    }, $pelicula['genre_ids']));
                                } else {
                                    echo 'Géneros no disponibles';
                                }
                                ?>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="tmdb_id" value="<?php echo $pelicula['id']; ?>">
                                <button type="submit" name="agregar_pelicula" class="btn-agregar">
                                    ➕ Agregar a Base de Datos
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($_POST['buscar'])): ?>
            <div>No se encontraron resultados.</div>
        <?php endif; ?>
    </div>
</body>
</html> 