const imgBase = 'https://image.tmdb.org/t/p/w500';

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const movieId = urlParams.get('id');

    const videoMessageElement = document.querySelector('.video-message');
    // Ocultar el mensaje de video por defecto
    if (videoMessageElement) {
        videoMessageElement.style.display = 'none';
        videoMessageElement.style.color = 'white'; 
        videoMessageElement.style.textAlign = 'center';
        videoMessageElement.style.marginTop = '20px';
    }

    if (movieId) {
        fetch(`../../php/obtener_pelicula.php?id=${movieId}`)
            .then(res => res.json())
            .then(peli => {
                if (!peli || peli.error) {
                    throw new Error(peli && peli.error ? peli.error : 'Película no encontrada');
                }
                
                // Unificar renderizado para ambos tipos de película y asegurar contenido por defecto
                // --- ESTRELLAS ---
                const starsContainer = document.querySelector('.movie-rating');
                let rating = 0;
                let valoracionPromedio = Number(peli.valoracion_promedio) || 0;
                if (valoracionPromedio > 0) {
                    rating = Math.round(valoracionPromedio);
                } else if (peli.vote_average && peli.vote_average > 0) {
                    rating = Math.round(peli.vote_average / 2);
                }
                if (starsContainer) {
                    starsContainer.innerHTML = '';
                    for (let i = 0; i < 5; i++) {
                        const star = document.createElement('span');
                        star.className = 'star';
                        star.innerHTML = i < rating ? '&#9733;' : '&#9734;';
                        star.style.color = i < rating ? '#ffb300' : '#ccc';
                        starsContainer.appendChild(star);
                    }
                    // Info de valoración
                    const ratingInfo = document.createElement('div');
                    ratingInfo.style.cssText = `margin-top: 5px; font-size: 14px; color: ${valoracionPromedio > 0 ? '#ffb300' : '#ccc'}; font-weight: bold;`;
                    ratingInfo.textContent = valoracionPromedio > 0 ? `${valoracionPromedio.toFixed(1)}/5 (${peli.total_valoraciones || 0} valoraciones)` : 'Sin valoraciones';
                    starsContainer.appendChild(ratingInfo);
                }
                // --- METADATOS ---
                document.querySelector('.movie-title').textContent = peli.title || 'Sin título';
                document.querySelector('.movie-year').textContent = peli.release_date ? peli.release_date.split('-')[0] : 'Año desconocido';
                document.querySelector('.movie-genre').textContent = (peli.genres && peli.genres.length > 0) ? peli.genres.map(g => g.name).join(', ') : 'Género desconocido';
                if (peli.runtime) {
                    const hours = Math.floor(peli.runtime / 60);
                    const minutes = peli.runtime % 60;
                    document.querySelector('.movie-duration').textContent = `${hours}H ${minutes}M`;
                } else {
                    document.querySelector('.movie-duration').textContent = 'Duración desconocida';
                }
                // --- SINOPSIS ---
                document.querySelector('.movie-synopsis').textContent = peli.overview || 'Sinopsis no disponible';
                // --- IMAGEN ---
                let posterSrc = '../../img/placeholder.png';
                if (peli.poster_path) {
                    if (peli.tipo === 'manual') {
                        posterSrc = peli.poster_path;
                    } else {
                        posterSrc = imgBase + peli.poster_path;
                    }
                }
                document.querySelector('.movie-poster').src = posterSrc;
                document.querySelector('.movie-poster').alt = peli.title || 'Sin título';
                // --- BOTONES ---
                const btnReseñas = document.getElementById('btn-reseñas');
                if (btnReseñas) {
                    btnReseñas.onclick = () => {
                        window.location.href = `../reseñas.php?id_pelicula=${peli.id}`;
                    };
                }
                const btnComprarEntradas = document.getElementById('btn-comprar-entradas');
                if (btnComprarEntradas) {
                    btnComprarEntradas.onclick = () => {
                        window.location.href = `../compra-entradas.php?id=${peli.id}`;
                    };
                }
                // --- TRAILER Y ACTORES ---
                if (peli.tipo === 'api' && peli.tmdb_id) {
                    cargarDetallesApiTMDB(peli.tmdb_id);
                } else {
                    cargarDetallesManuales(peli);
                }
            })
            .catch(error => {
                console.error('Error al obtener los detalles de la película:', error);
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

function cargarDetallesManuales(peli) {
    const videoPlaceholder = document.querySelector('.video-player-placeholder');
    const playIcon = document.querySelector('.video-player-placeholder .play-icon');
    const trailerThumbnail = document.querySelector('.trailer-thumbnail');
    const videoMessageElement = document.querySelector('.video-message');
    const actorsListElement = document.querySelector('.movie-actors-list');

    // Manejar trailer
    if (peli.trailer_url && peli.trailer_url.trim() !== '') {
        // Extraer el ID del video de YouTube
        const youtubeId = extraerYoutubeId(peli.trailer_url);
        if (youtubeId) {
            if (trailerThumbnail) {
                trailerThumbnail.src = `https://img.youtube.com/vi/${youtubeId}/hqdefault.jpg`;
                trailerThumbnail.style.display = 'block';
            }
            if (playIcon) {
                playIcon.style.display = 'block';
            }
            if (videoPlaceholder) {
                videoPlaceholder.style.cursor = 'pointer';
                videoPlaceholder.addEventListener('click', () => {
                    window.open(peli.trailer_url, '_blank');
                });
            }
            if (videoMessageElement) {
                videoMessageElement.style.display = 'none';
            }
        } else {
            if (videoMessageElement) {
                videoMessageElement.textContent = 'URL del trailer no válida.';
                videoMessageElement.style.display = 'block';
            }
            if (trailerThumbnail) trailerThumbnail.style.display = 'none';
            if (playIcon) playIcon.style.display = 'none';
        }
    } else {
        if (videoMessageElement) {
            videoMessageElement.textContent = 'No hay trailer disponible para esta película.';
            videoMessageElement.style.display = 'block';
        }
        if (trailerThumbnail) trailerThumbnail.style.display = 'none';
        if (playIcon) playIcon.style.display = 'none';
    }

    // Manejar actores
    if (actorsListElement) {
        if (peli.actores && peli.actores.trim() !== '') {
            actorsListElement.textContent = peli.actores;
        } else {
            actorsListElement.textContent = 'Información de actores no disponible.';
        }
    }
}

function extraerYoutubeId(url) {
    const regex = /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/;
    const match = url.match(regex);
    return match ? match[1] : null;
}

function cargarDetallesApiTMDB(tmdb_id) {
    const apiKey = 'e6822a7ed386f7102b6a857ea5e3c17f';
    // Trailers
    fetch(`https://api.themoviedb.org/3/movie/${tmdb_id}/videos?api_key=${apiKey}&language=es-ES`)
        .then(res => res.json())
        .then(videoData => {
            const videoPlaceholder = document.querySelector('.video-player-placeholder');
            const playIcon = document.querySelector('.video-player-placeholder .play-icon');
            const trailerThumbnail = document.querySelector('.trailer-thumbnail');
            const videoMessageElement = document.querySelector('.video-message');

            if (videoData.results && videoData.results.length > 0) {
                const trailer = videoData.results.find(vid => vid.site === 'YouTube' && vid.type === 'Trailer' && vid.iso_639_1 === 'es') ||
                                videoData.results.find(vid => vid.site === 'YouTube' && vid.type === 'Trailer') ||
                                videoData.results.find(vid => vid.site === 'YouTube');
                if (trailer) {
                    const youtubeUrl = `https://www.youtube.com/watch?v=${trailer.key}`;
                    if (trailerThumbnail) {
                        trailerThumbnail.src = `https://img.youtube.com/vi/${trailer.key}/hqdefault.jpg`;
                        trailerThumbnail.style.display = 'block';
                    }
                    if (playIcon) {
                        playIcon.style.display = 'block';
                    }
                    if (videoPlaceholder) {
                        videoPlaceholder.style.cursor = 'pointer';
                        videoPlaceholder.addEventListener('click', () => {
                            window.open(youtubeUrl, '_blank');
                        });
                    }
                    if (videoMessageElement) {
                        videoMessageElement.style.display = 'none';
                    }
                } else {
                    if (videoMessageElement) {
                        videoMessageElement.textContent = 'No se encontraron trailers de YouTube para esta película.';
                        videoMessageElement.style.display = 'block';
                    }
                    if (trailerThumbnail) trailerThumbnail.style.display = 'none';
                    if (playIcon) playIcon.style.display = 'none';
                }
            } else {
                if (videoMessageElement) {
                    videoMessageElement.textContent = 'No se encontraron videos para esta película.';
                    videoMessageElement.style.display = 'block';
                }
                if (trailerThumbnail) trailerThumbnail.style.display = 'none';
                if (playIcon) playIcon.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error al cargar trailer:', error);
            const videoMessageElement = document.querySelector('.video-message');
            if (videoMessageElement) {
                videoMessageElement.textContent = 'Error al cargar el trailer.';
                videoMessageElement.style.display = 'block';
            }
        });
    
    // Actores
    fetch(`https://api.themoviedb.org/3/movie/${tmdb_id}/credits?api_key=${apiKey}&language=es-ES`)
        .then(res => res.json())
        .then(credits => {
            const actorsListElement = document.querySelector('.movie-actors-list');
            if (actorsListElement && credits && credits.cast && credits.cast.length > 0) {
                const principalActors = credits.cast.slice(0, 10).map(actor => actor.name).join(', ');
                actorsListElement.textContent = principalActors;
            } else if (actorsListElement) {
                actorsListElement.textContent = 'Información de actores no disponible.';
            }
        })
        .catch(error => {
            console.error('Error al cargar actores:', error);
            const actorsListElement = document.querySelector('.movie-actors-list');
            if (actorsListElement) {
                actorsListElement.textContent = 'Error al cargar información de actores.';
            }
        });
} 