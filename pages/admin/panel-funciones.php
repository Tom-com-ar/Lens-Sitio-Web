<?php
require_once '../../php/conexion.php';
session_start();
date_default_timezone_set('America/Argentina/Buenos_Aires'); // Ajusta según tu zona

$mensaje = '';
$error = '';
if (isset($_SESSION['mensaje_funcion'])) {
    $mensaje = $_SESSION['mensaje_funcion'];
    unset($_SESSION['mensaje_funcion']);
}
if (isset($_SESSION['error_funcion'])) {
    $error = $_SESSION['error_funcion'];
    unset($_SESSION['error_funcion']);
}

// Obtener películas activas
$peliculas = [];
try {
    $stmt = $conexion->query('SELECT id_pelicula, titulo FROM peliculas WHERE estado = "activa" ORDER BY titulo ASC');
    $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error al obtener películas: ' . $e->getMessage();
}

// Obtener salas
$salas = [];
try {
    $stmt = $conexion->query('SELECT id_sala, nombre FROM salas ORDER BY nombre ASC');
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error al obtener salas: ' . $e->getMessage();
}

// AGREGAR FUNCIÓN
if (isset($_POST['agregar_funcion'])) {
    $id_pelicula = intval($_POST['id_pelicula']);
    $id_sala = intval($_POST['id_sala']);
    $fecha_hora = $_POST['fecha_hora'];
    $precio = floatval($_POST['precio']);
    if ($id_pelicula && $id_sala && $fecha_hora) {
        try {
            // Obtener duración de la película
            $stmt = $conexion->prepare('SELECT duracion FROM peliculas WHERE id_pelicula = ?');
            $stmt->execute([$id_pelicula]);
            $duracion = $stmt->fetchColumn();
            if (!$duracion || $duracion <= 0) {
                $_SESSION['error_funcion'] = 'La película seleccionada no tiene duración válida.';
                header('Location: panel-funciones.php');
                exit;
            }
            $inicio = new DateTime($fecha_hora);
            $fin = clone $inicio;
            $fin->modify("+{$duracion} minutes");
            $fin->modify("+15 minutes"); // 15 minutos extra para limpieza
            // Buscar solapamientos en la misma sala
            $stmt = $conexion->prepare('SELECT f.*, p.titulo FROM funciones f JOIN peliculas p ON f.id_pelicula = p.id_pelicula WHERE f.id_sala = ? AND ((f.fecha_hora <= ? AND DATE_ADD(f.fecha_hora, INTERVAL (p.duracion + 15) MINUTE) > ?) OR (f.fecha_hora < ? AND DATE_ADD(f.fecha_hora, INTERVAL (p.duracion + 15) MINUTE) >= ?))');
            $stmt->execute([$id_sala, $fecha_hora, $fecha_hora, $fin->format('Y-m-d H:i:s'), $fin->format('Y-m-d H:i:s')]);
            $solapadas = $stmt->fetchAll();
            if ($solapadas && count($solapadas) > 0) {
                $_SESSION['error_funcion'] = 'Ya existe una función en la misma sala y horario que se solapa. Corrige el horario.';
                header('Location: panel-funciones.php');
                exit;
            }
            $stmt = $conexion->prepare('INSERT INTO funciones (id_pelicula, id_sala, fecha_hora, precio) VALUES (?, ?, ?, ?)');
            $stmt->execute([$id_pelicula, $id_sala, $fecha_hora, $precio]);
            $_SESSION['mensaje_funcion'] = 'Función agregada correctamente.';
            header('Location: panel-funciones.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_funcion'] = 'Error al agregar función: ' . $e->getMessage();
            header('Location: panel-funciones.php');
            exit;
        }
    } else {
        $_SESSION['error_funcion'] = 'Completa todos los campos correctamente.';
        header('Location: panel-funciones.php');
        exit;
    }
}

// EDITAR FUNCIÓN
if (isset($_POST['editar_funcion'])) {
    $id_funcion = intval($_POST['id_funcion']);
    $id_pelicula = intval($_POST['id_pelicula']);
    $id_sala = intval($_POST['id_sala']);
    $fecha_hora = $_POST['fecha_hora'];
    $precio = floatval($_POST['precio']);
    if ($id_funcion && $id_pelicula && $id_sala && $fecha_hora) {
        try {
            // Obtener duración de la película
            $stmt = $conexion->prepare('SELECT duracion FROM peliculas WHERE id_pelicula = ?');
            $stmt->execute([$id_pelicula]);
            $duracion = $stmt->fetchColumn();
            if (!$duracion || $duracion <= 0) {
                $_SESSION['error_funcion'] = 'La película seleccionada no tiene duración válida.';
                header('Location: panel-funciones.php');
                exit;
            }
            $inicio = new DateTime($fecha_hora);
            $fin = clone $inicio;
            $fin->modify("+{$duracion} minutes");
            $fin->modify("+15 minutes"); // 15 minutos extra para limpieza
            // Buscar solapamientos en la misma sala, excluyendo la función actual
            $stmt = $conexion->prepare('SELECT f.*, p.titulo FROM funciones f JOIN peliculas p ON f.id_pelicula = p.id_pelicula WHERE f.id_sala = ? AND f.id_funcion != ? AND ((f.fecha_hora <= ? AND DATE_ADD(f.fecha_hora, INTERVAL (p.duracion + 15) MINUTE) > ?) OR (f.fecha_hora < ? AND DATE_ADD(f.fecha_hora, INTERVAL (p.duracion + 15) MINUTE) >= ?))');
            $stmt->execute([$id_sala, $id_funcion, $fecha_hora, $fecha_hora, $fin->format('Y-m-d H:i:s'), $fin->format('Y-m-d H:i:s')]);
            $solapadas = $stmt->fetchAll();
            if ($solapadas && count($solapadas) > 0) {
                $_SESSION['error_funcion'] = 'Ya existe una función en la misma sala y horario que se solapa. Corrige el horario.';
                header('Location: panel-funciones.php');
                exit;
            }
            $stmt = $conexion->prepare('UPDATE funciones SET id_pelicula = ?, id_sala = ?, fecha_hora = ?, precio = ? WHERE id_funcion = ?');
            $stmt->execute([$id_pelicula, $id_sala, $fecha_hora, $precio, $id_funcion]);
            $_SESSION['mensaje_funcion'] = 'Función editada correctamente.';
            header('Location: panel-funciones.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_funcion'] = 'Error al editar función: ' . $e->getMessage();
            header('Location: panel-funciones.php');
            exit;
        }
    } else {
        $_SESSION['error_funcion'] = 'Completa todos los campos correctamente.';
        header('Location: panel-funciones.php');
        exit;
    }
}

// ELIMINAR FUNCIÓN
if (isset($_POST['eliminar_funcion'])) {
    $id_funcion = intval($_POST['id_funcion']);
    if ($id_funcion) {
        try {
            $stmt = $conexion->prepare('DELETE FROM funciones WHERE id_funcion = ?');
            $stmt->execute([$id_funcion]);
            $_SESSION['mensaje_funcion'] = 'Función eliminada correctamente.';
            header('Location: panel-funciones.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_funcion'] = 'Error al eliminar función: ' . $e->getMessage();
            header('Location: panel-funciones.php');
            exit;
        }
    }
}

// OBTENER TODAS LAS FUNCIONES
$funciones = [];
try {
    $stmt = $conexion->query('SELECT f.*, p.titulo, s.nombre as sala_nombre FROM funciones f JOIN peliculas p ON f.id_pelicula = p.id_pelicula JOIN salas s ON f.id_sala = s.id_sala ORDER BY f.fecha_hora DESC');
    $funciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error al obtener funciones: ' . $e->getMessage();
}

function funcion_finalizada($fecha_hora) {
    return time() >= strtotime($fecha_hora);
}

//echo "<div style='color:yellow; background:#222; padding:8px; margin-bottom:10px;'>
//Hora del servidor: <b>" . date('Y-m-d H:i:s') . "</b>
//</div>";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Funciones</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <style>
        .solo-eliminar-info {
            color: #ff4444;
            font-size: 12px;
            display: block;
            margin-top: 4px;
        }
    </style>
</head>
<body class="index-page">
    <div class="admin-container" style="max-width:100%;width:100%;padding:16px;box-sizing:border-box;">
        <div class="admin-header" style="min-width:0;">
            <h1 style="word-break:break-word;">🎬 Gestionar Funciones</h1>
        </div>
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="form-container" style="min-width:0;">
            <h2 style="word-break:break-word;">Funciones programadas</h2>
            <div class="peliculas-table" style="overflow-x:auto;width:100%;">
                <table style="min-width:700px;width:100%;max-width:100%;">
                    <tr>
                        <th>ID</th>
                        <th>Película</th>
                        <th>Sala</th>
                        <th>Fecha y Hora</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($funciones as $funcion): ?>
                    <?php $finalizada = funcion_finalizada($funcion['fecha_hora']); ?>
                    <tr>
                        <form method="POST" style="display:flex;flex-wrap:wrap;gap:4px;align-items:center;" onsubmit="return <?php echo $finalizada ? 'true' : 'true'; ?>;">
                            <td style="min-width:40px;"><?php echo $funcion['id_funcion']; ?><input type="hidden" name="id_funcion" value="<?php echo $funcion['id_funcion']; ?>"></td>
                            <td>
                                <select name="id_pelicula" required class="form-group input" style="width:100%;min-width:120px;" <?php if($finalizada) echo 'disabled'; ?>>
                                    <?php foreach ($peliculas as $pelicula): ?>
                                        <option value="<?php echo $pelicula['id_pelicula']; ?>" <?php if ($funcion['id_pelicula'] == $pelicula['id_pelicula']) echo 'selected'; ?>><?php echo htmlspecialchars($pelicula['titulo']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if($finalizada): ?><input type="hidden" name="id_pelicula" value="<?php echo $funcion['id_pelicula']; ?>"><?php endif; ?>
                            </td>
                            <td>
                                <select name="id_sala" required class="form-group input" style="width:100%;min-width:120px;" <?php if($finalizada) echo 'disabled'; ?>>
                                    <?php foreach ($salas as $sala): ?>
                                        <option value="<?php echo $sala['id_sala']; ?>" <?php if ($funcion['id_sala'] == $sala['id_sala']) echo 'selected'; ?>><?php echo htmlspecialchars($sala['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if($finalizada): ?><input type="hidden" name="id_sala" value="<?php echo $funcion['id_sala']; ?>"><?php endif; ?>
                            </td>
                            <td>
                                <input type="datetime-local" name="fecha_hora" value="<?php echo date('Y-m-d\TH:i', strtotime($funcion['fecha_hora'])); ?>" required class="form-group input" style="width:100%;min-width:120px;" <?php if($finalizada) echo 'disabled'; ?>>
                                <?php if($finalizada): ?><input type="hidden" name="fecha_hora" value="<?php echo date('Y-m-d\TH:i', strtotime($funcion['fecha_hora'])); ?>"><?php endif; ?>
                            </td>
                            <td>
                                <input type="number" name="precio" value="<?php echo $funcion['precio']; ?>" min="0" step="0.01" class="form-group input" style="width:100px;" <?php if($finalizada) echo 'disabled'; ?>>
                                <?php if($finalizada): ?><input type="hidden" name="precio" value="<?php echo $funcion['precio']; ?>"><?php endif; ?>
                            </td>
                            <td style="text-align:center;"><span style="font-weight:bold;<?php if($finalizada) echo 'color:red;'; ?>"><?php echo $finalizada ? 'Finalizado' : 'Próxima'; ?></span></td>
                            <td class="acciones" style="display:flex;gap:4px;flex-wrap:wrap;flex-direction:column;align-items:center;">
                                <button type="submit" name="editar_funcion" class="btn-edit" style="width:100%;min-width:80px;" <?php if($finalizada) echo 'disabled'; ?>>Editar</button>
                                <button type="submit" name="eliminar_funcion" class="btn-delete" style="width:100%;min-width:80px;" onclick="return confirm('¿Eliminar esta función?');">Eliminar</button>
                                <?php if($finalizada): ?>
                                    <span class="solo-eliminar-info">Solo puedes eliminar funciones finalizadas.<br>La edición está deshabilitada.</span>
                                <?php endif; ?>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <div class="form-container" style="min-width:0;">
            <h2 style="word-break:break-word;">Agregar nueva función</h2>
            <form method="POST" style="display:flex;flex-direction:column;gap:12px;width:100%;max-width:400px;margin:0 auto;">
                <div class="form-group" style="width:100%;">
                    <label for="id_pelicula">Película</label>
                    <select name="id_pelicula" id="id_pelicula" required style="width:100%;">
                        <option value="">Selecciona una película</option>
                        <?php foreach ($peliculas as $pelicula): ?>
                            <option value="<?php echo $pelicula['id_pelicula']; ?>"><?php echo htmlspecialchars($pelicula['titulo']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="width:100%;">
                    <label for="id_sala">Sala</label>
                    <select name="id_sala" id="id_sala" required style="width:100%;">
                        <option value="">Selecciona una sala</option>
                        <?php foreach ($salas as $sala): ?>
                            <option value="<?php echo $sala['id_sala']; ?>"><?php echo htmlspecialchars($sala['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="width:100%;">
                    <label for="fecha_hora">Fecha y Hora</label>
                    <input type="datetime-local" name="fecha_hora" id="fecha_hora" required style="width:100%;">
                </div>
                <div class="form-group" style="width:100%;">
                    <label for="precio">Precio</label>
                    <input type="number" name="precio" id="precio" placeholder="Precio" min="0" requiered step="0.01" style="width:100%;">
                </div>
                <div class="form-actions" style="width:100%;">
                    <button type="submit" name="agregar_funcion" class="btn-submit" style="width:100%;">Agregar Función</button>
                </div>
            </form>
        </div>
        <div style="text-align: center;width:100%;margin-top:16px;">
            <a href="panel_admin.php" class="btn-back" style="width:100%;max-width:300px;">&larr; Volver al panel principal</a>
        </div>
    </div>
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
</body>
</html> 