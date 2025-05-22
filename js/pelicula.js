const apiKey = 'e6822a7ed386f7102b6a857ea5e3c17f';
const imgBase = 'https://image.tmdb.org/t/p/w500';

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const movieId = urlParams.get('id');

    const videoMessageElement = document.querySelector('.video-message');
    // Ocultar el mensaje de video por defecto
    if (videoMessageElement) {
        videoMessageElement.style.display = 'none';
        videoMessageElement.style.color = 'white'; // Asegurar que el texto sea visible si se muestra
        videoMessageElement.style.textAlign = 'center';
        videoMessageElement.style.marginTop = '20px';
    }

    if (movieId) {
        fetch(`https://api.themoviedb.org/3/movie/${movieId}?api_key=${apiKey}&language=es-ES`)
            .then(res => res.json())
            .then(peli => {
                document.querySelector('.movie-poster').src = imgBase + peli.poster_path;
                document.querySelector('.movie-poster').alt = peli.title;
                document.querySelector('.movie-title').textContent = peli.title;
                document.querySelector('.movie-year').textContent = peli.release_date.split('-')[0];
                document.querySelector('.movie-genre').textContent = peli.genres.map(genre => genre.name).join(', ');
                
                // Convertir la duración de minutos a formato H M
                const hours = Math.floor(peli.runtime / 60);
                const minutes = peli.runtime % 60;
                document.querySelector('.movie-duration').textContent = `${hours}H ${minutes}M`;
                
                document.querySelector('.movie-synopsis').textContent = peli.overview;
                
                // Actualizar las estrellas de calificación (ejemplo básico)
                const rating = Math.round(peli.vote_average / 2); // Escala de 0 a 5
                const starsContainer = document.querySelector('.movie-rating');
                starsContainer.innerHTML = '';
                for (let i = 0; i < 5; i++) {
                    const star = document.createElement('span');
                    star.className = 'star';
                    star.innerHTML = i < rating ? '&#9733;' : '&#9734;';
                    starsContainer.appendChild(star);
                }

            })
            .then(() => {
                // Obtener los videos (trailers) de la película
                return fetch(`https://api.themoviedb.org/3/movie/${movieId}/videos?api_key=${apiKey}&language=es-ES`);
            })
            .then(res => res.json())
            .then(videoData => {
                const videoPlaceholder = document.querySelector('.video-player-placeholder');
                const playIcon = document.querySelector('.video-player-placeholder .play-icon');
                const trailerThumbnail = document.querySelector('.trailer-thumbnail');

                if (videoData.results && videoData.results.length > 0) {
                    // Buscar un trailer de YouTube en español si es posible, si no, el primero disponible
                    const trailer = videoData.results.find(vid => vid.site === 'YouTube' && vid.type === 'Trailer' && vid.iso_639_1 === 'es') ||
                                    videoData.results.find(vid => vid.site === 'YouTube' && vid.type === 'Trailer') ||
                                    videoData.results.find(vid => vid.site === 'YouTube');

                    if (trailer) {
                        const youtubeUrl = `https://www.youtube.com/watch?v=${trailer.key}`;

                        // Cargar la miniatura del trailer de YouTube
                        if (trailerThumbnail) {
                           trailerThumbnail.src = `https://img.youtube.com/vi/${trailer.key}/hqdefault.jpg`; // O sddefault.jpg, mqdefault.jpg, etc.
                           trailerThumbnail.style.display = 'block'; // Asegurarse de que la miniatura se muestre
                        }
                        // Asegurarse de que el ícono de play se muestre sobre la miniatura
                        if(playIcon) {
                           playIcon.style.display = 'block'; // O el display original del ícono
                        }

                        // Hacer que el placeholder sea clickable
                        if(videoPlaceholder) {
                           videoPlaceholder.style.cursor = 'pointer';
                           videoPlaceholder.addEventListener('click', () => {
                               window.open(youtubeUrl, '_blank'); // Abrir en una nueva pestaña
                           });
                        }

                        // Ocultar el mensaje si se encontró un trailer
                        if (videoMessageElement) {
                            videoMessageElement.style.display = 'none';
                        }

                    } else {
                        // No se encontraron trailers específicos, pero puede haber otros videos
                        document.querySelector('.video-message').textContent = 'No se encontraron trailers de YouTube para esta película.';
                        // Mostrar el mensaje si no hay trailer
                        if (videoMessageElement) {
                            videoMessageElement.style.display = 'block'; // O 'flex', dependiendo del display original
                        }
                        // Ocultar miniatura e ícono de play si no hay trailer
                        if (trailerThumbnail) trailerThumbnail.style.display = 'none';
                        if (playIcon) playIcon.style.display = 'none';

                    }
                } else {
                    // No se encontraron videos en absoluto
                    document.querySelector('.video-message').textContent = 'No se encontraron videos para esta película.';
                    // Mostrar el mensaje si no hay videos
                    if (videoMessageElement) {
                        videoMessageElement.style.display = 'block'; // O 'flex'
                    }
                    // Ocultar miniatura e ícono de play si no hay videos
                    if (trailerThumbnail) trailerThumbnail.style.display = 'none';
                    if (playIcon) playIcon.style.display = 'none';
                }
            })
            .then(() => {
                // Obtener los créditos (actores) de la película
                return fetch(`https://api.themoviedb.org/3/movie/${movieId}/credits?api_key=${apiKey}&language=es-ES`);
            })
            .then(res => res.json())
            .then(credits => {
                const actorsListElement = document.querySelector('.movie-actors-list');
                if (actorsListElement && credits && credits.cast && credits.cast.length > 0) {
                    // Limitar a, por ejemplo, los primeros 10 actores principales
                    const principalActors = credits.cast.slice(0, 10).map(actor => actor.name).join(', ');
                    actorsListElement.textContent = principalActors;
                } else if (actorsListElement) {
                     actorsListElement.textContent = 'Información de actores no disponible.';
                }
            })
            .catch(error => {
                console.error('Error al obtener los detalles de la película:', error);
                 // Mostrar un mensaje de error en la página si fallan las llamadas a la API
                if (videoMessageElement) {
                    videoMessageElement.textContent = 'Error al cargar la información de la película.';
                    videoMessageElement.style.display = 'block';
                }
            });
    } else {
        console.error('No se proporcionó un ID de película en la URL.');
        // Mostrar un mensaje de error en la página si no hay ID
        if (videoMessageElement) {
            videoMessageElement.textContent = 'No se proporcionó un ID de película.';
            videoMessageElement.style.display = 'block';
        }
    }
}); 