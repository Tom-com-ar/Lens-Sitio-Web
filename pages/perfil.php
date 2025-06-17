<?php
session_start();
require_once '../php/conexion.php';
validar_usuario_sesion($conexion);

error_log("Session started. Session ID: " . session_id());

$usuario_id_isset = isset($_SESSION['usuario_id']);
error_log("Check: isset(\$_SESSION['usuario_id']) is " . ($usuario_id_isset ? 'TRUE' : 'FALSE'));

// Verificar si el usuario está logueado
if (!$usuario_id_isset) { // Usamos la variable booleana para claridad en el log
    error_log("Usuario_id IS NOT set ($usuario_id_isset). Entering redirection block.");
    header("Location: iniciar-sesion.php"); // Path seems correct assuming iniciar-sesion.php is in the same directory
    error_log("Header Location sent.");
    exit();
    error_log("Exit executed. Script should terminate.");
} else {
    error_log("Usuario_id IS set ($usuario_id_isset): " . $_SESSION['usuario_id'] . ". Skipping redirection block.");
}

$id_usuario = $_SESSION['usuario_id'];
$mensaje = '';
$error = '';

// Procesar la actualización de datos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificar_datos'])) {
    $nuevo_nombre_usuario = trim($_POST['nombre_usuario']);
    $nueva_password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];

    // Validaciones básicas
    if (empty($nuevo_nombre_usuario)) {
        $error = "El nombre de usuario no puede estar vacío.";
    } else if (!empty($nueva_password) && $nueva_password !== $confirmar_password) {
        $error = "La nueva contraseña y la confirmación no coinciden.";
    }

    if (empty($error)) {
        try {
            // Actualizar nombre de usuario
            $stmt = $conexion->prepare('UPDATE usuarios SET nombre_usuario = ? WHERE id_usuario = ?');
            $stmt->execute([$nuevo_nombre_usuario, $id_usuario]);

            // Actualizar contraseña si se proporcionó una nueva
            if (!empty($nueva_password)) {
                $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare('UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?');
                $stmt->execute([$password_hash, $id_usuario]);
            }

            $mensaje = "¡Datos actualizados correctamente!";
        } catch(PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = "El nombre de usuario ya está en uso.";
            } else {
                $error = "Error al actualizar los datos: " . $e->getMessage();
            }
        }
    }
}

// Obtener información del usuario
try {
    $stmt = $conexion->prepare('SELECT nombre_usuario, email, fecha_registro FROM usuarios WHERE id_usuario = ?');
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();
} catch(PDOException $e) {
    error_log("Error al obtener información del usuario: " . $e->getMessage());
    $usuario = null;
}

// Obtener historial de compras
try {
    // Primero obtenemos los datos de las películas desde la API de TMDB
    $api_key = 'e6822a7ed386f7102b6a857ea5e3c17f';
    
    // Verificar si hay entradas en la base de datos
    $stmt_check = $conexion->prepare('SELECT COUNT(*) FROM entradas WHERE id_usuario = ?');
    $stmt_check->execute([$id_usuario]);
    $total_entradas = $stmt_check->fetchColumn();
    error_log("Total de entradas encontradas en la base de datos: " . $total_entradas);
    
    // Obtener todas las entradas del usuario
    $stmt = $conexion->prepare('
        SELECT e.*, CONCAT(e.fila, e.numero_asiento) as asiento
        FROM entradas e 
        WHERE e.id_usuario = ? 
        ORDER BY e.fecha_compra DESC
    ');
    $stmt->execute([$id_usuario]);
    $compras = $stmt->fetchAll();
    
    error_log("Compras obtenidas de la consulta: " . print_r($compras, true));
    
    // Agrupar compras por película
    $compras_agrupadas = [];
    foreach ($compras as $compra) {
        $tmdb_id = $compra['tmdb_id'];
        error_log("Procesando compra para película ID: " . $tmdb_id);
        
        // Si no tenemos los datos de la película, los obtenemos de la API
        if (!isset($compras_agrupadas[$tmdb_id])) {
            $url = "https://api.themoviedb.org/3/movie/{$tmdb_id}?api_key={$api_key}&language=es-ES";
            $response = file_get_contents($url);
            $pelicula = json_decode($response, true);
            
            if ($pelicula) {
                $compras_agrupadas[$tmdb_id] = [
                    'title' => $pelicula['title'],
                    'poster_path' => $pelicula['poster_path'],
                    'asientos' => [],
                    'cantidad' => 0
                ];
                error_log("Datos de película obtenidos: " . print_r($pelicula, true));
            } else {
                error_log("Error al obtener datos de la película ID: " . $tmdb_id);
            }
        }
        
        // Agregar el asiento al grupo
        if (isset($compras_agrupadas[$tmdb_id])) {
            $compras_agrupadas[$tmdb_id]['asientos'][] = [
                'numero' => $compra['asiento'],
                'fecha' => $compra['fecha_compra']
            ];
            $compras_agrupadas[$tmdb_id]['cantidad']++;
            error_log("Asiento agregado: " . $compra['asiento'] . " para película ID: " . $tmdb_id);
        }
    }
    
    error_log("Compras agrupadas final: " . print_r($compras_agrupadas, true));
    
} catch(PDOException $e) {
    error_log("Error al obtener historial de compras: " . $e->getMessage());
    $compras = [];
    $compras_agrupadas = [];
}

// Obtener historial de comentarios
try {
    // Obtener todos los comentarios del usuario
    $stmt_comentarios = $conexion->prepare('SELECT * FROM comentarios WHERE id_usuario = ? ORDER BY fecha_comentario DESC');
    $stmt_comentarios->execute([$id_usuario]);
    $comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Comentarios obtenidos de la consulta (antes de procesar): " . print_r($comentarios, true));

    $comentarios_con_pelicula = [];
    $api_key = 'e6822a7ed386f7102b6a857ea5e3c17f'; // Tu clave API de TMDB

    foreach ($comentarios as $comentario) {
        $tmdb_id = $comentario['tmdb_id'];
        error_log("Procesando comentario para película ID: " . $tmdb_id);
        $url = "https://api.themoviedb.org/3/movie/{$tmdb_id}?api_key={$api_key}&language=es-ES";
        $response = @file_get_contents($url); // Usamos @ para suprimir advertencias si la URL falla
        $pelicula = $response ? json_decode($response, true) : null;

        if ($pelicula) {
            $comentario['pelicula_titulo'] = $pelicula['title'];
            $comentario['pelicula_poster'] = $pelicula['poster_path'];
             error_log("Datos de película obtenidos para comentario: " . $pelicula['title']);
        } else {
            $comentario['pelicula_titulo'] = 'Película Desconocida';
            $comentario['pelicula_poster'] = null; // O una imagen por defecto
            error_log("Error o datos no obtenidos para la película ID del comentario: " . $tmdb_id);
        }
        $comentarios_con_pelicula[] = $comentario;
         error_log("Comentario procesado y agregado: " . print_r(end($comentarios_con_pelicula), true));
    }
    
     error_log("Comentarios procesados final: " . print_r($comentarios_con_pelicula, true));

} catch(PDOException $e) {
    error_log("Error al obtener historial de comentarios: " . $e->getMessage());
    $comentarios_con_pelicula = [];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lens - Perfil de Usuario</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="perfil-page">
    <header class="top-bar">
        <img src="../img/User.png" alt="Perfil" class="icono-perfil" />
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

    <main class="perfil-contenido">
        <section class="info-usuario">
            <h1>Perfil de Usuario</h1>
            <?php if ($usuario): ?>
                <div class="datos-usuario">
                    <?php if ($mensaje): ?>
                        <div class="mensaje-exito"><?php echo $mensaje; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="mensaje-error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div id="datos-mostrar">
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre_usuario']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                        <p><strong>Fecha de registro:</strong> <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></p>
                    </div>

                    <div id="formulario-edicion" style="display: none;">
                        <form action="perfil.php" method="POST" class="form-edicion">
                            <input type="hidden" name="modificar_datos" value="true">
                            <div class="input-group">
                                <label for="nombre_usuario">Nombre de Usuario:</label>
                                <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>
                            </div>
                            <div class="input-group">
                                <label for="email">Email:</label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                                <small style="color: #555;">El email no se puede modificar.</small>
                            </div>
                            <div class="input-group">
                                <label for="password">Nueva Contraseña (dejar vacío para no cambiar):</label>
                                <input type="password" id="password" name="password">
                            </div>
                            <div class="input-group">
                                <label for="confirmar_password">Confirmar Nueva Contraseña:</label>
                                <input type="password" id="confirmar_password" name="confirmar_password">
                            </div>
                            <div class="botones-edicion">
                                <button type="submit" class="btn btn-guardar">Guardar Cambios</button>
                                <button type="button" class="btn btn-cancelar" onclick="toggleEdicion()">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="acciones-usuario">
                    <button onclick="toggleEdicion()" class="btn btn-editar" id="btn-editar">Modificar datos</button>
                    
                    <!-- Formulario para eliminar cuenta via POST -->
                    <form action="../php/borrar_cuenta.php" method="POST" onsubmit="return confirm('¿Estás seguro de que querés eliminar tu cuenta? Esta acción no se puede deshacer.');" style="display: inline;">
                        <input type="hidden" name="confirmar_borrar" value="true">
                        <button type="submit" class="btn btn-eliminar">Eliminar cuenta</button>
                    </form>

                    <a href="../php/cerrar_sesion.php" class="btn btn-cerrar">Cerrar sesión</a>
                </div>
            <?php endif; ?>
        </section>

        <section class="historial-compras">
            <h2>Historial de Compras</h2>
            <?php if (empty($compras_agrupadas)): ?>
                <p class="no-compras">No has realizado ninguna compra aún.</p>
                <?php if ($total_entradas > 0): ?>
                    <p class="error-mensaje">Hay <?php echo $total_entradas; ?> entradas en la base de datos pero no se pudieron mostrar.</p>
                <?php endif; ?>
            <?php else: ?>
                <div class="compras-grid">
                    <?php foreach ($compras_agrupadas as $tmdb_id => $compra): ?>
                        <div class="compra-card">
                            <div class="compra-info">
                                <h3><?php echo htmlspecialchars($compra['title']); ?></h3>
                                <p><strong>Cantidad de entradas:</strong> <?php echo $compra['cantidad']; ?></p>
                                <div class="asientos-info">
                                    <strong>Asientos:</strong>
                                    <ul class="lista-asientos">
                                        <?php foreach ($compra['asientos'] as $asiento): ?>
                                            <li>
                                                Asientos: <?php echo htmlspecialchars($asiento['numero']); ?> - 
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="historial-comentarios">
            <h2>Mis Comentarios</h2>
            <?php if (empty($comentarios_con_pelicula)): ?>
                <p class="no-comentarios">No has realizado ningún comentario aún.</p>
            <?php else: ?>
                <div class="comentarios-grid">
                    <?php foreach ($comentarios_con_pelicula as $comentario): ?>
                        <div class="comentario-card">
                            <div class="comentario-info">
                                <h3><?php echo htmlspecialchars($comentario['nombre_pelicula']); ?></h3>
                                <p><strong>Comentario:</strong> <?php echo htmlspecialchars($comentario['comentario']); ?></p>
                                <p><strong>Puntuación:</strong> <?php echo htmlspecialchars($comentario['valoracion']); ?>/5</p>
                                <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($comentario['fecha_comentario'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <nav class="menu-inferior">
        <a href="../index.php"><button><img src="../img/casa.png" alt="Inicio"></button></a>
        <a href="../pages/catalogo.php"><button><img src="../img/categoria.png" alt="Catálogo"></button></a>
        <a href="../pages/lista-comentarios.php"><button><img src="../img/msj.png" alt="Comentarios"></button></a>
    </nav>

    <script src="../js/search.js"></script>
    <script>
    function toggleEdicion() {
        const datosMostrar = document.getElementById('datos-mostrar');
        const formularioEdicion = document.getElementById('formulario-edicion');
        const btnEditar = document.getElementById('btn-editar');
        
        if (datosMostrar.style.display === 'none') {
            datosMostrar.style.display = 'block';
            formularioEdicion.style.display = 'none';
            btnEditar.textContent = 'Modificar datos';
        } else {
            datosMostrar.style.display = 'none';
            formularioEdicion.style.display = 'block';
            btnEditar.textContent = 'Cancelar edición';
        }
    }
    </script>
</body>
</html>
