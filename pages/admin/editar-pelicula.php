<?php
session_start();
require_once '../../php/conexion.php';

// Verificar que el usuario sea administrador
validar_admin($conexion);

$mensaje = '';
$error = '';
$pelicula = null;
$clase_mensaje = '';

// Obtener ID de la película
$id_pelicula = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pelicula <= 0) {
    header("Location: panel-peliculas.php");
    exit();
}

// Obtener géneros desde la API de TMDB
$api_key = 'e6822a7ed386f7102b6a857ea5e3c17f';
$generos_disponibles = [];
$generos_json = file_get_contents("https://api.themoviedb.org/3/genre/movie/list?api_key=$api_key&language=es-ES");
if ($generos_json) {
    $generos_data = json_decode($generos_json, true);
    if (isset($generos_data['genres'])) {
        foreach ($generos_data['genres'] as $g) {
            $generos_disponibles[$g['id']] = $g['name'];
        }
    }
}

// Obtener datos de la película desde la tabla unificada
try {
    $stmt = $conexion->prepare("SELECT * FROM peliculas WHERE id_pelicula = ?");
    $stmt->execute([$id_pelicula]);
    $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pelicula) {
        $error = "La película con ID $id_pelicula no fue encontrada.";
        // No redirigir para poder mostrar el error
    }
} catch (PDOException $e) {
    $error = 'Error al cargar la película: ' . $e->getMessage();
    $pelicula = null;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pelicula) {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    
    // Convertir IDs de género a nombres
    $generos_ids = isset($_POST['genero']) ? $_POST['genero'] : [];
    $generos_nombres = [];
    foreach ($generos_ids as $id) {
        if (isset($generos_disponibles[$id])) {
            $generos_nombres[] = $generos_disponibles[$id];
        }
    }
    $genero = implode(', ', $generos_nombres);

    $fecha_estreno = $_POST['fecha_estreno'];
    $duracion = $_POST['duracion'];
    $trailer_url = $_POST['trailer_url'];
    $actores = $_POST['actores'];
    $estado = $_POST['estado'];

    // Manejo de imagen de portada
    $imagen_portada = $pelicula['imagen_portada']; // Mantener la imagen actual por defecto
    if ($_POST['tipo_imagen'] === 'url') {
        $imagen_portada = $_POST['imagen_url'];
    } elseif ($_POST['tipo_imagen'] === 'archivo' && isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../img/peliculas/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['imagen_archivo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = uniqid() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['imagen_archivo']['tmp_name'], $filepath)) {
                $imagen_portada = 'img/peliculas/' . $filename;
            } else {
                $error = "Error al subir la imagen.";
                $clase_mensaje = "error";
            }
        } else {
            $error = "Formato de imagen no válido. Use: jpg, jpeg, png, gif, webp";
            $clase_mensaje = "error";
        }
    }

    if (empty($error)) {
        try {
            $sql = "UPDATE peliculas 
                    SET titulo = :titulo, descripcion = :descripcion, genero = :genero, 
                        fecha_estreno = :fecha_estreno, duracion = :duracion, 
                        imagen_portada = :imagen_portada, trailer_url = :trailer_url, 
                        actores = :actores, estado = :estado, fecha_actualizada = NOW()
                    WHERE id_pelicula = :id_pelicula";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':genero', $genero);
            $stmt->bindParam(':fecha_estreno', $fecha_estreno);
            $stmt->bindParam(':duracion', $duracion);
            $stmt->bindParam(':imagen_portada', $imagen_portada);
            $stmt->bindParam(':trailer_url', $trailer_url);
            $stmt->bindParam(':actores', $actores);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id_pelicula', $id_pelicula, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $mensaje = "Película actualizada correctamente.";
                $clase_mensaje = "exito";
                // Recargar datos de la película para mostrar los cambios
                $stmt = $conexion->prepare("SELECT * FROM peliculas WHERE id_pelicula = ?");
                $stmt->execute([$id_pelicula]);
                $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Error al actualizar la película.";
                $clase_mensaje = "error";
            }
        } catch (PDOException $e) {
            $error = "Error al actualizar la película: " . $e->getMessage();
            $clase_mensaje = "error";
        }
    }
}

// Convertir géneros de string (nombres) a array de IDs para el formulario
$generos_actuales_ids = [];
if ($pelicula && !empty($pelicula['genero'])) {
    $generos_nombres_actuales = array_map('trim', explode(',', $pelicula['genero']));
    $generos_disponibles_inverso = array_flip($generos_disponibles);
    foreach ($generos_nombres_actuales as $nombre) {
        if (isset($generos_disponibles_inverso[$nombre])) {
            $generos_actuales_ids[] = $generos_disponibles_inverso[$nombre];
        }
    }
}

// Determinar el tipo de imagen actual
$tipo_imagen_actual = 'url';
$imagen_url_actual = $pelicula['imagen_portada'] ?? '';
if ($pelicula && !empty($pelicula['imagen_portada']) && !str_starts_with($pelicula['imagen_portada'], 'http') && strpos($pelicula['imagen_portada'], 'img/peliculas/') !== false) {
    $tipo_imagen_actual = 'archivo';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Película - Panel de Administrador</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <style>
        .imagen-preview {
            max-width: 200px;
            max-height: 300px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
        }
        .tipo-imagen-container {
            margin-bottom: 15px;
        }
        .tipo-imagen-option {
            margin-right: 20px;
        }
        .campo-imagen {
            display: none;
        }
        .campo-imagen.activo {
            display: block;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="url"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .btn-submit {
            background: #ffb300;
            color: #222;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-submit:hover {
            background: #e6a200;
        }
        .mensaje {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .mensaje.exito {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        .btn-back:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>✏️ Editar Película</h1>
            <p>Modifica los datos de la película: <?php echo htmlspecialchars($pelicula['titulo'] ?? ''); ?></p>
        </div>

        <?php if ($mensaje && $clase_mensaje === "exito"): ?>
            <div class="mensaje exito">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <?php if ($error && $clase_mensaje === "error"): ?>
            <div class="mensaje error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($pelicula): ?>
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titulo">Título:</label>
                        <input type="text" name="titulo" id="titulo" value="<?php echo htmlspecialchars($pelicula['titulo']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea name="descripcion" id="descripcion" required><?php echo htmlspecialchars($pelicula['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Géneros:</label>
                        <div class="generos-grid">
                            <?php foreach ($generos_disponibles as $id => $nombre): ?>
                                <div class="genero-option">
                                    <input type="checkbox" name="genero[]" value="<?php echo $id; ?>" 
                                           id="genero_<?php echo $id; ?>"
                                           <?php echo in_array($id, $generos_actuales_ids) ? 'checked' : ''; ?>>
                                    <label for="genero_<?php echo $id; ?>"><?php echo $nombre; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_estreno">Fecha de Estreno (YYYY):</label>
                        <input type="number" name="fecha_estreno" id="fecha_estreno" 
                               value="<?php echo htmlspecialchars($pelicula['fecha_estreno']); ?>" min="1900" max="2030" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="duracion">Duración (minutos):</label>
                        <input type="number" name="duracion" id="duracion" 
                               value="<?php echo htmlspecialchars($pelicula['duracion']); ?>" min="1" max="999" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="trailer_url">URL del Trailer (YouTube):</label>
                        <input type="url" name="trailer_url" id="trailer_url" 
                               value="<?php echo htmlspecialchars($pelicula['trailer_url'] ?? ''); ?>" 
                               placeholder="https://www.youtube.com/watch?v=..." pattern="https?://.*">
                        <small style="color: #666;">Ejemplo: https://www.youtube.com/watch?v=dQw4w9WgXcQ</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="actores">Actores:</label>
                        <textarea name="actores" id="actores" 
                                  placeholder="Lista de actores principales (separados por comas)"><?php echo htmlspecialchars($pelicula['actores'] ?? ''); ?></textarea>
                        <small style="color: #666;">Ejemplo: Tom Hanks, Emma Watson, Leonardo DiCaprio</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Imagen de Portada:</label>
                        <div class="tipo-imagen-container">
                            <label class="tipo-imagen-option">
                                <input type="radio" name="tipo_imagen" value="url" <?php echo $tipo_imagen_actual === 'url' ? 'checked' : ''; ?> onchange="toggleImagenTipo()"> Usar URL
                            </label>
                            <label class="tipo-imagen-option">
                                <input type="radio" name="tipo_imagen" value="archivo" <?php echo $tipo_imagen_actual === 'archivo' ? 'checked' : ''; ?> onchange="toggleImagenTipo()"> Subir archivo
                            </label>
                        </div>
                        
                        <div id="campo-url" class="campo-imagen <?php echo $tipo_imagen_actual === 'url' ? 'activo' : ''; ?>">
                            <input type="url" name="imagen_url" id="imagen_url" 
                                   value="<?php echo $tipo_imagen_actual === 'url' ? htmlspecialchars($pelicula['imagen_portada']) : ''; ?>" 
                                   placeholder="https://ejemplo.com/imagen.jpg" onchange="previsualizarImagen(this.value)">
                        </div>
                        
                        <div id="campo-archivo" class="campo-imagen <?php echo $tipo_imagen_actual === 'archivo' ? 'activo' : ''; ?>">
                            <input type="file" name="imagen_archivo" id="imagen_archivo" accept="image/*" onchange="previsualizarArchivo(this)">
                            <?php if ($tipo_imagen_actual === 'archivo' && !empty($pelicula['imagen_portada'])): ?>
                                <small style="color: #666;">Imagen actual: <?php echo htmlspecialchars($pelicula['imagen_portada']); ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <img id="imagen-preview" class="imagen-preview" alt="Vista previa" 
                             src="<?php echo !empty($pelicula['imagen_portada']) ? ($tipo_imagen_actual === 'url' ? $pelicula['imagen_portada'] : '../../' . $pelicula['imagen_portada']) : ''; ?>"
                             style="<?php echo !empty($pelicula['imagen_portada']) ? 'display: block;' : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select name="estado" id="estado" required>
                            <option value="activa" <?php echo $pelicula['estado'] === 'activa' ? 'selected' : ''; ?>>Activa</option>
                            <option value="inactiva" <?php echo $pelicula['estado'] === 'inactiva' ? 'selected' : ''; ?>>Inactiva</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">💾 Guardar Cambios</button>
                        <a href="panel-peliculas.php" class="btn-back">← Volver</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="mensaje error">
                <?= $error ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="panel-peliculas.php" class="btn-back">← Volver al Panel</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleImagenTipo() {
            const tipoUrl = document.querySelector('input[value="url"]');
            const tipoArchivo = document.querySelector('input[value="archivo"]');
            const campoUrl = document.getElementById('campo-url');
            const campoArchivo = document.getElementById('campo-archivo');
            
            if (tipoUrl.checked) {
                campoUrl.classList.add('activo');
                campoArchivo.classList.remove('activo');
            } else {
                campoUrl.classList.remove('activo');
                campoArchivo.classList.add('activo');
            }
        }
        
        function previsualizarImagen(url) {
            const preview = document.getElementById('imagen-preview');
            if (url) {
                preview.src = url;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
        
        function previsualizarArchivo(input) {
            const preview = document.getElementById('imagen-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
        
        // Mostrar previsualización inicial si hay imagen
        document.addEventListener('DOMContentLoaded', function() {
            const preview = document.getElementById('imagen-preview');
            if (preview.src && preview.src !== window.location.href) {
                preview.style.display = 'block';
            }
        });
    </script>
</body>
</html> 