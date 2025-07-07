<?php
session_start();
require_once '../php/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: iniciar-sesion.php?redirect=catalogo.php');
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$id_pelicula = isset($_GET['id']) ? intval($_GET['id']) : null;
$id_funcion = isset($_GET['id_funcion']) ? intval($_GET['id_funcion']) : null;

$mensaje = '';
$error = '';

// PROCESAR COMPRA DE ENTRADAS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asientos']) && is_array($_POST['asientos']) && isset($_POST['id_funcion'])) {
    $id_funcion_post = intval($_POST['id_funcion']);
    $asientos = array_filter($_POST['asientos']);
    if (empty($asientos)) {
        $error = "No has seleccionado ningún asiento.";
    } else {
    try {
        $conexion->beginTransaction();
        foreach ($asientos as $asiento) {
            $fila = substr($asiento, 0, 1);
            $numero = intval(substr($asiento, 1));
                // Verificar si el asiento ya está ocupado para esa función
                $stmt_check = $conexion->prepare('SELECT COUNT(*) FROM entradas WHERE id_funcion = ? AND fila = ? AND numero_asiento = ?');
                $stmt_check->execute([$id_funcion_post, $fila, $numero]);
                if ($stmt_check->fetchColumn() > 0) {
                    throw new Exception("El asiento $fila$numero ya está ocupado. Por favor, selecciona otro.");
            }
                // Insertar la entrada
                $stmt_funcion = $conexion->prepare('SELECT id_pelicula FROM funciones WHERE id_funcion = ?');
                $stmt_funcion->execute([$id_funcion_post]);
                $id_pelicula_funcion = $stmt_funcion->fetchColumn();
                $stmt_insert = $conexion->prepare("INSERT INTO entradas (id_usuario, id_funcion, id_pelicula, fila, numero_asiento, fecha_compra) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt_insert->execute([$id_usuario, $id_funcion_post, $id_pelicula_funcion, $fila, $numero]);
        }
        $conexion->commit();
            $mensaje = "¡Compra exitosa! Has comprado " . count($asientos) . " entrada(s).";
    } catch(Exception $e) {
        $conexion->rollBack();
        $error = "Error al procesar la compra: " . $e->getMessage();
    }
}
}

if ($id_funcion) {
    // --- FLUJO: SELECCIÓN DE BUTACAS PARA UNA FUNCIÓN ---
    $stmt = $conexion->prepare('SELECT f.*, p.titulo, p.genero, p.origen, p.tmdb_id, p.imagen_portada, s.nombre as sala_nombre FROM funciones f JOIN peliculas p ON f.id_pelicula = p.id_pelicula JOIN salas s ON f.id_sala = s.id_sala WHERE f.id_funcion = ?');
    $stmt->execute([$id_funcion]);
    $funcion = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$funcion) {
        header('Location: catalogo.php?error=funcion_no_encontrada');
        exit();
    }
    // Obtener asientos ocupados para esta función
$asientos_ocupados = [];
try {
        $stmt_occupied = $conexion->prepare('SELECT CONCAT(fila, numero_asiento) as asiento FROM entradas WHERE id_funcion = ?');
        $stmt_occupied->execute([$id_funcion]);
        $asientos_ocupados = $stmt_occupied->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
        $error = "No se pudieron cargar los asientos. Inténtalo de nuevo.";
}
$asientos_ocupados_json = json_encode($asientos_ocupados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
        <title>Compra de Entradas</title>
</head>
<body class="pelicula-page">
<header class="top-bar">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="./perfil.php"><img src="../img/User.png" alt="Perfil" class="icono-perfil" /></a>
        <?php else: ?>
            <a href="./iniciar-sesion.php"><img src="../img/User.png" alt="Perfil" class="icono-perfil" /></a>
        <?php endif; ?>
        <img src="../img/logo.png" alt="Logo Cine Lens" class="logo" />
        <div class="barra-busqueda">
            <input type="text" id="busqueda-pelicula" placeholder="Buscar por nombre de película...">
        </div>
        <nav class="menu-superior">
            <a href="../index.php" class="menu-link">Inicio</a>
            <a href="../pages/catalogo.php" class="menu-link">Catalogo</a>
            <a href=",,/pages/lista-comentarios.php" class="menu-link">Comentarios</a>
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
    <main class="entradas-page">
        <section class="movie-info-section-butacas">
            <div class="movie-poster-container-butacas">
                <img src="<?php echo ($funcion['origen'] === 'manual' && !empty($funcion['imagen_portada'])) ? htmlspecialchars($funcion['imagen_portada']) : '../img/placeholder.png'; ?>" alt="<?php echo htmlspecialchars($funcion['titulo']); ?>" class="movie-poster-butacas" <?php if ($funcion['origen'] === 'api') echo 'data-tmdb-id="' . $funcion['tmdb_id'] . '"'; ?>>
            </div>
            <div class="movie-metadata-butacas">
                <p class="movie-genre-butacas"><?php echo htmlspecialchars($funcion['genero']); ?></p>
                <p><strong>Sala:</strong> <?php echo htmlspecialchars($funcion['sala_nombre']); ?></p>
                <p><strong>Horario:</strong> <?php echo date('d/m/Y H:i', strtotime($funcion['fecha_hora'])); ?></p>
                <p><strong>Precio:</strong> $<?php echo number_format($funcion['precio'], 2); ?></p>
            </div>
        </section>
        <section class="compra-entradas">
            <h1 class="movie-title-butacas"><?php echo htmlspecialchars($funcion['titulo']); ?></h1>
            <!-- SVG de la pantalla -->
            <svg class="pantalla-cine" viewBox="0 0 319 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.29742 15C6.70078 15 6.13934 14.7247 5.77599 14.2539L3.7008 11.5651C2.76313 10.3502 3.46001 8.58431 4.98957 8.39741C18.8926 6.69849 74.6484 0.606465 157.397 0.869684C202.894 1.01441 254.962 3.658 313.347 8.68218C314.952 8.8203 315.732 10.705 314.697 11.9469L312.736 14.2986C312.365 14.7428 311.817 15 311.241 15L7.29742 15Z" fill="#222222"></path>
                <path d="M4.08767 12.3643C3.05435 12.4761 2.84608 13.8907 3.80678 14.2788L28.9715 24.4462C29.1387 24.5138 29.3133 24.5359 29.4923 24.5092C34.7903 23.7181 119.512 11.6812 289.929 24.1612C290.078 24.1722 290.232 24.1497 290.372 24.0949L314.674 14.5699C315.646 14.189 315.478 12.7841 314.443 12.6761C289.274 10.0482 148.249 -3.23927 4.08767 12.3643Z" fill="url(#paint0_linear_1_3)"></path>
                <defs>
                    <linearGradient id="paint0_linear_1_3" x1="159.279" y1="4.23901" x2="159.249" y2="29" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#555555"></stop>
                        <stop offset="1" stop-color="#181818"></stop>
                    </linearGradient>
                </defs>
            </svg>
            <h1 class="title-pantalla">PANTALLA</h1>
            <div class="sala-cine">
                <div class="letras-filas">
                    <span>A</span><span>B</span><span>C</span><span>D</span><span>E</span><span>F</span><span>G</span><span>H</span><span>I</span><span>J</span>
                </div>
                <div class="butacas-grid"></div>
            </div>
            <div class="leyenda-butacas">
                <span><span class="butaca disponible"></span> Disponible</span>
                <span><span class="butaca seleccionado"></span> Seleccionado</span>
                <span><span class="butaca ocupado"></span> Ocupado</span>
            </div>
            <div class="butacas-seleccionadas">
                <span class="butacas-texto">Ninguna Seleccionada</span>
            </div>
            <?php if ($mensaje): ?><div class="mensaje-exito"><?php echo $mensaje; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="mensaje-error"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST" class="formulario-compra">
                <input type="hidden" name="id_funcion" value="<?php echo $id_funcion; ?>">
                <button type="submit" class="btn-comprar-entradas">Comprar Entradas</button>
            </form>
            <button type="button" class="btn-borrar-seleccion">Borrar Selección</button>
            <button type="button" class="btn-volver-atras">Volver a funciones</button>
        </section>
    </main>
    <script>
        const asientosOcupados = <?php echo $asientos_ocupados_json; ?>;
    </script>
    <script src="../js/entradas.js"></script>
</body>
    </html>
    <?php
    exit;
}

if ($id_pelicula) {
    $stmt = $conexion->prepare('SELECT * FROM peliculas WHERE id_pelicula = ? AND estado = "activa"');
    $stmt->execute([$id_pelicula]);
    $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pelicula) {
        header('Location: catalogo.php?error=pelicula_no_encontrada');
        exit();
    }
    $stmt = $conexion->prepare('SELECT f.*, s.nombre as sala_nombre FROM funciones f JOIN salas s ON f.id_sala = s.id_sala WHERE f.id_pelicula = ? AND f.fecha_hora >= NOW() ORDER BY f.fecha_hora ASC');
    $stmt->execute([$id_pelicula]);
    $funciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../css/style.css">
        <title>Funciones disponibles</title>
    </head>
    <body class="pelicula-page">
    <header class="top-bar">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="./perfil.php"><img src="../img/User.png" alt="Perfil" class="icono-perfil" /></a>
        <?php else: ?>
            <a href="./iniciar-sesion.php"><img src="../img/User.png" alt="Perfil" class="icono-perfil" /></a>
        <?php endif; ?>
        <img src="../img/logo.png" alt="Logo Cine Lens" class="logo" />
        <div class="barra-busqueda">
            <input type="text" id="busqueda-pelicula" placeholder="Buscar por nombre de película...">
        </div>
        <nav class="menu-superior">
            <a href="../index.php" class="menu-link">Inicio</a>
            <a href="../pages/catalogo.php" class="menu-link">Catalogo</a>
            <a href=",,/pages/lista-comentarios.php" class="menu-link">Comentarios</a>
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
    <main class="entradas-page">
        <section class="movie-info-section-butacas">
            <div class="movie-poster-container-butacas">
                <img src="<?php echo ($pelicula['origen'] === 'manual' && !empty($pelicula['imagen_portada'])) ? htmlspecialchars($pelicula['imagen_portada']) : '../img/placeholder.png'; ?>" alt="<?php echo htmlspecialchars($pelicula['titulo']); ?>" class="movie-poster-butacas" <?php if ($pelicula['origen'] === 'api') echo 'data-tmdb-id="' . $pelicula['tmdb_id'] . '"'; ?>>
            </div>
            <div class="movie-metadata-butacas">
                <p class="movie-genre-butacas"><?php echo htmlspecialchars($pelicula['genero']); ?></p>
            </div>
        </section>
        <section class="compra-entradas">
            <h1 class="movie-title-butacas"><?php echo htmlspecialchars($pelicula['titulo']); ?></h1>
            <div style="display: flex; justify-content: center; padding: 0 20px;">
                <div style="background: #333; color: #fff; border-radius: 12px; overflow: hidden; width: 100%; max-width: 800px; box-shadow: 0 2px 8px #0004;">
                    <div style="background: #222; padding: 16px; text-align: center; font-weight: bold; font-size: 18px;">
                        Funciones Disponibles
                    </div>
                    <div style="padding: 0;">
                        <?php foreach ($funciones as $f): ?>
                        <div style="border-bottom: 1px solid #444; padding: 16px; display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                                <div style="flex: 1; min-width: 120px;">
                                    <strong style="color: #ffb300;">Sala:</strong> <?php echo htmlspecialchars($f['sala_nombre']); ?>
                                </div>
                                <div style="flex: 1; min-width: 150px;">
                                    <strong style="color: #ffb300;">Fecha y Hora:</strong> <?php echo date('d/m/Y H:i', strtotime($f['fecha_hora'])); ?>
                                </div>
                                <div style="flex: 1; min-width: 100px;">
                                    <strong style="color: #ffb300;">Precio:</strong> $<?php echo number_format($f['precio'],2); ?>
                                </div>
                                <div style="flex-shrink: 0;">
                                    <a href="compra-entradas.php?id_funcion=<?php echo $f['id_funcion']; ?>" class="btn-comprar-entradas" style="padding: 8px 20px; border-radius: 8px; background: #ffb300; color: #222; font-weight: bold; text-decoration: none; display: inline-block; white-space: nowrap;">Elegir</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <a href="../pages/catalogo.php" class="btn-volver-atras" style="text-align: center; margin-top:10px;">Volver al catálogo</a>
        </section>
    </main>
    <script src="../js/entradas.js"></script>
    </body>
</html>
    <?php
    exit;
}
?>