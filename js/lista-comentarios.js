document.addEventListener('DOMContentLoaded', () => {
    const comentariosContainer = document.getElementById('comentarios-container');

    // Simulamos algunos comentarios. En una aplicación real, los cargarías de una fuente de datos.
    const comentarios = [
        { nombrePelicula: "Nombre Película", texto: "Lorem ipsum dolor sit amet, consectetur adipiscing elit." },
        { nombrePelicula: "Otra Película", texto: "Excelente película, muy recomendada." },
        { nombrePelicula: "Película Interesante", texto: "Me gustó mucho la trama y los personajes." },
        { nombrePelicula: "Nombre Película", texto: "Lorem ipsum dolor sit amet, consectetur adipiscing elit." },
        { nombrePelicula: "Otra Película", texto: "Excelente película, muy recomendada." },
        { nombrePelicula: "Película Interesante", texto: "Me gustó mucho la trama y los personajes." },
        { nombrePelicula: "Nombre Película", texto: "Lorem ipsum dolor sit amet, consectetur adipiscing elit." },
        { nombrePelicula: "Otra Película", texto: "Excelente película, muy recomendada." },
        { nombrePelicula: "Película Interesante", texto: "Me gustó mucho la trama y los personajes." },
        { nombrePelicula: "Nombre Película", texto: "Lorem ipsum dolor sit amet, consectetur adipiscing elit." },
        { nombrePelicula: "Otra Película", texto: "Excelente película, muy recomendada." },
        { nombrePelicula: "Película Interesante", texto: "Me gustó mucho la trama y los personajes." },
        { nombrePelicula: "Nombre Película", texto: "Lorem ipsum dolor sit amet, consectetur adipiscing elit." },
        { nombrePelicula: "Otra Película", texto: "Excelente película, muy recomendada." },
        { nombrePelicula: "Película Interesante", texto: "Me gustó mucho la trama y los personajes." },
        { nombrePelicula: "Nombre Película", texto: "Lorem ipsum dolor sit amet, consectetur adipiscing elit." },
        { nombrePelicula: "Otra Película", texto: "Excelente película, muy recomendada." },
        { nombrePelicula: "Película Interesante", texto: "Me gustó mucho la trama y los personajes." },
        { nombrePelicula: "Nombre Película", texto: "Lorem ipsum dolor sit amet, consectetur adipiscing elit." },
        { nombrePelicula: "Otra Película", texto: "Excelente película, muy recomendada." },
        { nombrePelicula: "Película Interesante", texto: "Me gustó mucho la trama y los personajes." },
        { nombrePelicula: "Nombre Película", texto: "Lorem ipsum dolor sit amet, consectetur adipiscing elit." },
        { nombrePelicula: "Otra Película", texto: "Excelente película, muy recomendada." },
        { nombrePelicula: "Película Interesante", texto: "Me gustó mucho la trama y los personajes." },
        { nombrePelicula: "Nombre Película", texto: "Lorem ipsum dolor sit amet, consectetur adipiscing elit." },
        { nombrePelicula: "Otra Película", texto: "Excelente película, muy recomendada." },
        { nombrePelicula: "Película Interesante", texto: "Me gustó mucho la trama y los personajes." },
        
    ];

    if (comentarios.length === 0) {
        const mensaje = document.createElement('div');
        mensaje.classList.add('mensaje-sin-comentarios');
        mensaje.textContent = 'Aún no hay comentarios.';
        comentariosContainer.appendChild(mensaje);
    } else {
        // Esta parte ya no se ejecutará con la lista vacía, pero la mantengo por si cargas comentarios reales después
        comentarios.forEach(comentario => {
            const comentarioDiv = document.createElement('div');
            comentarioDiv.classList.add('comentario-tarjeta');

            comentarioDiv.innerHTML = `
                <div class="icono-usuario-comentario">
                    <img src="../img/User.png" alt="Icono de usuario">
                </div>
                <div class="contenido-comentario">
                    <div class="nombre-pelicula-comentario">${comentario.nombrePelicula}</div>
                    <div class="texto-comentario">${comentario.texto}</div>
                </div>
            `;
            comentariosContainer.appendChild(comentarioDiv);
        });
    }
}); 