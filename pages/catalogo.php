<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Lens - Catálogo</title>
</head>

<body class="catalogo-page">
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
            <a href="#" class="menu-link">Catalogo</a>
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
        <section class="peliculas">
            <div class="titulo-flecha">
                <h2>Películas</h2>
                <button id="abrir-filtros" class="btn-filtrar-open">Filtros</button>
            </div>
            <div class="grid-peliculas">
            </div>

        </section>
    </main>

    <nav class="menu-inferior">
        <a href="../index.php"><button> <img src="../img/casa.png" alt="casa-index"></button></a>
        <a href="#"><button> <img src="../img/categoria.png" alt="catalogo"></button></a>
        <a href="./lista-comentarios.php"><button> <img src="../img/msj.png" alt=""></button></a>
    </nav>

    <div id="modal-filtros" class="modal-filtros">
        <div class="modal-contenido">
            <span class="cerrar-modal" id="cerrar-modal">&times;</span>
            <h2>Filtrar Películas</h2>
            <form id="form-filtros">
                <label>Género:
                    <select id="filtro-genero">
                        <option value="">Todos</option>
                    </select>
                </label>
                <label>Año desde:
                    <input type="number" id="filtro-ano-desde" min="1900" max="2099" placeholder="Ej: 2000">
                </label>
                <label>Año hasta:
                    <input type="number" id="filtro-ano-hasta" min="1900" max="2099" placeholder="Ej: 2025">
                </label>
                <label>Duración:
                    <select id="filtro-duracion">
                        <option value="">Todas</option>
                        <option value="1">Hasta 120 min</option>
                        <option value="2">121 a 150 min</option>
                        <option value="3">Más de 150 min</option>
                    </select>
                </label>
                <button type="submit" class="btn-filtrar">Aplicar Filtros</button>
            </form>
        </div>
    </div>

    <script src="../js/catalogo.js"></script>
    <script src="../js/search.js"></script>
</body>

</html>