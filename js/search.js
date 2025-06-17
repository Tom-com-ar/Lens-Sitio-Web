document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('.barra-busqueda-nav input');
    const searchButton = document.querySelector('.barra-busqueda-nav .btn-buscar');

    const performSearch = () => {
        const query = searchInput.value.trim();
        const currentPage = window.location.pathname;

        if (query) {
            // Verificar si estamos en la página del catálogo
            if (currentPage.includes('catalogo.php')) {
                // Si estamos en el catálogo, intentar llamar a la función de búsqueda del catálogo
                if (typeof window.performCatalogSearch === 'function') {
                    window.performCatalogSearch(query);
                } else {
                    console.error('La función performCatalogSearch no está definida en catalogo.js.');
                    // Aunque no debería pasar si los scripts están bien cargados, como fallback, mostrar alerta
                    alert('Diríjase al catálogo para realizar la búsqueda.');
                }
            } else {
                // Si no estamos en el catálogo, mostrar una alerta
                alert('Diríjase al catálogo para realizar la búsqueda.');
            }
        }
    };

    // Escuchar el evento 'keypress' en el input (para Enter)
    if (searchInput) {
        searchInput.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevenir el envío del formulario si lo hay
                performSearch(); // Usar la lógica existente para redirigir o alertar/buscar
            }
        });

        // Escuchar el evento 'input' para búsqueda en tiempo real en el catálogo
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            const currentPage = window.location.pathname;

            if (currentPage.includes('catalogo.php')) {
                // Si estamos en el catálogo, realizar la búsqueda en tiempo real
                 if (typeof window.performCatalogSearch === 'function') {
                    window.performCatalogSearch(query);
                } else {
                    console.error('La función performCatalogSearch no está definida para búsqueda en tiempo real.');
                }
            }
            // No hacemos nada si no estamos en el catálogo, la alerta/redirección ya se maneja con 'Enter' o el click
        });
    }

    // Escuchar el evento 'click' en el botón de búsqueda
    if (searchButton) {
        searchButton.addEventListener('click', (event) => {
             event.preventDefault(); // Prevenir el envío del formulario si lo hay
             performSearch();
        });
    }
}); 