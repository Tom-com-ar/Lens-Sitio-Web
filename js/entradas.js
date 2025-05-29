const filas = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
const columnas = 12;
const grid = document.querySelector('.butacas-grid');
const textoSeleccionadas = document.querySelector('.butacas-texto');

// Simulamos algunas butacas ocupadas
const butacasOcupadas = [];

function crearButacas() {
    for (let i = 0; i < filas.length; i++) {
        for (let j = 1; j <= columnas; j++) {
            const butaca = document.createElement('div');
            butaca.classList.add('butaca');

            const id = `${filas[i]}${j}`;
            butaca.dataset.id = id;

            if (butacasOcupadas.includes(id)) {
                butaca.classList.add('ocupado');
            } else {
                butaca.classList.add('disponible');
                butaca.addEventListener('click', () => seleccionarButaca(butaca));
            }

            grid.appendChild(butaca);
        }
    }
}

function seleccionarButaca(butaca) {
    butaca.classList.toggle('seleccionado');

    // Actualizar texto
    const seleccionadas = document.querySelectorAll('.butaca.seleccionado');
    const ids = Array.from(seleccionadas).map(b => b.dataset.id);
    textoSeleccionadas.textContent = ids.join(' - ') || 'Ninguna';
}

// Ejecutar
crearButacas();
