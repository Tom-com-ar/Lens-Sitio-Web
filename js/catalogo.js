const apiKey = 'e6822a7ed386f7102b6a857ea5e3c17f';
const imgBase = 'https://image.tmdb.org/t/p/w500';
let pagina = 1;
let cargadas = 0;
const porPagina = 30;
const maxPeliculas = 150; // Limitar a 60 películas
let catalogoPeliculas = [];

// Cargar hasta 60 películas (2 páginas de 30)
function cargarPeliculas() {
    if (cargadas >= maxPeliculas) return;
    fetch(`https://api.themoviedb.org/3/movie/popular?api_key=${apiKey}&language=es-ES&page=${pagina}`)
        .then(res => res.json())
        .then(data => {
            const grid = document.querySelector('.grid-peliculas');
            let restantes = Math.min(porPagina, maxPeliculas - cargadas);
            let pelis = data.results.slice(0, restantes);
            pelis.forEach(peli => {
                const img = document.createElement('img');
                img.src = imgBase + peli.poster_path;
                img.alt = peli.title;

                const link = document.createElement('a');
                link.href = `./pelicula/pelicula.html?id=${peli.id}`;
                link.appendChild(img);
                grid.appendChild(link);
            });
            // Guardar en catálogo para filtros
            catalogoPeliculas = catalogoPeliculas.concat(pelis);
            cargadas += pelis.length;
            if (cargadas < maxPeliculas && data.page < data.total_pages) {
                pagina++;
                cargarPeliculas();
            } else {
                pagina++;
            }
        });
}

document.addEventListener('DOMContentLoaded', () => {
    cargarPeliculas();
    document.getElementById('btn-mas').addEventListener('click', cargarPeliculas);
});

// Modal abrir/cerrar
document.getElementById('abrir-filtros').onclick = () => {
    document.getElementById('modal-filtros').classList.add('activo');
};
document.getElementById('cerrar-modal').onclick = () => {
    document.getElementById('modal-filtros').classList.remove('activo');
};
window.onclick = function(event) {
    if (event.target === document.getElementById('modal-filtros')) {
        document.getElementById('modal-filtros').classList.remove('activo');
    }
};

// Cargar géneros desde la API
fetch(`https://api.themoviedb.org/3/genre/movie/list?api_key=${apiKey}&language=es-ES`)
    .then(res => res.json())
    .then(data => {
        const select = document.getElementById('filtro-genero');
        data.genres.forEach(g => {
            const opt = document.createElement('option');
            opt.value = g.id;
            opt.textContent = g.name;
            select.appendChild(opt);
        });
    });

// Mostrar películas filtradas
function mostrarPeliculasFiltradas(peliculas) {
    const grid = document.querySelector('.grid-peliculas');
    grid.innerHTML = '';
    peliculas.slice(0, maxPeliculas).forEach(peli => {
        const img = document.createElement('img');
        img.src = imgBase + peli.poster_path;
        img.alt = peli.title;
        const link = document.createElement('a');
        link.href = `./pelicula/pelicula.html?id=${peli.id}`;
        link.appendChild(img);
        grid.appendChild(link);
    });
}

// Filtros
document.getElementById('form-filtros').onsubmit = function(e) {
    e.preventDefault();
    const genero = document.getElementById('filtro-genero').value;
    const anoDesde = parseInt(document.getElementById('filtro-ano-desde').value);
    const anoHasta = parseInt(document.getElementById('filtro-ano-hasta').value);
    const duracion = document.getElementById('filtro-duracion') ? document.getElementById('filtro-duracion').value : "";

    let filtradas = catalogoPeliculas.filter(peli => {
        let ok = true;
        if (genero && !peli.genre_ids.includes(Number(genero))) ok = false;
        if (anoDesde && peli.release_date) {
            const year = parseInt(peli.release_date.slice(0,4));
            if (year < anoDesde) ok = false;
        }
        if (anoHasta && peli.release_date) {
            const year = parseInt(peli.release_date.slice(0,4));
            if (year > anoHasta) ok = false;
        }
        // Duración solo si tienes ese campo en el catálogo
        if (duracion && peli.runtime) {
            if (duracion === "1" && peli.runtime > 90) ok = false;
            if (duracion === "2" && (peli.runtime < 91 || peli.runtime > 120)) ok = false;
            if (duracion === "3" && peli.runtime <= 120) ok = false;
        }
        return ok;
    });
    mostrarPeliculasFiltradas(filtradas);
    document.getElementById('modal-filtros').classList.remove('activo');
};

// Botón limpiar filtros
const formFiltros = document.getElementById('form-filtros');
if (!document.getElementById('btn-limpiar-filtros')) {
    const btnLimpiar = document.createElement('button');
    btnLimpiar.type = 'button';
    btnLimpiar.id = 'btn-limpiar-filtros';
    btnLimpiar.textContent = 'Limpiar filtros';
    btnLimpiar.className = 'btn-filtrar limpiar';
    btnLimpiar.onclick = () => {
        formFiltros.reset();
        mostrarPeliculasFiltradas(catalogoPeliculas);
    };
    formFiltros.appendChild(btnLimpiar);
}



