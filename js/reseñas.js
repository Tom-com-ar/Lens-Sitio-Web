document.addEventListener('DOMContentLoaded', function() {
    const reseñaForm = document.getElementById('reseña-form');
    const movieId = new URLSearchParams(window.location.search).get('id');

    if (!movieId) {
        console.error('No se encontró el ID de la película');
        return;
    }

    // Cargar comentarios existentes
    cargarComentarios();

    if (reseñaForm) {
        reseñaForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const comentario = document.getElementById('reseña').value.trim();
            const valoracion = document.getElementById('valoracion').value;

            if (!comentario || !valoracion) {
                alert('Por favor completa todos los campos');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('tmdb_id', movieId);
                formData.append('comentario', comentario);
                formData.append('valoracion', valoracion);

                const response = await fetch('../pages/reseñas.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }

                const data = await response.json();
                
                if (data.error) {
                    alert(data.error);
                } else if (data.success) {
                    alert('¡Reseña enviada con éxito!');
                    reseñaForm.reset();
                    cargarComentarios();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al enviar la reseña. Por favor intenta de nuevo.');
            }
        });
    }
});

async function cargarComentarios() {
    const movieId = new URLSearchParams(window.location.search).get('id');
    if (!movieId) return;

    try {
        const response = await fetch(`../pages/reseñas.php?tmdb_id=${movieId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Error al cargar los comentarios');
        }

        const data = await response.json();
        
        if (data.error) {
            console.error(data.error);
        } else if (data.comentarios) {
            mostrarComentarios(data.comentarios);
        }
    } catch (error) {
        console.error('Error al cargar comentarios:', error);
    }
}
