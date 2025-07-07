const filas = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
const columnas = 12;
const grid = document.querySelector('.butacas-grid');
const textoSeleccionadas = document.querySelector('.butacas-texto');

// Crear el plano de butacas fijo (10x12)
function crearButacas() {
    if (!grid) return;
    grid.innerHTML = '';
    for (let i = 0; i < filas.length; i++) {
        for (let j = 1; j <= columnas; j++) {
            const butaca = document.createElement('div');
            butaca.classList.add('butaca');
            const id = `${filas[i]}${j}`;
            butaca.dataset.id = id;
            if (typeof asientosOcupados !== 'undefined' && asientosOcupados.includes(id)) {
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
    if (textoSeleccionadas) {
        textoSeleccionadas.textContent = ids.join(' - ') || 'Ninguna Seleccionada';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Solo crear el plano si existe el grid (es la vista de butacas)
    if (grid) {
        crearButacas();
    }
    // Botón borrar selección
    const btnBorrarSeleccion = document.querySelector('.btn-borrar-seleccion');
    if (btnBorrarSeleccion) {
        btnBorrarSeleccion.addEventListener('click', () => {
            // Seleccionar solo las butacas seleccionadas dentro de la cuadrícula
            const seleccionadas = document.querySelectorAll('.butacas-grid .butaca.seleccionado');
            seleccionadas.forEach(butaca => {
                butaca.classList.remove('seleccionado');
            });
            // Actualizar texto después de deseleccionar
            if (textoSeleccionadas) textoSeleccionadas.textContent = 'Ninguna Seleccionada';
        });
    }
    // Botón comprar entradas
    const btnComprarEntradas = document.querySelector('.btn-comprar-entradas');
    const form = document.querySelector('.formulario-compra');
    if (btnComprarEntradas && form) {
        btnComprarEntradas.addEventListener('click', (e) => {
            const asientosSeleccionados = document.querySelectorAll('.butaca.seleccionado');
            if (asientosSeleccionados.length === 0) {
                e.preventDefault();
                alert('Por favor, selecciona al menos un asiento');
                return;
            }
            // Eliminar inputs ocultos de asientos anteriores si existen
            form.querySelectorAll('input[name="asientos[]"]').forEach(input => input.remove());
            // Agregar inputs ocultos para cada asiento seleccionado
            asientosSeleccionados.forEach(asiento => {
                if (asiento.dataset.id) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'asientos[]';
                    input.value = asiento.dataset.id;
                    form.appendChild(input);
                }
            });
        });
    }
    // Botón volver atrás
    const btnVolverAtras = document.querySelector('.btn-volver-atras');
    if (btnVolverAtras) {
        btnVolverAtras.addEventListener('click', () => {
            window.history.back();
        });
    }
    // Cargar imagen de TMDB solo si es de la API
    const img = document.querySelector('.movie-poster-butacas');
    if (img && img.dataset.tmdbId && img.dataset.tmdbId !== '') {
        const tmdbId = img.dataset.tmdbId;
        fetch(`https://api.themoviedb.org/3/movie/${tmdbId}?api_key=e6822a7ed386f7102b6a857ea5e3c17f&language=es-ES`)
            .then(res => res.json())
            .then(data => {
                if (data && data.poster_path) {
                    img.src = 'https://image.tmdb.org/t/p/w500' + data.poster_path;
                }
        });
    }
});