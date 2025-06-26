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

    // Cargar los detalles de la película
    if (movieId) {
        fetch(`https://api.themoviedb.org/3/movie/${movieId}?api_key=${apiKey}&language=es-ES`)
            .then(res => res.json())
            .then(peli => {
                // Mostrar el título
                const titleElement = document.querySelector('.movie-title-butacas');
                if (titleElement) {
                    titleElement.textContent = peli.title;
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
            textoSeleccionadas.textContent = 'Ninguna Seleccionada';
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
                window.location.href = `../pages/pelicula/pelicula.php?id=${movieId}`;
            } else {
                // Si no hay movieId, simplemente regresar a la página de catálogo o inicio
                window.history.back();
            }
        });
    }

    // Agregar funcionalidad al botón Comprar Entradas
    const btnComprarEntradas = document.querySelector('.btn-comprar-entradas');
    const form = document.querySelector('.formulario-compra');

    if (btnComprarEntradas && form) {
        btnComprarEntradas.addEventListener('click', (e) => {
            e.preventDefault(); // Prevenir el envío por defecto del formulario
            
            const asientosSeleccionados = document.querySelectorAll('.butaca.seleccionado');
            if (asientosSeleccionados.length === 0) {
                alert('Por favor, selecciona al menos un asiento');
                return;
            }
            
            // Debug: Mostrar los IDs de los asientos seleccionados antes de enviar
            const idsAsientosParaEnviar = Array.from(asientosSeleccionados).map(b => b.dataset.id);
            console.log('Asientos seleccionados para enviar:', idsAsientosParaEnviar);
            
            // Eliminar inputs ocultos de asientos anteriores si existen
            form.querySelectorAll('input[name="asientos[]"]').forEach(input => input.remove());
            
            // Agregar inputs ocultos para cada asiento seleccionado
            asientosSeleccionados.forEach(asiento => {
                // Verificar que el dataset.id exista antes de agregarlo
                if (asiento.dataset.id) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'asientos[]';
                    input.value = asiento.dataset.id;
                    form.appendChild(input);
                } else {
                    console.error('Error: Asiento seleccionado sin dataset.id', asiento);
                }
            });
            
            // Enviar el formulario
            form.submit();
        });
    }
});
