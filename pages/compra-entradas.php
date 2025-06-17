<?php
session_start();
require_once '../php/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: iniciar-sesion.php');
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$tmdb_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$tmdb_id) {
    header('Location: index.php');
    exit();
}

$mensaje = '';
$error = '';

// Procesar la compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asientos']) && is_array($_POST['asientos'])) {
    $asientos = array_filter($_POST['asientos']); // Eliminar valores vacíos
    
    try {
        $conexion->beginTransaction();
        
        foreach ($asientos as $asiento) {
            // Extraer fila y número del asiento (formato: "A1", "B2", etc.)
            $fila = substr($asiento, 0, 1);
            $numero = intval(substr($asiento, 1));
            
            // Verificar si el asiento ya está ocupado
            $stmt = $conexion->prepare('SELECT COUNT(*) FROM entradas WHERE tmdb_id = ? AND fila = ? AND numero_asiento = ?');
            $stmt->execute([$tmdb_id, $fila, $numero]);
            $asiento_ocupado = $stmt->fetchColumn();

            if ($asiento_ocupado) {
                throw new Exception("El asiento $fila$numero ya está ocupado.");
            }

            // Obtener el nombre de la película
            $nombre_pelicula = obtener_nombre_pelicula($tmdb_id);

            // Insertar la entrada
            $stmt = $conexion->prepare("INSERT INTO entradas (id_usuario, tmdb_id, nombre_pelicula, fila, numero_asiento, fecha_compra) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$id_usuario, $tmdb_id, $nombre_pelicula, $fila, $numero]);
        }
        
        $conexion->commit();
        $mensaje = "¡Compra exitosa! Tus entradas han sido registradas.";
        
    } catch(Exception $e) {
        $conexion->rollBack();
        $error = "Error al procesar la compra: " . $e->getMessage();
    }
}

// Obtener información de la película desde la API de TMDB
function obtener_nombre_pelicula($tmdb_id, $apiKey = 'e6822a7ed386f7102b6a857ea5e3c17f') {
    $url = "https://api.themoviedb.org/3/movie/$tmdb_id?api_key=$apiKey&language=es-ES";
    $json = @file_get_contents($url);
    if ($json !== false) {
        $data = json_decode($json, true);
        return $data['title'] ?? 'Título no disponible';
    }
    return 'Título no disponible';
}

$pelicula_titulo = obtener_nombre_pelicula($tmdb_id);

// Obtener asientos ocupados para esta película
$asientos_ocupados = [];
try {
    $stmt = $conexion->prepare('SELECT CONCAT(fila, numero_asiento) as asiento FROM entradas WHERE tmdb_id = ?');
    $stmt->execute([$tmdb_id]);
    $asientos_ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    error_log("Error al obtener asientos ocupados: " . $e->getMessage());
    // Continuar aunque haya error para no bloquear la página
}

// Convertir el array de asientos ocupados a JSON para pasarlo al JS
$asientos_ocupados_json = json_encode($asientos_ocupados);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Lens - Compra de entradas</title>
</head>

<body class="pelicula-page">
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
    <?php if ($mensaje): ?>
            <div class="mensaje-exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje-error"><?php echo $error; ?></div>
        <?php endif; ?>
    <main class="entradas-page">
        <section class="movie-info-section-butacas">
            <div class="movie-poster-container-butacas">
                <img src="https://image.tmdb.org/t/p/w500<?php echo htmlspecialchars($pelicula['poster_path']); ?>" alt="<?php echo htmlspecialchars($pelicula['title']); ?>" class="movie-poster-butacas">
            </div>
            <div class="movie-metadata-butacas">
                <p class="movie-genre-butacas">
                    <?php
                    if (!empty($pelicula['genres'])) {
                        $genreNames = array_column($pelicula['genres'], 'name');
                        echo htmlspecialchars(implode(', ', $genreNames));
                    }
                    ?>
                </p>
            </div>
        </section>

        <section class="compra-entradas">
            <h1 class="movie-title-butacas"><?php echo htmlspecialchars($pelicula['title']); ?></h1>
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
            <form method="POST" class="formulario-compra">
                <button type="submit" class="btn-comprar-entradas">Comprar Entradas</button>
            </form>
            <button class="btn-borrar-seleccion">Borrar Selección</button>
            <button class="btn-volver-atras">Volver Atrás</button>
        </section>
    </main>

    <script>
        const asientosOcupados = <?php echo $asientos_ocupados_json; ?>;

        document.addEventListener('DOMContentLoaded', () => {
            // Marcar asientos ocupados después de que entradas.js los cree
            setTimeout(() => {
                console.log('Timeout reached. Checking for butacas and button.');
                const butacas = document.querySelectorAll('.butaca');
                console.log('Butacas encontradas:', butacas.length);

                butacas.forEach(butaca => {
                    const asientoId = butaca.dataset.id;
                    if (asientosOcupados.includes(asientoId)) {
                        butaca.classList.remove('disponible');
                        butaca.classList.add('ocupado');

                        // Clonar el elemento para remover todos los event listeners
                        const newButaca = butaca.cloneNode(true);
                        butaca.parentNode.replaceChild(newButaca, butaca);
                         console.log('Asiento marcado como ocupado:', asientoId);
                    }
                });

                 // Agregar event listener al botón comprar DENTRO de este timeout
                const btnComprarEntradas = document.querySelector('.btn-comprar-entradas');
                const form = document.querySelector('.formulario-compra');

                console.log('Botón Comprar Entradas encontrado:', !!btnComprarEntradas);
                console.log('Formulario de compra encontrado:', !!form);

                if (btnComprarEntradas && form) {
                     console.log('Adding click listener to buy button.');
                    btnComprarEntradas.addEventListener('click', (e) => {
                        console.log('Buy button clicked.');
                        const asientosSeleccionados = document.querySelectorAll('.butaca.seleccionado');
                        console.log('Asientos seleccionados al hacer click:', asientosSeleccionados.length);

                        if (asientosSeleccionados.length === 0) {
                            e.preventDefault(); // Prevenir el envío si no hay asientos seleccionados
                            alert('No has seleccionado ninguna butaca');
                             console.log('Alerta mostrada: No hay butacas seleccionadas.');
                        }
                        // Si hay asientos seleccionados, el formulario se enviará normalmente
                    });
                } else {
                    console.error('Error: Botón Comprar Entradas o formulario no encontrados después del timeout.');
                }

            }, 1000); // Aumentado el tiempo a 1000ms (1 segundo)
        });
    </script>
    <script src="../js/entradas.js"></script>
</body>

</html>