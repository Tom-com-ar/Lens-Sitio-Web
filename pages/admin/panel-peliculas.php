<?php
session_start();
require_once '../../php/conexion.php';

// Verificar que el usuario sea administrador
validar_admin($conexion);

$mensaje = '';
$error = '';

// Procesar eliminación
if (isset($_POST['eliminar']) && isset($_POST['id_pelicula'])) {
    $id_pelicula = intval($_POST['id_pelicula']);
    try {
        $stmt = $conexion->prepare("DELETE FROM peliculas WHERE id_pelicula = ?");
        $stmt->execute([$id_pelicula]);
        if ($stmt->rowCount() > 0) {
            $mensaje = 'Película eliminada exitosamente';
        } else {
            $error = 'No se pudo eliminar la película';
        }
    } catch (PDOException $e) {
        $error = 'Error al eliminar la película: ' . $e->getMessage();
    }
}

// Búsqueda rápida
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Obtener todas las películas de la tabla unificada
try {
    if ($busqueda !== '') {
        $stmt = $conexion->prepare("SELECT * FROM peliculas WHERE titulo LIKE ? ORDER BY fecha_agregada DESC");
        $stmt->execute(['%' . $busqueda . '%']);
    } else {
        $stmt = $conexion->query("SELECT * FROM peliculas ORDER BY fecha_agregada DESC");
    }
    $peliculas = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error al cargar las películas: ' . $e->getMessage();
    $peliculas = [];
}

// Contar por tipo
$total_manuales = 0;
$total_api = 0;
foreach ($peliculas as $p) {
    if ($p['origen'] === 'manual') $total_manuales++;
    if ($p['origen'] === 'api') $total_api++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Películas - Panel de Administrador</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body class="index-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1>📋 Gestionar Películas</h1>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-number"><?php echo $total_manuales; ?></p>
                <p class="stat-label">Películas Manuales</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-number"><?php echo $total_api; ?></p>
                <p class="stat-label">Películas de la API</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-number"><?php echo count($peliculas); ?></p>
                <p class="stat-label">Total de Películas</p>
            </div>
        </div>
        
        <div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <form method="GET" style="display:inline-block;">
                <input type="text" name="busqueda" placeholder="Buscar por título..." value="<?php echo htmlspecialchars($busqueda); ?>" style="padding:6px 10px;min-width:200px;">
                <button type="submit" class="btn-add" style="background:rgb(255, 174, 0); class="btn-submit">Buscar</button>
                <?php if ($busqueda !== ''): ?>
                    <a href="panel-peliculas.php" class="btn-delete" style="margin-left:8px;">Limpiar</a>
                <?php endif; ?>
            </form>
            <a href="agregar-pelicula.php" class="btn-add">Agregar Nueva Película</a>
            <a href="sincronizar-api.php" class="btn-add" style="background:rgb(255, 60, 0);">Sincronizar API</a>
            <a href="panel_admin.php" class="btn-add" style="background-color:grey">← Volver al Panel</a>
        </div>
        
        <div class="peliculas-table">
            <?php if (empty($peliculas)): ?>
                <div class="no-peliculas">
                    <h3>No hay películas</h3>
                    <p>Comienza agregando películas manualmente o sincronizando con la API.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Género</th>
                            <th>Año</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peliculas as $pelicula): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($pelicula['imagen_portada'])): ?>
                                        <img src="<?php echo htmlspecialchars($pelicula['imagen_portada']); ?>" 
                                             alt="<?php echo htmlspecialchars($pelicula['titulo']); ?>" 
                                             class="pelicula-imagen"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="sin-imagen" style="display: none;">Sin imagen</div>
                                    <?php else: ?>
                                        <div class="sin-imagen">Sin imagen</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($pelicula['titulo']); ?></strong>
                                    <?php if (!empty($pelicula['descripcion'])): ?>
                                        <br><small style="color: #ccc;"><?php echo htmlspecialchars(substr($pelicula['descripcion'], 0, 100)) . (strlen($pelicula['descripcion']) > 100 ? '...' : ''); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="color: <?php echo $pelicula['origen'] === 'api' ? '#9b59b6' : '#FF9D00'; ?>; font-weight: bold;">
                                        <?php echo $pelicula['origen'] === 'api' ? '🌐 API' : '✏️ Manual'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($pelicula['genero'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($pelicula['fecha_estreno'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="estado-<?php echo $pelicula['estado']; ?>">
                                        <?php echo ucfirst($pelicula['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pelicula['fecha_agregada'])); ?></td>
                                <td class="acciones">
                                    <?php if ($pelicula['origen'] === 'manual'): ?>
                                        <a href="editar-pelicula.php?id=<?php echo $pelicula['id_pelicula']; ?>" class="btn-edit">✏️ Editar</a>
                                    <?php else: ?>
                                        <span class="btn-disabled" title="Las películas de la API no pueden ser editadas manualmente">🔒 Solo lectura</span>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta película?');">
                                        <input type="hidden" name="id_pelicula" value="<?php echo $pelicula['id_pelicula']; ?>">
                                        <button type="submit" name="eliminar" class="btn-delete">🗑️ Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selecciona los mensajes de éxito y error
        const mensaje = document.querySelector('.mensaje.exito');
        const error = document.querySelector('.mensaje.error');
        // Si existen, los oculta después de 3 segundos (3000 ms)
        if (mensaje) {
            setTimeout(() => mensaje.style.display = 'none', 3000);
        }
        if (error) {
            setTimeout(() => error.style.display = 'none', 3000);
        }
    });
    </script>
</html> 