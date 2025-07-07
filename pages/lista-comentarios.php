<?php
session_start();
require_once '../php/conexion.php';

$search_query = $_GET['search'] ?? '';
$error = '';

try {
    // Consulta base para obtener comentarios con el nombre de la película y el usuario
    $sql = '
        SELECT 
            c.id_pelicula,
            c.comentario, 
            c.valoracion, 
            c.fecha_comentario, 
            u.nombre_usuario,
            p.titulo as nombre_pelicula
        FROM comentarios c
        JOIN usuarios u ON c.id_usuario = u.id_usuario
        JOIN peliculas p ON c.id_pelicula = p.id_pelicula
    ';
    
    $params = [];
    
    // Si hay una búsqueda, agregar la condición WHERE
    if (!empty($search_query)) {
        $sql .= ' WHERE p.titulo LIKE ?';
        $params[] = "%$search_query%";
    }
    
    $sql .= ' ORDER BY c.fecha_comentario DESC LIMIT 200';
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = "Error al obtener los comentarios: " . $e->getMessage();
    $comentarios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Lens - Comentarios</title>
</head>
<body class="comentarios-page">
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
            <a href="#" class="menu-link">Comentarios</a>
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
        <section class="comentarios-section">
            <h2>Comentarios de Películas</h2>

            <!-- Formulario de búsqueda -->
            <form action="lista-comentarios.php" method="GET" class="barra-busqueda-comentarios">
                <input type="text" name="search" placeholder="Buscar por nombre de la película..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn-buscar-comentarios">Buscar</button>
            </form>

            <?php if (!empty($error)): ?>
                <div class="mensaje-error"><?php echo $error; ?></div>
            <?php elseif (empty($comentarios)): ?>
                <p class="mensaje-sin-comentarios">
                    <?php echo empty($search_query) ? 'Aún no hay comentarios.' : 'No se encontraron comentarios para tu búsqueda.'; ?>
                </p>
            <?php else: ?>
                <div id="comentarios-container" class="lista-comentarios-grid">
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="comentario-card">
                            <div class="comentario-info">
                                <h3>Película: <?php echo htmlspecialchars($comentario['nombre_pelicula']); ?></h3>
                                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($comentario['nombre_usuario']); ?></p>
                                <p><strong>Comentario:</strong> <?php echo htmlspecialchars($comentario['comentario']); ?></p>
                                <p><strong>Valoración:</strong> <?php echo htmlspecialchars($comentario['valoracion']); ?>/5</p>
                                <p><small>Fecha: <?php echo date('d/m/Y', strtotime($comentario['fecha_comentario'])); ?></small></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <nav class="menu-inferior">
        <a href="../index.php"><button> <img src="../img/casa.png" alt="casa-index"></button></a>
        <a href="../pages/catalogo.php"><button> <img src="../img/categoria.png" alt="catalogo"></button></a>
        <a href="#"><button> <img src="../img/msj.png" alt=""></button></a>
    </nav>

    <script src="../js/search.js"></script>
</body>
</html>
