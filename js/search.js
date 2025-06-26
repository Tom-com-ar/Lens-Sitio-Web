//Barras de busqueda
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('.barra-busqueda-nav input');
    const searchButton = document.querySelector('.barra-busqueda-nav .btn-buscar');

    //Pulsar Enter
    if (searchInput) {
        searchInput.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault(); 
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
                    alert('Diríjase al catálogo para realizar la búsqueda.');
                }
            }else{
                alert('Diríjase al catálogo para realizar la búsqueda.');
            }
        });
    }

    //Click en el boton de busqueda
    if (searchButton) {
        searchButton.addEventListener('click', (event) => {
             event.preventDefault(); 
        });
    }
}); 