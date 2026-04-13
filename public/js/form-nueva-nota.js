(() => {
    'use strict';

    const MAX_FUENTES = 5;

    const form = document.getElementById('form-nueva-nota');
    const fuentesContainer = document.getElementById('fuentes-container');
    const addButton = document.getElementById('btn-agregar-fuente');
    const submitButton = document.getElementById('btn-generar-nota');

    if (!form || !fuentesContainer || !addButton || !submitButton) {
        return;
    }

    const getFuenteItems = () => Array.from(fuentesContainer.querySelectorAll('.form-nota__fuente-item'));

    const renumerarFuentes = () => {
        const items = getFuenteItems();

        items.forEach((item, index) => {
            const number = index + 1;
            const input = item.querySelector('.form-nota__input');
            const label = item.querySelector('.form-nota__label');
            const deleteButton = item.querySelector('.form-nota__btn-eliminar');

            if (input) {
                input.id = `fuente-${number}`;
                input.setAttribute('aria-label', `URL de la fuente ${number}`);
                input.required = number === 1;
            }

            if (label) {
                label.setAttribute('for', `fuente-${number}`);
                label.textContent = `Fuente ${number}`;
            }

            if (deleteButton) {
                deleteButton.setAttribute('aria-label', `Eliminar fuente ${number}`);
                const hideDeleteButton = items.length === 1;
                deleteButton.hidden = hideDeleteButton;
                deleteButton.disabled = hideDeleteButton;
            }
        });

        addButton.disabled = items.length >= MAX_FUENTES;
    };

    function agregarFuente() {
        const items = getFuenteItems();

        if (items.length >= MAX_FUENTES) {
            return;
        }

        const firstItem = items[0];
        const newItem = firstItem.cloneNode(true);

        const input = newItem.querySelector('.form-nota__input');
        if (input) {
            input.value = '';
            input.required = false;
        }

        let label = newItem.querySelector('.form-nota__label');
        if (!label) {
            label = document.createElement('label');
            label.className = 'form-nota__label';
            newItem.insertBefore(label, newItem.firstChild);
        }

        let deleteButton = newItem.querySelector('.form-nota__btn-eliminar');
        if (!deleteButton) {
            deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'form-nota__btn-eliminar';
            deleteButton.textContent = 'Eliminar';
            newItem.appendChild(deleteButton);
        }

        deleteButton.hidden = false;
        deleteButton.disabled = false;

        fuentesContainer.appendChild(newItem);
        renumerarFuentes();
    }

    function eliminarFuente(button) {
        const items = getFuenteItems();

        if (items.length <= 1) {
            return;
        }

        const item = button.closest('.form-nota__fuente-item');
        if (!item) {
            return;
        }

        item.remove();
        renumerarFuentes();
    }

    addButton.addEventListener('click', () => {
        agregarFuente();
    });

    fuentesContainer.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLButtonElement)) {
            return;
        }

        if (!target.classList.contains('form-nota__btn-eliminar')) {
            return;
        }

        eliminarFuente(target);
    });

    form.addEventListener('submit', () => {
        submitButton.disabled = true;
        submitButton.textContent = 'Generando nota...';
    });

    renumerarFuentes();

    // Expuestos para cumplir el contrato solicitado del formulario.
    window.agregarFuente = agregarFuente;
    window.eliminarFuente = eliminarFuente;
})();

