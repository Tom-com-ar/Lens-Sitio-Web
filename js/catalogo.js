const apiKey = 'e6822a7ed386f7102b6a857ea5e3c17f';
const imgBase = 'https://image.tmdb.org/t/p/w500';
let pagina = 1;
let cargadas = 0;
const porPagina = 30;

function cargarPeliculas() {
    fetch(`https://api.themoviedb.org/3/movie/popular?api_key=${apiKey}&language=es-ES&page=${pagina}`)
        .then(res => res.json())
        .then(data => {
            const grid = document.querySelector('.grid-peliculas');
            let restantes = porPagina - (cargadas % porPagina);
            let pelis = data.results.slice(0, restantes);
            pelis.forEach(peli => {
                const img = document.createElement('img');
                img.src = imgBase + peli.poster_path;
                img.alt = peli.title;
                grid.appendChild(img);
            });
            cargadas += pelis.length;
            if (pelis.length < restantes && data.page < data.total_pages) {
                pagina++;
                cargarPeliculas(); // Llama recursivamente si faltan para llegar a 30
            } else {
                pagina++;
            }
        });
}

document.addEventListener('DOMContentLoaded', () => {
    cargarPeliculas();
    document.getElementById('btn-mas').addEventListener('click', cargarPeliculas);
});