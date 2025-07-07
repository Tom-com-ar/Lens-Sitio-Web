<?php
require_once '../../php/conexion.php';

$mensaje = '';
$error = '';

// AGREGAR SALA
if (isset($_POST['agregar_sala'])) {
    $nombre = trim($_POST['nombre']);
    $capacidad = 120; // Siempre 120
    if ($nombre) {
        try {
            $stmt = $conexion->prepare('INSERT INTO salas (nombre, capacidad) VALUES (?, ?)');
            $stmt->execute([$nombre, $capacidad]);
            $mensaje = 'Sala agregada correctamente.';
        } catch (PDOException $e) {
            $error = 'Error al agregar sala: ' . $e->getMessage();
        }
    } else {
        $error = 'Completa todos los campos correctamente.';
    }
}

// EDITAR SALA
if (isset($_POST['editar_sala'])) {
    $id_sala = intval($_POST['id_sala']);
    $nombre = trim($_POST['nombre']);
    $capacidad = 120; // Siempre 120, no depende del POST
    if ($id_sala && $nombre) {
        try {
            $stmt = $conexion->prepare('UPDATE salas SET nombre = ?, capacidad = ? WHERE id_sala = ?');
            $stmt->execute([$nombre, $capacidad, $id_sala]);
            $mensaje = 'Sala editada correctamente.';
        } catch (PDOException $e) {
            $error = 'Error al editar sala: ' . $e->getMessage();
        }
    } else {
        $error = 'Completa todos los campos correctamente.';
    }
}

// ELIMINAR SALA
if (isset($_POST['eliminar_sala'])) {
    $id_sala = intval($_POST['id_sala']);
    if ($id_sala) {
        try {
            $stmt = $conexion->prepare('DELETE FROM salas WHERE id_sala = ?');
            $stmt->execute([$id_sala]);
            $mensaje = 'Sala eliminada correctamente.';
        } catch (PDOException $e) {
            $error = 'Error al eliminar sala: ' . $e->getMessage();
        }
    }
}

// OBTENER TODAS LAS SALAS
$salas = [];
try {
    $stmt = $conexion->query('SELECT * FROM salas ORDER BY id_sala ASC');
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error al obtener salas: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Salas</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body class="index-page">
    <div class="admin-container" style="max-width:100%;width:100%;padding:16px;box-sizing:border-box;">
        <div class="admin-header" style="min-width:0;">
            <h1 style="word-break:break-word;">🎬 Gestionar Salas</h1>
        </div>
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="form-container" style="min-width:0;">
            <h2 style="word-break:break-word;">Salas existentes</h2>
            <div class="peliculas-table" style="overflow-x:auto;width:100%;">
                <table style="min-width:480px;width:100%;max-width:100%;">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Capacidad</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($salas as $sala): ?>
                    <tr>
                        <form method="POST" style="display:flex;flex-wrap:wrap;gap:4px;align-items:center;">
                            <td style="min-width:40px;"><?php echo $sala['id_sala']; ?><input type="hidden" name="id_sala" value="<?php echo $sala['id_sala']; ?>"></td>
                            <td><input type="text" name="nombre" value="<?php echo htmlspecialchars($sala['nombre']); ?>" required class="form-group input" style="width:100%;min-width:120px;"></td>
                            <td><input type="number" name="capacidad" value="120" min="1" required class="form-group input" style="width:100px;" readonly disabled></td>
                            <td class="acciones" style="display:flex;gap:4px;flex-wrap:wrap;">
                                <button type="submit" name="editar_sala" class="btn-edit" style="width:100%;min-width:80px;">Editar</button>
                                <button type="submit" name="eliminar_sala" class="btn-delete" style="width:100%;min-width:80px;" onclick="return confirm('¿Eliminar esta sala?');">Eliminar</button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <div class="form-container" style="min-width:0;">
            <h2 style="word-break:break-word;">Agregar nueva sala</h2>
            <form method="POST" style="display:flex;flex-direction:column;gap:12px;width:100%;max-width:400px;margin:0 auto;">
                <div class="form-group" style="width:100%;">
                    <label for="nombre">Nombre de la sala</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Nombre de la sala" required style="width:100%;">
                </div>
                <div class="form-group" style="width:100%;">
                    <label for="capacidad">Capacidad</label>
                    <input type="number" name="capacidad" id="capacidad" placeholder="Capacidad" min="1" value="120" required readonly style="width:100%;">
                </div>
                <div class="form-actions" style="width:100%;">
                    <button type="submit" name="agregar_sala" class="btn-submit" style="width:100%;">Agregar Sala</button>
                </div>
            </form>
        </div>
        <div style="text-align: center;width:100%;margin-top:16px;">
            <a href="panel_admin.php" class="btn-back" style="width:100%;max-width:300px;">&larr; Volver al panel principal</a>
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