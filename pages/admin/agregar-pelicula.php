<?php
// pages/admin/agregar_pelicula.php
require_once '../../php/conexion.php';

$mensaje = '';
$clase_mensaje = '';

// Obtener géneros desde la API de TMDB
$api_key = 'e6822a7ed386f7102b6a857ea5e3c17f'; // Reemplaza por tu API KEY real
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $generos_ids = isset($_POST['genero']) ? $_POST['genero'] : [];
    
    // Convertir IDs de géneros a nombres
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
    $estado = 'activa';
    $origen = 'manual';

    // Manejo de imagen de portada
    $imagen_portada = '';
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
                $mensaje = "Error al subir la imagen.";
                $clase_mensaje = "error";
            }
        } else {
            $mensaje = "Formato de imagen no válido. Use: jpg, jpeg, png, gif, webp";
            $clase_mensaje = "error";
        }
    }

    if (empty($mensaje)) {
        $sql = "INSERT INTO peliculas (titulo, descripcion, genero, fecha_estreno, duracion, imagen_portada, trailer_url, actores, estado, origen)
                VALUES (:titulo, :descripcion, :genero, :fecha_estreno, :duracion, :imagen_portada, :trailer_url, :actores, :estado, :origen)";
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
        $stmt->bindParam(':origen', $origen);

        if ($stmt->execute()) {
            $mensaje = "Película guardada correctamente.";
            $clase_mensaje = "exito";
        } else {
            $mensaje = "Error al agregar la película.";
            $clase_mensaje = "error";
        }
    }
}
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
            color: white;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Agregar Película Manualmente</h1>
            <p>Completa el formulario para agregar una nueva película al catálogo.</p>
        </div>

        <?php if ($mensaje && $clase_mensaje === "exito"): ?>
            <div class="mensaje exito">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <?php if ($mensaje && $clase_mensaje === "error"): ?>
            <div class="mensaje error">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título:</label>
                    <input type="text" name="titulo" id="titulo" placeholder="Título de la Película" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" id="descripcion" placeholder="Descripción de la Película" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Géneros:</label>
                    <div class="generos-grid">
                    <?php foreach ($generos_disponibles as $id => $nombre): ?>
                        <div class="genero-option">
                            <input type="checkbox" name="genero[]" value="<?php echo $id; ?>" id="genero_<?php echo $id; ?>">
                            <label for="genero_<?php echo $id; ?>"><?php echo $nombre; ?></label>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="fecha_estreno">Fecha de Estreno (YYYY):</label>
                    <input type="number" name="fecha_estreno" id="fecha_estreno" placeholder="Ej: 2000" min="1900" max="2030" required>
                </div>
                
                <div class="form-group">
                    <label for="duracion">Duración (minutos):</label>
                    <input type="number" name="duracion" id="duracion" placeholder="Ej: 150" min="1" max="999" required>
                </div>
                
                <div class="form-group">
                    <label for="trailer_url">URL del Trailer (YouTube):</label>
                    <input type="url" name="trailer_url" id="trailer_url" placeholder="https://www.youtube.com/watch?v=..." pattern="https?://.*">
                    <small style="color: #666;">Ejemplo: https://www.youtube.com/watch?v=dQw4w9WgXcQ</small>
                </div>
                
                <div class="form-group">
                    <label for="actores">Actores:</label>
                    <textarea name="actores" id="actores" placeholder="Lista de actores principales (separados por comas)"></textarea>
                    <small style="color: #666;">Ejemplo: Tom Hanks, Emma Watson, Leonardo DiCaprio</small>
                </div>
                
                <div class="form-group">
                    <label>Imagen de Portada:</label>
                    <div class="tipo-imagen-container">
                        <label class="tipo-imagen-option">
                            <input type="radio" name="tipo_imagen" value="url" checked onchange="toggleImagenTipo()"> Usar URL
                        </label>
                        <label class="tipo-imagen-option">
                            <input type="radio" name="tipo_imagen" value="archivo" onchange="toggleImagenTipo()"> Subir archivo
                        </label>
                    </div>
                    
                    <div id="campo-url" class="campo-imagen activo">
                        <input type="url" name="imagen_url" id="imagen_url" placeholder="https://ejemplo.com/imagen.jpg" onchange="previsualizarImagen(this.value)">
                    </div>
                    
                    <div id="campo-archivo" class="campo-imagen">
                        <input type="file" name="imagen_archivo" id="imagen_archivo" accept="image/*" onchange="previsualizarArchivo(this)">
                    </div>
                    
                    <img id="imagen-preview" class="imagen-preview" alt="Vista previa">
                </div>
                
                <button type="submit" class="btn-submit">Agregar Película</button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="panel_admin.php" class="logout-btn">Volver al Panel</a>
        </div>
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
            
            // Limpiar previsualización
            document.getElementById('imagen-preview').style.display = 'none';
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
    </script>
</body>
</html> 
