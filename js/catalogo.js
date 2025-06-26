const apiKey = 'e6822a7ed386f7102b6a857ea5e3c17f';
const imgBase = 'https://image.tmdb.org/t/p/w500';
let pagina = 1;
let cargadas = 0;
const porPagina = 30;
const maxPeliculasCargaInicial = 36; // 75 películas
let catalogoPeliculas = []; // Almacenar películas populares cargadas para filtros 

// Función para mostrar películas
function mostrarPeliculas(peliculas) {
    const grid = document.querySelector('.grid-peliculas');
    if (!grid) {
        console.error('Elemento .grid-peliculas no encontrado.');
        return;
    }
    grid.innerHTML = ''; // Limpiar el contenido anterior
    
    if (peliculas.length === 0) {
        const mensaje = document.createElement('div');
        mensaje.style.cssText = `
            width: 100%;
            padding: 20px;
            text-align: center;
            background: #222;
            border-radius: 10px;
            margin: 20px 0;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
        `;
        mensaje.textContent = 'No se encontraron películas.';
        grid.appendChild(mensaje);
        return;
    }

    peliculas.forEach(peli => {
        const img = document.createElement('img');
        img.src = peli.poster_path ? imgBase + peli.poster_path : '../img/placeholder.png';
        img.alt = peli.title;

        const link = document.createElement('a');
        link.href = `../pages/pelicula/pelicula.php?id=${peli.id}`;
        link.appendChild(img);
        grid.appendChild(link);
    });
}

// Función para cargar películas populares para la carga inicial y filtros locales
function cargarPeliculasPopularesInicial() {
    if (cargadas >= maxPeliculasCargaInicial) return;

    fetch(`https://api.themoviedb.org/3/movie/popular?api_key=${apiKey}&language=es-ES&page=${pagina}`)
        .then(res => res.json())
        .then(data => {
            const nuevasPeliculas = data.results.slice(0, maxPeliculasCargaInicial - cargadas);
            
            // Obtener detalles completos de cada película
            const promesasDetalles = nuevasPeliculas.map(peli => 
                fetch(`https://api.themoviedb.org/3/movie/${peli.id}?api_key=${apiKey}&language=es-ES`)
                    .then(res => res.json())
                    .then(detalles => ({
                        ...peli,
                        runtime: detalles.runtime
                    }))
            );

            Promise.all(promesasDetalles)
                .then(peliculasConDetalles => {
                    catalogoPeliculas = catalogoPeliculas.concat(peliculasConDetalles);
                    cargadas += peliculasConDetalles.length;

                    if (cargadas < maxPeliculasCargaInicial && data.page < data.total_pages) {
                        pagina++;
                        cargarPeliculasPopularesInicial();
                    } else {
                        mostrarPeliculas(catalogoPeliculas);
                        const urlParams = new URLSearchParams(window.location.search);
                        const searchTerm = urlParams.get('search');
                        if (searchTerm) {
                            filtrarPeliculasLocales(searchTerm);
                        }
                    }
                });
        })
        .catch(error => {
            console.error('Error al cargar películas populares iniciales:', error);
            const grid = document.querySelector('.grid-peliculas');
            if (grid) {
                grid.innerHTML = '<p>Error al cargar películas populares. Inténtalo de nuevo más tarde.</p>';
            }
        });
}

// Función para filtrar películas cargadas localmente
function filtrarPeliculasLocales(query) {
    if (!query) {
        mostrarPeliculas(catalogoPeliculas);
        return;
    }
    const queryLower = query.toLowerCase().trim();
    const filtradas = catalogoPeliculas.filter(peli => {
        return peli.title.toLowerCase().includes(queryLower);
    });
    mostrarPeliculas(filtradas);
}

// Hacer la función de búsqueda accesible globalmente
window.performCatalogSearch = function(query) {
    filtrarPeliculasLocales(query);
};

document.addEventListener('DOMContentLoaded', () => {
    // Lógica de inicialización al cargar la página

    // Iniciar la carga inicial de películas populares
    cargarPeliculasPopularesInicial();
    // Lógica del modal de filtros (mantener la misma que ya tenías)
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

    // Cargar géneros desde la API (mantener la misma que ya tenías)
    fetch(`https://api.themoviedb.org/3/genre/movie/list?api_key=${apiKey}&language=es-ES`)
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('filtro-genero');
            if (select) { // Verificar si el elemento existe
                data.genres.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g.id;
                    opt.textContent = g.name;
                    select.appendChild(opt);
                });
            }
        })
        .catch(error => console.error('Error al cargar géneros:', error));

    // Lógica de filtros (ajustar para usar catalogoPeliculas y mostrarPeliculas)
    const formFiltros = document.getElementById('form-filtros');
    if (formFiltros) { // Verificar si el elemento existe
        formFiltros.onsubmit = function(e) {
            e.preventDefault();
            const genero = document.getElementById('filtro-genero') ? document.getElementById('filtro-genero').value : "";
            const anoDesde = document.getElementById('filtro-ano-desde') ? parseInt(document.getElementById('filtro-ano-desde').value) : NaN;
            const anoHasta = document.getElementById('filtro-ano-hasta') ? parseInt(document.getElementById('filtro-ano-hasta').value) : NaN;
            const duracion = document.getElementById('filtro-duracion') ? document.getElementById('filtro-duracion').value : "";

            let filtradas = catalogoPeliculas.filter(peli => {
                let ok = true;
                if (genero && peli.genre_ids && !peli.genre_ids.includes(Number(genero))) ok = false; // Añadir verificación peli.genre_ids
                if (!isNaN(anoDesde) && peli.release_date) {
                    const year = parseInt(peli.release_date.slice(0,4));
                    if (year < anoDesde) ok = false;
                }
                if (!isNaN(anoHasta) && peli.release_date) {
                    const year = parseInt(peli.release_date.slice(0,4));
                    if (year > anoHasta) ok = false;
                }
                if (duracion && peli.runtime) {
                    if (duracion === "1" && peli.runtime > 120) ok = false;
                    if (duracion === "2" && (peli.runtime < 121 || peli.runtime > 150)) ok = false;
                    if (duracion === "3" && peli.runtime <= 150) ok = false;
                }
                return ok;
            });
            mostrarPeliculas(filtradas); // Usar la función mostrarPeliculas general
            document.getElementById('modal-filtros').classList.remove('activo');
        };

        // Botón limpiar filtros (mantener la misma que ya tenías)
        const btnLimpiar = document.createElement('button');
        btnLimpiar.type = 'button';
        btnLimpiar.id = 'btn-limpiar-filtros';
        btnLimpiar.textContent = 'Limpiar filtros';
        btnLimpiar.className = 'btn-filtrar limpiar';
        btnLimpiar.onclick = () => {
            formFiltros.reset();
            mostrarPeliculas(catalogoPeliculas); // Mostrar películas populares cargadas inicialmente
        };
        formFiltros.appendChild(btnLimpiar);
    }

    // Configurar los listeners de búsqueda
    const searchInputs = document.querySelectorAll('input[type="text"]');
    searchInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            filtrarPeliculasLocales(e.target.value);
        });
    });
});



