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

    function clearErrorForField(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        field.classList.remove('form-nota__input--error');

        const fieldWrapper = field.closest('.form-nota__grupo') || field.closest('.form-nota__fuente-item');
        if (fieldWrapper) {
            const errorSpan = fieldWrapper.querySelector('.form-nota__error');
            if (errorSpan) {
                errorSpan.remove();
            }
        }
    }

    function setErrorForField(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        field.classList.add('form-nota__input--error');

        const fieldWrapper = field.closest('.form-nota__grupo') || field.closest('.form-nota__fuente-item');
        if (fieldWrapper) {
            let errorSpan = fieldWrapper.querySelector('.form-nota__error');

            if (!errorSpan) {
                errorSpan = document.createElement('span');
                errorSpan.className = 'form-nota__error';
                errorSpan.setAttribute('role', 'alert');
                field.parentNode.insertBefore(errorSpan, field.nextSibling);
            }

            errorSpan.textContent = message;
        }
    }

    function validarFuentes() {
        const items = getFuenteItems();
        let hasValidUrl = false;
        const errors = [];

        items.forEach((item, index) => {
            const input = item.querySelector('.form-nota__input');
            if (!input) return;

            const url = input.value.trim();
            const fieldId = input.id;

            if (url === '') {
                clearErrorForField(fieldId);
                return;
            }

            try {
                new URL(url);
                hasValidUrl = true;
                clearErrorForField(fieldId);
            } catch (error) {
                setErrorForField(fieldId, 'Por favor ingresá una URL válida (ej: https://ejemplo.com)');
                errors.push({ field: input, index });
            }
        });

        if (!hasValidUrl) {
            const firstInput = document.getElementById('fuente-1');
            setErrorForField('fuente-1', 'Necesitás al menos una fuente válida');
            errors.push({ field: firstInput, index: 0 });
        }

        return errors.length === 0;
    }

    function validarSeccion() {
        const seccionSelect = document.getElementById('seccion');
        if (!seccionSelect) return true;

        const value = seccionSelect.value.trim();

        if (value === '') {
            setErrorForField('seccion', 'Seleccioná una sección del diario');
            return false;
        }

        clearErrorForField('seccion');
        return true;
    }

    function validarPalabrasClaveYInstrucciones() {
        const palabrasClaveInput = document.getElementById('palabras-clave');
        const instruccionesTextarea = document.getElementById('instrucciones-extra');
        let isValid = true;

        if (palabrasClaveInput) {
            const value = palabrasClaveInput.value.trim();
            if (value.length > 500) {
                setErrorForField('palabras-clave', 'Las palabras clave no pueden exceder 500 caracteres');
                isValid = false;
            } else {
                clearErrorForField('palabras-clave');
            }
        }

        if (instruccionesTextarea) {
            const value = instruccionesTextarea.value.trim();
            if (value.length > 1000) {
                setErrorForField('instrucciones-extra', 'Las instrucciones no pueden exceder 1000 caracteres');
                isValid = false;
            } else {
                clearErrorForField('instrucciones-extra');
            }
        }

        return isValid;
    }

    function validarFormulario() {
        const validFuentes = validarFuentes();
        const validSeccion = validarSeccion();
        const validTextos = validarPalabrasClaveYInstrucciones();

        return validFuentes && validSeccion && validTextos;
    }

    function findFirstErrorField() {
        const inputsWithError = form.querySelectorAll('.form-nota__input--error');
        if (inputsWithError.length > 0) {
            return inputsWithError[0];
        }
        return null;
    }

    form.addEventListener('input', (event) => {
        const target = event.target;

        if (target.id === 'fuente-1' || target.id.startsWith('fuente-')) {
            clearErrorForField(target.id);
        } else if (target.id === 'seccion') {
            clearErrorForField(target.id);
        } else if (target.id === 'palabras-clave') {
            clearErrorForField(target.id);
        } else if (target.id === 'instrucciones-extra') {
            clearErrorForField(target.id);
        }
    });

    form.addEventListener('change', (event) => {
        const target = event.target;

        if (target.id === 'seccion') {
            clearErrorForField(target.id);
        }
    });

    form.addEventListener('submit', (event) => {
        const isValid = validarFormulario();

        if (!isValid) {
            event.preventDefault();

            const firstErrorField = findFirstErrorField();
            if (firstErrorField) {
                firstErrorField.focus();
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Generando nota...';
    });

    renumerarFuentes();

    // Expuestos para cumplir el contrato solicitado del formulario.
    window.agregarFuente = agregarFuente;
    window.eliminarFuente = eliminarFuente;
})();

