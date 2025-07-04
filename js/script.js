const apiKey = 'e6822a7ed386f7102b6a857ea5e3c17f'; // Reemplaza con tu clave real
const url = `https://api.themoviedb.org/3/movie/popular?api_key=${apiKey}&language=es-ES&page=1`;
const imgBase = 'https://image.tmdb.org/t/p/w500';

//De 768px hasta pantallas mas pequeñas

// Scrool horizontal hasta 768px
fetch(url)
    .then(res => res.json())
    .then(data => {
        const carrusel_populares = document.querySelector('.grid-populares');
        carrusel_populares.innerHTML = '';
        data.results.slice(0, 8).forEach(peli => {
            const img = document.createElement('img');
            img.src = imgBase + peli.poster_path;
            img.alt = peli.title;
           
            const link = document.createElement('a');
            link.href = `pages/pelicula/pelicula.php?id=${peli.id}`;
            link.appendChild(img);
            carrusel_populares.appendChild(link);
        });
    });

//Scrool vertical hasta 768px 
fetch(url)
    .then(res => res.json())
    .then(data => {
        const grid_peliculas = document.querySelector('.grid-peliculas');
        grid_peliculas.innerHTML = '';
        data.results.slice(0, 20).forEach(peli => {
            const img = document.createElement('img');
            img.src = imgBase + peli.poster_path;
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
    fetch(`https://api.themoviedb.org/3/movie/popular?api_key=${apiKey}&language=es-ES&page=1`)
        .then(res => res.json())
        .then(data => {
            if (!data.results || data.results.length < 2) return;
            // Elegir 2 índices aleatorios distintos
            let idx1 = Math.floor(Math.random() * data.results.length);
            let idx2;
            do {
                idx2 = Math.floor(Math.random() * data.results.length);
            } while (idx2 === idx1);

            const pelis = [data.results[idx1], data.results[idx2]];
            const tarjetas = [document.querySelector('.tarjeta-izq'), document.querySelector('.tarjeta-der')];

            pelis.forEach((peli, i) => {
                tarjetas[i].innerHTML = `
                    <img src="${imgBase + peli.poster_path}" alt="${peli.title}">
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

//Abajo de Recomendados
fetch(url)
    .then(res => res.json())
    .then(data => {
        const grid_peliculas = document.querySelector('.grid-peliculas-size');
        grid_peliculas.innerHTML = '';
        data.results.slice(0, 8).forEach(peli => {
            const card = document.createElement('div');
            card.className = 'pelicula-card';
            card.dataset.movieId = peli.id; // Guardar el ID de la película

            const img = document.createElement('img');
            img.src = imgBase + peli.poster_path;
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