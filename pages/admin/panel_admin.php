<?php
session_start();
require_once '../../php/conexion.php';

// Verificar que el usuario sea administrador
validar_admin($conexion);

// Obtener estadísticas
$stmt = $conexion->query("SELECT COUNT(*) as total FROM peliculas");
$total_peliculas = $stmt->fetch()['total'];

$stmt = $conexion->query("SELECT COUNT(*) as total FROM peliculas WHERE origen = 'api'");
$total_api = $stmt->fetch()['total'];

$stmt = $conexion->query("SELECT COUNT(*) as total FROM peliculas WHERE origen = 'manual'");
$total_manual = $stmt->fetch()['total'];

$stmt = $conexion->query("SELECT COUNT(*) as total FROM usuarios");
$total_usuarios = $stmt->fetch()['total'];

$stmt = $conexion->query("SELECT COUNT(*) as total FROM comentarios");
$total_comentarios = $stmt->fetch()['total'];

$stmt = $conexion->query("SELECT COUNT(*) as total FROM entradas");
$total_entradas = $stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Cine Lens</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <style>
        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .stats-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background:rgb(0, 0, 0);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            gap: 20px;
        }
        .stat-item {
            text-align: center;
            flex: 1;
            background: #222;
            border-radius: 10px;
            padding: 20px 10px;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .stat-number {
            font-size: 2.2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 1em;
            color: #ffb300;
        }
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 30px;
            margin: 40px 0 30px 0;
        }
        .admin-card {
            background: #181818;
            border-radius: 12px;
            padding: 30px 20px;
            color: #fff;
            text-decoration: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.10);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .admin-card:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 6px 24px rgba(0,0,0,0.18);
            background: #222;
        }
        .admin-card h3 {
            margin-bottom: 10px;
            color: #ffb300;
        }
        .logout-btn {
            background: #e74c3c;
            color: #fff;
            padding: 10px 25px;
            border: none;
            border-radius: 6px;
            margin: 10px 10px 0 0;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body class="index-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1>🎬 Panel de Administrador</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Administrador'); ?></p>
        </div>
        <div class="stats-bar" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;width:100%;margin-bottom:24px;">
            <div class="stat-item" style="min-width:0;">
                <div class="stat-number"><?php echo $total_peliculas; ?></div>
                <div class="stat-label">Total Películas</div>
            </div>
            <div class="stat-item" style="min-width:0;">
                <div class="stat-number"><?php echo $total_api; ?></div>
                <div class="stat-label">De la API</div>
            </div>
            <div class="stat-item" style="min-width:0;">
                <div class="stat-number"><?php echo $total_manual; ?></div>
                <div class="stat-label">Manuales</div>
            </div>
            <div class="stat-item" style="min-width:0;">
                <div class="stat-number"><?php echo $total_usuarios; ?></div>
                <div class="stat-label">Usuarios</div>
            </div>
            <div class="stat-item" style="min-width:0;">
                <div class="stat-number"><?php echo $total_comentarios; ?></div>
                <div class="stat-label">Comentarios</div>
            </div>
            <div class="stat-item" style="min-width:0;">
                <div class="stat-number"><?php echo $total_entradas; ?></div>
                <div class="stat-label">Entradas</div>
            </div>
        </div>
        <div class="admin-menu" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;width:100%;margin-bottom:30px;">
            <a href="buscar-pelicula-api.php" class="admin-card">
                <h3>🔍 Buscar en API</h3>
                <p>Buscar películas en TMDB y agregarlas a la base de datos. Control total sobre el catálogo.</p>
            </a>
            <a href="agregar-pelicula.php" class="admin-card">
                <h3>➕ Agregar Película</h3>
                <p>Agregar una nueva película manualmente al catálogo con toda su información.</p>
            </a>
            <a href="panel-peliculas.php" class="admin-card">
                <h3>📋 Gestionar Películas</h3>
                <p>Ver, editar y eliminar películas del catálogo. Administrar el contenido.</p>
            </a>

            <a href="panel-salas.php" class="admin-card">
                <h3>📋 Gestionar Salas</h3>
                <p>Ver, editar y eliminar salas.</p>
            </a>

            <a href="panel-funciones.php" class="admin-card">
                <h3>📋 Gestionar Funciones</h3>
                <p>Ver, editar y eliminar salas.</p>
            </a>
        </div>
        <div style="text-align: center;">
            <a href="../../index.php" class="logout-btn">🏠 Volver al Sitio</a>
            <a href="../../php/cerrar_sesion.php" class="logout-btn">🚪 Cerrar Sesión</a>
        </div>
    </div>
</body>
</html> 