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
                const titleElement = document.getElementById('nombre-pelicua-reseña');
                if (titleElement) {
                    titleElement.textContent = peli.title;
                }
            })
            .catch(error => {
                console.error('Error al obtener los detalles de la película:', error);
            });
    }
});