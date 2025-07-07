<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cine Lens</title>
    <link rel="stylesheet" href="./css/style.css" />
</head>

<body  class="index-page">
    <header class="top-bar">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="./pages/perfil.php"><img src="./img/User.png" alt="Perfil" class="icono-perfil" /></a>
        <?php else: ?>
            <a href="./pages/iniciar-sesion.php"><img src="./img/User.png" alt="Perfil" class="icono-perfil" /></a>
        <?php endif; ?>
        <img src="./img/logo.png" alt="Logo Cine Lens" class="logo" />
        <div class="barra-busqueda">
            <input type="text" placeholder="Buscar..." />
        </div>
        <nav class="menu-superior">
            <a href="#" class="menu-link">Inicio</a>
            <a href="./pages/catalogo.php" class="menu-link">Catalogo</a>
            <a href="./pages/lista-comentarios.php" class="menu-link">Comentarios</a>
            <div class="barra-busqueda-nav">
                <input type="text" placeholder="Buscar..." />
                <button class="btn-buscar"><img src="./img/busqueda-de-lupa.png" alt="Buscar"></button>
            </div>
        </nav>
        <div class="usuario-menu">
            <img src="./img/User.png" alt="Perfil" class="icono-perfil-nav" />
            <div class="nav-buttons">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="pages/perfil.php" class="btn-acceder">Perfil</a>
                <?php else: ?>
                    <a href="pages/iniciar-sesion.php" class="btn-acceder">Acceder</a>
                <?php endif; ?>
            </div>
            <img src="./img/flecha-correcta.png" alt="Desplegar" class="flecha-usuario" />
        </div>
    </header>

    <main class="contenido">
        <section class="populares">
            <div class="titulo-flecha">
                <h2>Populares Ahora</h2>
                <div class="vermas-contenedor">
                    <a href="./pages/catalogo.php">
                        <span class="vermas-text">Ver Más</span>
                        <img src="./img/flecha-correcta.png" alt="filtros" class="flecha-vermas">
                    </a>
                </div>
            </div>
            <div class="grid-populares"></div>
        </section>

        <section class="recomendados-size">
            <div class="tarjeta-izq">
                <img src="" alt="">
                <div class="info-tarjeta">
                    <h3 class="titulo-tarjeta"></h3>
                    <button class="btn-vermas-tarjeta"><span>▶</span> Ver Mas</button>
                </div>
            </div>
            <div class="tarjeta-der">
                <img src="" alt="">
                <div class="info-tarjeta">
                    <h3 class="titulo-tarjeta"></h3>
                    <button class="btn-vermas-tarjeta"><span>▶</span> Ver Mas</button>
                </div>
            </div>
        </section>

        <section class="peliculas">
            <div class="titulo-flecha">
                <h2>Películas</h2>
                <div class="vermas-contenedor">
                    <a href="./pages/catalogo.php">
                        <span class="vermas-text">Ver Más</span>
                        <img src="./img/flecha-correcta.png" alt="filtros" class="flecha-vermas">
                    </a>
                </div>
            </div>
            </div>
            <div class="grid-peliculas"></div>
            <div class="grid-peliculas-size">
                <div class="pelicula-card">
                    <img src="" alt="">
                    <div class="nombre-pelicula"></div>
                </div>
            </div>
        </section>
    </main>

    <nav class="menu-inferior">
        <a href="#"><button> <img src="./img/casa.png" alt="casa-index"></button></a>
        <a href="./pages/catalogo.php"><button> <img src="./img/categoria.png"
                    alt="catalogo"></button></a>
        <a href="./pages/lista-comentarios.php"><button> <img src="./img/msj.png" alt=""></button></a>
    </nav>

    <script src="./js/script.js"></script>
    <script src="./js/search.js"></script>
</body>

</html>