document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('busqueda-pelicula');
    const contenedor = document.getElementById('comentarios-container');

    async function buscarComentarios(query) {
        const res = await fetch(`../pages/lista-comentarios.php?ajax=1&search=${encodeURIComponent(query)}`);
        const data = await res.json();
        if (data.error) {
            contenedor.innerHTML = `<p class="mensaje-error">${data.error}</p>`;
            return;
        }
        if (data.comentarios && data.comentarios.length > 0) {
            contenedor.innerHTML = data.comentarios.map(c => `
                <div class="comentario-card">
                    <img src="${c.pelicula_poster ? 'https://image.tmdb.org/t/p/w92' + c.pelicula_poster : '../img/default_poster.png'}" class="poster-comentario">
                    <div class="comentario-info">
                        <h3>${c.pelicula_titulo}</h3>
                        <p><strong>Usuario:</strong> ${c.nombre_usuario}</p>
                        <p><strong>Comentario:</strong> ${c.comentario}</p>
                        <p><strong>Valoración:</strong> ${c.valoracion}/5</p>
                        <p><small>Fecha: ${new Date(c.fecha_comentario).toLocaleDateString()}</small></p>
                    </div>
                </div>
            `).join('');
        } else {
            contenedor.innerHTML = '<p class="mensaje-sin-comentarios">No se encontraron comentarios.</p>';
        }
    }

    // Búsqueda en tiempo real
    inputBusqueda.addEventListener('input', function() {
        buscarComentarios(this.value);
    });

    // Cargar todos al inicio
    buscarComentarios('');
}); 