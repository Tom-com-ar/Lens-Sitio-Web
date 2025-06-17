<?php
session_start();

// Verificar si es una petición AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');
}

if (!isset($_SESSION['usuario_id'])) {
    if ($isAjax) {
        echo json_encode(['error' => 'Debes iniciar sesión para comentar']);
        exit;
    } else {
        header('Location: iniciar-sesion.php');
        exit();
    }
}

require_once '../php/conexion.php';

$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$tmdb_id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['tmdb_id']) ? $_POST['tmdb_id'] : null);

// Log para depuración
error_log("Método de solicitud: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));

// --- RESPUESTA AJAX PARA OBTENER COMENTARIOS ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tmdb_id'])) {
    $tmdb_id = $_GET['tmdb_id'];
    try {
        $stmt = $conexion->prepare("
            SELECT c.*, u.nombre_usuario 
            FROM comentarios c 
            JOIN usuarios u ON c.id_usuario = u.id_usuario 
            WHERE c.tmdb_id = ?
            ORDER BY c.fecha_comentario DESC
        ");
        $stmt->execute([$tmdb_id]);
        $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['comentarios' => $comentarios]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al cargar los comentarios: ' . $e->getMessage()]);
        exit;
    }
}

// --- RESPUESTA AJAX PARA ENVIAR RESEÑA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['error' => 'Debes iniciar sesión para comentar']);
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'];
    $tmdb_id = $_POST['tmdb_id'];
    $comentario = trim($_POST['comentario']);
    $valoracion = (int)$_POST['valoracion'];
    $fecha_actual = date('Y-m-d H:i:s');

    try {
        $tmdb_id = $_POST['tmdb_id'];
        $nombre_pelicula = obtener_nombre_pelicula($tmdb_id);

        $sql = "INSERT INTO comentarios (id_usuario, tmdb_id, nombre_pelicula, comentario, valoracion, fecha_comentario) 
                VALUES (:usuario_id, :tmdb_id, :nombre_pelicula, :comentario, :valoracion, :fecha)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':tmdb_id', $tmdb_id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre_pelicula', $nombre_pelicula, PDO::PARAM_STR);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->bindParam(':valoracion', $valoracion, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha_actual, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'mensaje' => 'Comentario guardado correctamente']);
        } else {
            echo json_encode(['error' => 'Error al guardar el comentario']);
        }
        exit;
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al guardar el comentario: ' . $e->getMessage()]);
        exit;
    }
}

// Procesar el formulario de reseña
if (isset($_SESSION['usuario_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reseña'])) {
    $comentario = trim($_POST['comentario']);
    $valoracion = (int)$_POST['valoracion'];
    $tmdb_id = $_POST['tmdb_id'];
    $fecha_actual = date('Y-m-d H:i:s');
    
    try {
        $stmt = $conexion->prepare("INSERT INTO comentarios (id_usuario, tmdb_id, nombre_pelicula, comentario, valoracion, fecha_comentario) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$usuario_id, $tmdb_id, $nombre_pelicula, $comentario, $valoracion]);
        header("Location: reseñas.php?id=" . urlencode($tmdb_id));
        exit();
    } catch (PDOException $e) {
        $error = "Error al guardar el comentario: " . $e->getMessage();
    }
}

try {
    $stmt = $conexion->prepare("
        SELECT c.*, u.nombre_usuario 
        FROM comentarios c 
        JOIN usuarios u ON c.id_usuario = u.id_usuario 
        WHERE c.tmdb_id = ?
        ORDER BY c.fecha_comentario DESC
    ");
    $stmt->execute([$tmdb_id]);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar los comentarios: " . $e->getMessage();
    $comentarios = [];
}

function obtener_nombre_pelicula($tmdb_id, $apiKey = 'e6822a7ed386f7102b6a857ea5e3c17f') {
    $url = "https://api.themoviedb.org/3/movie/$tmdb_id?api_key=$apiKey&language=es-ES";
    $json = @file_get_contents($url);
    if ($json !== false) {
        $data = json_decode($json, true);
        return $data['title'] ?? 'Título no disponible';
    }
    return 'Título no disponible';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lens - Dejar Reseña</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="reseñas-page">
    <header class="top-bar">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="./perfil.php"><img src="../img/User.png" alt="Perfil" class="icono-perfil" /></a>
        <?php else: ?>
            <a href="./iniciar-sesion.php"><img src="../img/User.png" alt="Perfil" class="icono-perfil" /></a>
        <?php endif; ?>
        <img src="../img/logo.png" alt="Logo Cine Lens" class="logo" />
        <div class="barra-busqueda">
            <input type="text" placeholder="Buscar..." />
        </div>
        <nav class="menu-superior">
            <a href="../index.php" class="menu-link">Inicio</a>
            <a href="../pages/catalogo.php" class="menu-link">Catalogo</a>
            <a href="../pages/lista-comentarios.php" class="menu-link">Comentarios</a>
            <div class="barra-busqueda-nav">
                <input type="text" placeholder="Buscar..." />
                <button class="btn-buscar"><img src="../img/busqueda-de-lupa.png" alt="Buscar"></button>
            </div>
        </nav>
        <div class="usuario-menu">
            <img src="../img/User.png" alt="Perfil" class="icono-perfil-nav" />
            <div class="nav-buttons">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="./perfil.php" class="btn-acceder">Perfil</a>
                <?php else: ?>
                    <a href="./iniciar-sesion.php" class="btn-acceder">Acceder</a>
                <?php endif; ?>
            </div>
            <img src="../img/flecha-correcta.png" alt="Desplegar" class="flecha-usuario" />
        </div>
    </header>

    <main class="contenido">
        <section class="reseña-section">
            <h2 id="nombre-pelicua-reseña">Deja aquí tu reseña de la película</h2>
            
            <?php if ($usuario_id): ?>
            <form id="reseña-form" method="POST">
                <input type="hidden" name="tmdb_id" value="<?php echo htmlspecialchars($tmdb_id); ?>">
                <div class="form-group">
                    <label for="valoracion">Valoración (1-5):</label>
                    <select name="valoracion" id="valoracion" required>
                        <option value="">Selecciona una valoración</option>
                        <option value="1">1 - Muy mala</option>
                        <option value="2">2 - Mala</option>
                        <option value="3">3 - Regular</option>
                        <option value="4">4 - Buena</option>
                        <option value="5">5 - Excelente</option>
                    </select>
                </div>
                <div class="form-group">
                    <textarea id="reseña" name="comentario" rows="10" required placeholder="Escribe tu reseña aquí..."></textarea>
                </div>
                <button type="submit" class="boton-enviar-reseña">Enviar Reseña</button>
            </form>
            <?php else: ?>
            <p class="mensaje-login">Para dejar una reseña, por favor <a href="iniciar-sesion.php">inicia sesión</a></p>
            <?php endif; ?>
        </section>
    </main>

    <nav class="menu-inferior">
        <a href="../index.php"><button> <img src="../img/casa.png" alt="casa-index"></button></a>
        <a href="../pages/catalogo.php"><button> <img src="../img/categoria.png" alt="catalogo"></button></a>
        <a href="../pages/lista-comentarios.php"><button> <img src="../img/msj.png" alt=""></button></a>
    </nav>

    <script src="../js/reseñas.js"></script>
    <script src="../js/search.js"></script>
    <script src="../js/pelicula.js"></script>
</body>
</html>
