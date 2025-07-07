<?php
session_start();

error_log("\\n--- Inicio de pelicula.php ---");
error_log("Estado inicial de la sesion: " . print_r($_SESSION, true));
error_log("Usuario ID en session: " . ($_SESSION['usuario_id'] ?? 'No definido'));

require_once '../../php/conexion.php'; // Asegúrate de que la ruta a tu archivo de conexión sea correcta

$movie_id = null;
$pelicula = null;
$error_pelicula = '';
$mensaje_reseña = '';
$error_reseña = '';

// Obtener el ID de la película de la URL
if (isset($_GET['id'])) {
    // ... existing code ...
}

// Procesar envío de reseña (si el usuario está logueado y se envió el formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reseña'])) {
    error_log("POST request received for review submission.");
    if (isset($_SESSION['usuario_id'])) {
        error_log("Usuario logueado, procesando reseña.");
        $id_usuario = $_SESSION['usuario_id'];
        $comentario = trim($_POST['comentario']);
        $puntuacion = $_POST['puntuacion']; // Asegúrate de validar este valor

        // Validar puntuación
        if (!is_numeric($puntuacion) || $puntuacion < 1 || $puntuacion > 10) {
            $error_reseña = "La puntuación debe ser un número entre 1 y 10.";
             error_log("Error de validacion: Puntuacion invalida.");
        }

        
        if (empty($comentario)) {
            $error_reseña = "El comentario no puede estar vacío.";
        }

        if (empty($error_reseña) && $movie_id) {
            try {
                // Insertar reseña en la base de datos
                $stmt_insert = $conexion->prepare('INSERT INTO resenas (id_usuario, tmdb_id, comentario, puntuacion, fecha_creacion) VALUES (?, ?, ?, ?, NOW())');
                $stmt_insert->execute([$id_usuario, $movie_id, $comentario, $puntuacion]);

                $mensaje_reseña = "¡Tu reseña ha sido enviada con éxito!";
                 error_log("Reseña insertada correctamente.");
                // Redirigir para evitar reenvío del formulario y mostrar el mensaje de éxito
                header('Location: pelicula.php?id=' . $movie_id . '#seccion-resenas');
                exit();

            } catch(PDOException $e) {
                 error_log("PDOException al insertar reseña: " . $e->getMessage());
                 // Verificar si el error es por clave duplicada (si has puesto restricción UNIQUE en id_usuario y tmdb_id)
                if ($e->getCode() === '23000') {
                     $error_reseña = "Ya has enviado una reseña para esta película.";
                } else {
                    $error_reseña = "Error al guardar la reseña: " . $e->getMessage();
                }
            }
        }
    } else {
        // Si se recibió un POST para reseña pero el usuario NO está logueado
        error_log("Intento de enviar reseña sin estar logueado. Redirigiendo...");
        header('Location: ../iniciar-sesion.php?redirect=' . urlencode($_SERVER['REQUEST_URI'])); // Redirigir a login, pasando la URL actual para volver después
        exit();
    }
}

function crearMiniaturaVertical($ruta_original, $ancho = 260, $alto = 390) {
    $info = getimagesize($ruta_original);
    if (!$info) return $ruta_original;
    $mime = $info['mime'];
    switch ($mime) {
        case 'image/jpeg': $img = imagecreatefromjpeg($ruta_original); break;
        case 'image/png': $img = imagecreatefrompng($ruta_original); break;
        case 'image/gif': $img = imagecreatefromgif($ruta_original); break;
        case 'image/webp': $img = imagecreatefromwebp($ruta_original); break;
        default: return $ruta_original;
    }
    $w = imagesx($img); $h = imagesy($img);
    $proporcion = $ancho / $alto;
    $proporcion_img = $w / $h;
    // Recorte centrado
    if ($proporcion_img > $proporcion) {
        $nuevo_w = intval($h * $proporcion);
        $nuevo_h = $h;
        $src_x = intval(($w - $nuevo_w) / 2);
        $src_y = 0;
    } else {
        $nuevo_w = $w;
        $nuevo_h = intval($w / $proporcion);
        $src_x = 0;
        $src_y = intval(($h - $nuevo_h) / 2);
    }
    $thumb = imagecreatetruecolor($ancho, $alto);
    imagecopyresampled($thumb, $img, 0, 0, $src_x, $src_y, $ancho, $alto, $nuevo_w, $nuevo_h);
    $tmp = sys_get_temp_dir() . '/thumb_' . md5($ruta_original) . '.jpg';
    imagejpeg($thumb, $tmp, 90);
    imagedestroy($img); imagedestroy($thumb);
    return $tmp;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/style.css">
    <title>Lens</title>
    <style>

.movie-poster {
    width: 400px;
    height: 600px;
}
    </style>
</head>
<body class="pelicula-page">
    <header class="top-bar">
        <img src="../../img/User.png" alt="Perfil" class="icono-perfil" />
        <img src="../../img/logo.png" alt="Logo Cine Lens" class="logo" />
        <div class="barra-busqueda">
            <input type="text" placeholder="Buscar..." />
        </div>
        <nav class="menu-superior">
            <a href="../../index.php" class="menu-link">Inicio</a>
            <a href="../../pages/catalogo.php" class="menu-link">Catalogo</a>
            <a href="../../pages/lista-comentarios.php" class="menu-link">Comentarios</a>
            <div class="barra-busqueda-nav">
                <input type="text" placeholder="Buscar..." />
                <button class="btn-buscar"><img src="../../img/busqueda-de-lupa.png" alt="Buscar"></button>
            </div>
        </nav>
        <div class="usuario-menu">
            <img src="../../img/User.png" alt="Perfil" class="icono-perfil-nav" />
            <div class="nav-buttons">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="../perfil.php" class="btn-acceder">Perfil</a>
                <?php else: ?>
                    <a href="../iniciar-sesion.php" class="btn-acceder">Acceder</a>
                <?php endif; ?>
            </div>
            <img src="../../img/flecha-correcta.png" alt="Desplegar" class="flecha-usuario" />
        </div>
    </header>

    <main class="contenido">
        <section class="movie-detail-page">
            <div class="movie-poster-container">
                <?php
                $src_img = '../../img/placeholder.png';
                if (!empty($pelicula['imagen_portada'])) {
                    if (strpos($pelicula['imagen_portada'], 'http') === 0) {
                        $src_img = $pelicula['imagen_portada'];
                    } else {
                        $ruta_local = '../../' . ltrim($pelicula['imagen_portada'], '/');
                        if (file_exists($ruta_local)) {
                            $src_img = crearMiniaturaVertical($ruta_local);
                        }
                    }
                }
                ?>
                <img 
                    src="<?php echo $src_img; ?>"
                    alt="<?php echo htmlspecialchars($pelicula['titulo'] ?? ''); ?>"
                    class="movie-poster"
                    onerror="this.src='../../img/placeholder.png';"
                >
                <div class="movie-overlay">
                    <div class="comment-icon-placeholder">
                        <img src="../../img/msj.png" alt="Comentarios">
                    </div>
                </div>
            </div>

            <section class="movie-info-section">
                <div class="movie-rating">
                    <span class="star"></span>
                    <span class="star"></span>
                    <span class="star"></span>
                    <span class="star"></span>
                    <span class="star"></span>
                </div>
                <div class="movie-metadata">
                    <h1 class="movie-title"></h1>
                    <span class="movie-year"></span>
                    <span class="movie-genre"></span>
                    <span class="movie-duration"></span>
                </div>
            
                <button class="buy-tickets-button" id="btn-comprar-entradas">Comprar Entradas</button>
                <button class="boton-reseñas" id="btn-reseñas">Enviar Reseñas</button>

                <p class="movie-synopsis"></p>

                <div class="video-player-placeholder">
                    <img src="" alt="Miniatura del trailer" class="trailer-thumbnail">
                    <img src="../../img/boton-de-play.png" alt="Reproducir trailer" class="play-icon">
                    <div class="video-message"></div>
                </div>

                <h2 class="actors-title">Actores</h2>
                <p class="movie-actors-list"></p>
            </section>
        </section>
    </main>

    <nav class="menu-inferior">
        <a href="../../index.php"><button> <img src="../../img/casa.png" alt="casa-index"></button></a>
        <a href="../catalogo.php"><button> <img src="../../img/categoria.png" alt="catalogo"></button></a>
        <a href="../lista-comentarios.php"><button> <img src="../../img/msj.png" alt=""></button></a>
    </nav>

    <script src="../../js/pelicula.js"></script>
    <script src="../../js/search.js"></script>
</body>
</html>