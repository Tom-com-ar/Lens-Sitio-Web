const filas = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
const columnas = 12;
const grid = document.querySelector('.butacas-grid');
const textoSeleccionadas = document.querySelector('.butacas-texto');

// Simulamos algunas butacas ocupadas
const butacasOcupadas = [];

function crearButacas() {
    for (let i = 0; i < filas.length; i++) {
        for (let j = 1; j <= columnas; j++) {
            const butaca = document.createElement('div');
            butaca.classList.add('butaca');

            const id = `${filas[i]}${j}`;
            butaca.dataset.id = id;

            if (butacasOcupadas.includes(id)) {
                butaca.classList.add('ocupado');
            } else {
                butaca.classList.add('disponible');
                butaca.addEventListener('click', () => seleccionarButaca(butaca));
            }

            grid.appendChild(butaca);
        }
    }
}

function seleccionarButaca(butaca) {
    butaca.classList.toggle('seleccionado');

    // Actualizar texto
    const seleccionadas = document.querySelectorAll('.butaca.seleccionado');
    const ids = Array.from(seleccionadas).map(b => b.dataset.id);
    textoSeleccionadas.textContent = ids.join(' - ') || 'Ninguna';
}

// Cargar los detalles de la película
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const movieId = urlParams.get('id');
    const apiKey = 'e6822a7ed386f7102b6a857ea5e3c17f';
    const imgBase = 'https://image.tmdb.org/t/p/w500';

    if (movieId) {
        fetch(`https://api.themoviedb.org/3/movie/${movieId}?api_key=${apiKey}&language=es-ES`)
            .then(res => res.json())
            .then(peli => {
                // Mostrar el título
                const titleElement = document.querySelector('.movie-title-butacas');
                if (titleElement) {
                    titleElement.textContent = peli.title;
                }

                // Mostrar la sinopsis
                const synopsisElement = document.querySelector('.movie-synopsis-butacas');
                if (synopsisElement) {
                    synopsisElement.textContent = peli.overview;
                }

                // Mostrar los géneros
                const genresElement = document.querySelector('.movie-genre-butacas');
                if (genresElement) {
                    genresElement.textContent = peli.genres.map(genre => genre.name).join(', ');
                }

                // Mostrar la imagen/póster
                const posterElement = document.querySelector('.movie-poster-butacas');
                if (posterElement) {
                    posterElement.src = imgBase + peli.poster_path;
                    posterElement.alt = peli.title;
                }
            })
            .catch(error => {
                console.error('Error al obtener los detalles de la película:', error);
            });
    }

    // Crear las butacas después de cargar la página
    crearButacas();

    // Agregar funcionalidad al botón Borrar Selección
    const btnBorrarSeleccion = document.querySelector('.btn-borrar-seleccion');
    if (btnBorrarSeleccion) {
        btnBorrarSeleccion.addEventListener('click', () => {
            // Seleccionar solo las butacas seleccionadas dentro de la cuadrícula
            const seleccionadas = document.querySelectorAll('.butacas-grid .butaca.seleccionado');
            seleccionadas.forEach(butaca => {
                butaca.classList.remove('seleccionado');
            });
            
            // Actualizar texto después de deseleccionar
            const butacasRestantesSeleccionadas = document.querySelectorAll('.butaca.seleccionado');
            const idsRestantes = Array.from(butacasRestantesSeleccionadas).map(b => b.dataset.id);
            textoSeleccionadas.textContent = idsRestantes.join(' - ') || 'Ninguna';
        });
    }

    // Agregar funcionalidad al botón Volver Atrás
    const btnVolverAtras = document.querySelector('.btn-volver-atras');
    if (btnVolverAtras) {
        btnVolverAtras.addEventListener('click', () => {
            // Recuperar el movieId de la URL actual para volver a la página de detalles correcta
            const urlParams = new URLSearchParams(window.location.search);
            const movieId = urlParams.get('id');
            if (movieId) {
                window.location.href = `../pages/pelicula/pelicula.html?id=${movieId}`;
            } else {
                // Si no hay movieId, simplemente regresar a la página de catálogo o inicio
                window.history.back();
            }
        });
    }
});
