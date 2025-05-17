const apiKey = 'e6822a7ed386f7102b6a857ea5e3c17f'; // Reemplaza con tu clave real
const url = `https://api.themoviedb.org/3/movie/popular?api_key=${apiKey}&language=es-ES&page=1`;
const imgBase = 'https://image.tmdb.org/t/p/w500';

fetch(url)
    .then(res => res.json())
    .then(data => {
        const carrusel_populares = document.querySelector('.grid-populares');
        carrusel_populares.innerHTML = '';
        data.results.slice(0, 6).forEach(peli => {
            const img = document.createElement('img');
            img.src = imgBase + peli.poster_path;
            img.alt = peli.title;
            carrusel_populares.appendChild(img);
        });
    });

// Películas más famosas (mejor valoradas)
fetch(url)
    .then(res => res.json())
    .then(data => {
        const grid_peliculas = document.querySelector('.grid-peliculas');
        grid_peliculas.innerHTML = '';
        data.results.slice(0, 20).forEach(peli => {
            const img = document.createElement('img');
            img.src = imgBase + peli.poster_path;
            img.alt = peli.title;
            grid_peliculas.appendChild(img);
        });
    });

