<?php
session_start();
require_once '../php/conexion.php';

// --- Identificadores ---
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
$usuario_id = $_SESSION['usuario_id'] ?? null;
$id_pelicula = isset($_REQUEST['id_pelicula']) ? intval($_REQUEST['id_pelicula']) : null;

if ($isAjax) {
    header('Content-Type: application/json');
}

// --- Lógica de la página ---

// GET: Solicitar comentarios para una película
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $isAjax && $id_pelicula) {
    try {
        $stmt = $conexion->prepare("
            SELECT c.*, u.nombre_usuario 
            FROM comentarios c 
            JOIN usuarios u ON c.id_usuario = u.id_usuario 
            WHERE c.id_pelicula = :id_pelicula
            ORDER BY c.fecha_comentario DESC
        ");
        $stmt->bindParam(':id_pelicula', $id_pelicula, PDO::PARAM_INT);
        $stmt->execute();
        $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['comentarios' => $comentarios]);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al cargar los comentarios.']);
        exit;
    }
}

// POST: Enviar un nuevo comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id_pelicula) {
    if (!$usuario_id) {
        http_response_code(403);
        $error_msg = 'Debes iniciar sesión para comentar';
        echo $isAjax ? json_encode(['error' => $error_msg]) : $error_msg;
        exit;
    }

    $comentario = trim($_POST['comentario'] ?? '');
    $valoracion = isset($_POST['valoracion']) ? intval($_POST['valoracion']) : 0;

    if (empty($comentario) || $valoracion < 1 || $valoracion > 5) {
        http_response_code(400);
        $error_msg = 'Por favor, completa todos los campos del formulario.';
        echo $isAjax ? json_encode(['error' => $error_msg]) : $error_msg;
        exit;
    }

    try {
        $sql = "INSERT INTO comentarios (id_usuario, id_pelicula, comentario, valoracion, fecha_comentario) 
                VALUES (:id_usuario, :id_pelicula, :comentario, :valoracion, NOW())";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_usuario', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_pelicula', $id_pelicula, PDO::PARAM_INT);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->bindParam(':valoracion', $valoracion, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            if ($isAjax) {
                echo json_encode(['success' => true, 'mensaje' => 'Comentario guardado correctamente']);
            } else {
                header("Location: " . $_SERVER['PHP_SELF'] . "?id_pelicula=" . $id_pelicula);
            }
            exit;
        } else {
            http_response_code(500);
            $error_msg = 'Error al guardar el comentario.';
            echo $isAjax ? json_encode(['error' => $error_msg]) : $error_msg;
            exit;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        $error_msg = 'Error de base de datos: ' . $e->getMessage();
        echo $isAjax ? json_encode(['error' => $error_msg]) : $error_msg;
        exit;
    }
}

// --- Carga de datos para la vista (si no es AJAX) ---
$pelicula = null;
$comentarios = [];
$error_vista = '';

if ($id_pelicula && !$isAjax) {
    try {
        // Obtener datos de la película
        $stmt_pelicula = $conexion->prepare("SELECT titulo FROM peliculas WHERE id_pelicula = ?");
        $stmt_pelicula->execute([$id_pelicula]);
        $pelicula = $stmt_pelicula->fetch(PDO::FETCH_ASSOC);

        if (!$pelicula) {
            $error_vista = "La película que intentas reseñar no existe.";
        } else {
            // Obtener comentarios existentes
            $stmt_comentarios = $conexion->prepare("
                SELECT c.*, u.nombre_usuario 
                FROM comentarios c 
                JOIN usuarios u ON c.id_usuario = u.id_usuario 
                WHERE c.id_pelicula = ? 
                ORDER BY c.fecha_comentario DESC
            ");
            $stmt_comentarios->execute([$id_pelicula]);
            $comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_vista = "Error al cargar la página de reseñas.";
    }
} elseif (!$id_pelicula && !$isAjax) {
    $error_vista = "No se ha especificado una película para reseñar.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dejar Reseña - Cine Lens</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body class="reseñas-page">
<header class="top-bar">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="../index.php"><img src="../img/User.png" alt="Perfil" class="icono-perfil" /></a>
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
            <?php if ($pelicula): ?>
                <h2>Deja tu reseña para: <strong><?php echo htmlspecialchars($pelicula['titulo']); ?></strong></h2>
            <?php elseif ($error_vista): ?>
                <h2>Error</h2>
                <p class="mensaje error"><?php echo $error_vista; ?></p>
            <?php else: ?>
                <h2>Dejar Reseña</h2>
            <?php endif; ?>
            
            <?php if ($pelicula && $usuario_id): ?>
            <form id="reseña-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="id_pelicula" value="<?php echo htmlspecialchars($id_pelicula); ?>">
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
                    <label for="comentario">Tu reseña:</label>
                    <textarea id="comentario" name="comentario" rows="8" required placeholder="Escribe tu reseña aquí..."></textarea>
                </div>
                <button type="submit" class="btn-submit">Enviar Reseña</button>
            </form>
            <?php elseif($pelicula): ?>
                <p class="mensaje-login">Para dejar una reseña, por favor <a href="iniciar-sesion.php?redirect=reseñas.php?id_pelicula=<?php echo $id_pelicula; ?>">inicia sesión</a>.</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('reseña-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const id_pelicula = formData.get('id_pelicula');

                fetch(`reseñas.php?id_pelicula=${id_pelicula}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.mensaje);
                        // Opcional: recargar la lista de comentarios dinámicamente
                        window.location.reload(); 
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    alert('Hubo un problema al enviar tu reseña.');
                });
            });
        }
    });
    </script>
</body>
</html>
