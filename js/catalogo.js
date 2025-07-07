const imgBase = 'https://image.tmdb.org/t/p/w500';
let pagina = 1;
let cargadas = 0;
const porPagina = 20;
let catalogoPeliculas = []; // Almacenar películas cargadas para filtros
let peliculasMostradas = 0; // Contador de películas mostradas
let todasLasPeliculas = []; // Todas las películas cargadas
let hayMasPeliculas = true; // Flag para saber si hay más películas
let cargando = false; // Flag para evitar múltiples cargas simultáneas

// Función para mostrar películas
function mostrarPeliculas(peliculas, agregar = false) {
    const grid = document.querySelector('.grid-peliculas');
    if (!grid) {
        console.error('Elemento .grid-peliculas no encontrado.');
        return;
    }
    
    if (!agregar) {
        grid.innerHTML = ''; // Limpiar el contenido anterior
        peliculasMostradas = 0;
    }
    
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

    // Mostrar solo las películas que corresponden
    const peliculasAMostrar = peliculas.slice(peliculasMostradas, peliculasMostradas + porPagina);
    
    peliculasAMostrar.forEach(peli => {
        const card = document.createElement('div');
        card.className = 'pelicula-card';
        card.style.cssText = `
            position: relative;
            display: inline-block;
            margin: 5px;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
            cursor: pointer;
        `;
        
        const img = document.createElement('img');
        
        // Manejar imagen según el tipo de película
        if (peli.poster_path) {
            if (peli.tipo === 'manual') {
                // Para películas manuales, usar la URL completa o agregar ruta relativa
                img.src = peli.poster_path.startsWith('http') ? peli.poster_path : '../' + peli.poster_path;
            } else {
                // Para películas de la API, usar la base de TMDB
                img.src = imgBase + peli.poster_path;
            }
        } else {
            img.src = '../img/placeholder.png';
        }
        
        img.alt = peli.title;

        // Crear overlay para valoración
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0, 0, 0, 0.8);
            color: #ffb300;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 4px;
        `;
        
        // Agregar estrella y valoración
        const estrella = document.createElement('span');
        estrella.innerHTML = '★';
        estrella.style.color = '#ffb300';
        
        const valoracion = document.createElement('span');
        const valoracionPromedio = parseFloat(peli.valoracion_promedio || 0).toFixed(1);
        valoracion.textContent = valoracionPromedio;
        
        overlay.appendChild(estrella);
        overlay.appendChild(valoracion);
        
        card.appendChild(img);
        card.appendChild(overlay);
        
        const link = document.createElement('a');
        link.href = `../pages/pelicula/pelicula.php?id=${peli.id}`;
        link.appendChild(card);
        grid.appendChild(link);
        
        // Efecto hover
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'scale(1.05)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'scale(1)';
        });
    });
    
    peliculasMostradas += peliculasAMostrar.length;
    hayMasPeliculas = peliculasMostradas < peliculas.length;
    
    // Remover indicador de carga si existe
    const indicadorCarga = document.querySelector('.indicador-carga');
    if (indicadorCarga) {
        indicadorCarga.remove();
    }
}

// Función para mostrar indicador de carga
function mostrarIndicadorCarga() {
    const grid = document.querySelector('.grid-peliculas');
    if (!grid) return;
    
    const indicador = document.createElement('div');
    indicador.className = 'indicador-carga';
    indicador.style.cssText = `
        width: 100%;
        padding: 20px;
        text-align: center;
        color:rgb(0, 0, 0);
        font-size: 16px;
        font-weight: bold;
    `;
    indicador.textContent = 'Cargando más películas...';
    grid.appendChild(indicador);
}

// Función para verificar si el usuario está cerca del final de la página
function verificarScroll() {
    if (cargando || !hayMasPeliculas) return;
    
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;
    
    // Si el usuario está a 200px del final, cargar más películas
    if (scrollTop + windowHeight >= documentHeight - 200) {
        cargarMasPeliculas();
    }
}

// Función para cargar más películas
function cargarMasPeliculas() {
    if (cargando || !hayMasPeliculas) return;
    
    cargando = true;
    mostrarIndicadorCarga();
    
    // Simular un pequeño delay para mejor UX
    setTimeout(() => {
        mostrarPeliculas(todasLasPeliculas, true);
        cargando = false;
    }, 500);
}

// Función para cargar películas desde la base de datos
function cargarPeliculasDesdeBD() {
    const url = `../php/obtener_peliculas.php?page=${pagina}&per_page=100`; // Cargar más películas
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            todasLasPeliculas = data.results;
            catalogoPeliculas = data.results;
            cargadas = data.results.length;
            
            // Mostrar las primeras 20 películas
            mostrarPeliculas(todasLasPeliculas, false);
            
            const urlParams = new URLSearchParams(window.location.search);
            const searchTerm = urlParams.get('search');
            if (searchTerm) {
                filtrarPeliculasLocales(searchTerm);
            }
        })
        .catch(error => {
            console.error('Error al cargar películas desde la base de datos:', error);
            const grid = document.querySelector('.grid-peliculas');
            if (grid) {
                grid.innerHTML = '<p>Error al cargar películas. Inténtalo de nuevo más tarde.</p>';
            }
        });
}

// Función para filtrar películas cargadas localmente
function filtrarPeliculasLocales(query) {
    if (!query) {
        todasLasPeliculas = catalogoPeliculas;
        mostrarPeliculas(todasLasPeliculas, false);
        return;
    }
    const queryLower = query.toLowerCase().trim();
    const filtradas = catalogoPeliculas.filter(peli => {
        return peli.title.toLowerCase().includes(queryLower);
    });
    todasLasPeliculas = filtradas;
    mostrarPeliculas(filtradas, false);
}

// Función para aplicar filtros avanzados
function aplicarFiltrosAvanzados() {
    const genero = document.getElementById('filtro-genero') ? document.getElementById('filtro-genero').value : "";
    const anoDesde = document.getElementById('filtro-ano-desde') ? parseInt(document.getElementById('filtro-ano-desde').value) : NaN;
    const anoHasta = document.getElementById('filtro-ano-hasta') ? parseInt(document.getElementById('filtro-ano-hasta').value) : NaN;
    const duracion = document.getElementById('filtro-duracion') ? document.getElementById('filtro-duracion').value : "";

    // Construir URL con filtros
    let url = `../php/obtener_peliculas.php?page=1&per_page=100`;
    
    if (genero) url += `&genero=${encodeURIComponent(genero)}`;
    if (!isNaN(anoDesde)) url += `&ano_desde=${anoDesde}`;
    if (!isNaN(anoHasta)) url += `&ano_hasta=${anoHasta}`;
    if (duracion) url += `&duracion=${duracion}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            todasLasPeliculas = data.results;
            mostrarPeliculas(data.results, false);
        })
        .catch(error => {
            console.error('Error al aplicar filtros:', error);
            mostrarPeliculas([], false);
        });
}

// Hacer la función de búsqueda accesible globalmente
window.performCatalogSearch = function(query) {
    if (!query) {
        todasLasPeliculas = catalogoPeliculas;
        mostrarPeliculas(todasLasPeliculas, false);
        return;
    }
    
    // Buscar en la base de datos
    const url = `../php/obtener_peliculas.php?page=1&per_page=100&search=${encodeURIComponent(query)}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            todasLasPeliculas = data.results;
            mostrarPeliculas(data.results, false);
        })
        .catch(error => {
            console.error('Error en la búsqueda:', error);
            // Fallback a búsqueda local
            filtrarPeliculasLocales(query);
        });
};

document.addEventListener('DOMContentLoaded', () => {
    // Lógica de inicialización al cargar la página

    // Iniciar la carga inicial de películas desde la base de datos
    cargarPeliculasDesdeBD();
    
    // Agregar listener de scroll para scroll infinito
    window.addEventListener('scroll', verificarScroll);
    
    // Lógica del modal de filtros
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

    // Cargar géneros desde la base de datos
    fetch('../php/obtener_generos.php')
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('filtro-genero');
            if (select && data.generos) {
                data.generos.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g;
                    opt.textContent = g;
                    select.appendChild(opt);
                });
            }
        })
        .catch(error => console.error('Error al cargar géneros:', error));

    // Lógica de filtros
    const formFiltros = document.getElementById('form-filtros');
    if (formFiltros) {
        formFiltros.onsubmit = function(e) {
            e.preventDefault();
            aplicarFiltrosAvanzados();
            document.getElementById('modal-filtros').classList.remove('activo');
        };

        // Botón limpiar filtros
        const btnLimpiar = document.createElement('button');
        btnLimpiar.type = 'button';
        btnLimpiar.id = 'btn-limpiar-filtros';
        btnLimpiar.textContent = 'Limpiar filtros';
        btnLimpiar.className = 'btn-filtrar limpiar';
        btnLimpiar.onclick = () => {
            formFiltros.reset();
            cargarPeliculasDesdeBD();
        };
        formFiltros.appendChild(btnLimpiar);
    }

    // Configurar los listeners de búsqueda
    const searchInputs = document.querySelectorAll('input[type="text"]');
    searchInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length >= 3 || e.target.value.length === 0) {
                window.performCatalogSearch(e.target.value);
            }
        });
    });
});



