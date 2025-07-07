const imgBase = 'https://image.tmdb.org/t/p/w500';

// Función para cargar películas desde la base de datos
function cargarPeliculasDesdeBD() {
    return fetch('./php/obtener_peliculas.php?page=1&per_page=20')
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            return data.results;
        })
        .catch(error => {
            console.error('Error al cargar películas desde la base de datos:', error);
            return [];
        });
}

// Función para obtener la URL de imagen correcta según el tipo de película
function obtenerUrlImagen(peli) {
    if (peli.poster_path) {
        if (peli.tipo === 'manual') {
            // Para películas manuales, usar la URL completa o agregar ruta relativa
            return peli.poster_path.startsWith('http') ? peli.poster_path : './' + peli.poster_path;
        } else {
            // Para películas de la API, usar la base de TMDB
            return imgBase + peli.poster_path;
        }
    }
    return './img/placeholder.png';
}

//De 768px hasta pantallas mas pequeñas

// Scrool horizontal hasta 768px - Películas Populares
cargarPeliculasDesdeBD()
    .then(peliculas => {
        const carrusel_populares = document.querySelector('.grid-populares');
        carrusel_populares.innerHTML = '';
        peliculas.slice(0, 8).forEach(peli => {
            const img = document.createElement('img');
            img.src = obtenerUrlImagen(peli);
            img.alt = peli.title;
           
            const link = document.createElement('a');
            link.href = `pages/pelicula/pelicula.php?id=${peli.id}`;
            link.appendChild(img);
            carrusel_populares.appendChild(link);
        });
    });

//Scrool vertical hasta 768px - Grid de Películas
cargarPeliculasDesdeBD()
    .then(peliculas => {
        const grid_peliculas = document.querySelector('.grid-peliculas');
        grid_peliculas.innerHTML = '';
        peliculas.slice(0, 20).forEach(peli => {
            const img = document.createElement('img');
            img.src = obtenerUrlImagen(peli);
            img.alt = peli.title;
           
            const link = document.createElement('a');
            link.href = `pages/pelicula/pelicula.php?id=${peli.id}`;
            link.appendChild(img);
            grid_peliculas.appendChild(link);
        });
    });

//De 768px hasta pantallas mas grandes

//Recomendados 
function cargarRecomendados() {
    cargarPeliculasDesdeBD()
        .then(peliculas => {
            if (!peliculas || peliculas.length < 2) return;
            
            // Elegir 2 índices aleatorios distintos
            let idx1 = Math.floor(Math.random() * peliculas.length);
            let idx2;
            do {
                idx2 = Math.floor(Math.random() * peliculas.length);
            } while (idx2 === idx1);

            const pelis = [peliculas[idx1], peliculas[idx2]];
            const tarjetas = [document.querySelector('.tarjeta-izq'), document.querySelector('.tarjeta-der')];

            pelis.forEach((peli, i) => {
                tarjetas[i].innerHTML = `
                    <img src="${obtenerUrlImagen(peli)}" alt="${peli.title}">
                    <div class="info-tarjeta">
                        <h3 class="titulo-tarjeta">${peli.title}</h3>
                        <button class="btn-vermas-tarjeta" data-movie-id="${peli.id}"><span>▶</span> Ver Mas</button>
                    </div>
                `;
               
                // Añadir event listener al botón "Ver Mas"
                tarjetas[i].querySelector('.btn-vermas-tarjeta').addEventListener('click', () => {
                    window.location.href = `pages/pelicula/pelicula.php?id=${peli.id}`;
                });
            });
        });
}

document.addEventListener('DOMContentLoaded', cargarRecomendados);

//Abajo de Recomendados - Grid de películas para pantallas grandes
cargarPeliculasDesdeBD()
    .then(peliculas => {
        const grid_peliculas = document.querySelector('.grid-peliculas-size');
        grid_peliculas.innerHTML = '';
        peliculas.slice(0, 8).forEach(peli => {
            const card = document.createElement('div');
            card.className = 'pelicula-card';
            card.dataset.movieId = peli.id; // Guardar el ID de la película

            const img = document.createElement('img');
            img.src = obtenerUrlImagen(peli);
            img.alt = peli.title;

            const nombre = document.createElement('div');
            nombre.className = 'nombre-pelicula';
            nombre.textContent = peli.title;

            card.appendChild(img);
            card.appendChild(nombre);
            grid_peliculas.appendChild(card);
           
            // Añadir event listener a la tarjeta de película
            card.addEventListener('click', () => {
                window.location.href = `pages/pelicula/pelicula.php?id=${peli.id}`;
            });
        });
    });