<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Redacta — Nueva Nota';

require_once __DIR__ . '/app/layout/header.php';
?>

<section aria-labelledby="nueva-nota-heading">
    <h1 class="login__title" id="nueva-nota-heading">Nueva nota</h1>

    <form
        class="form-nota"
        action="/procesar-nota.php"
        method="POST"
        id="form-nueva-nota"
        novalidate
        role="form"
        aria-label="Formulario de generación de nota periodística"
    >
        <div class="form-nota__grupo">
            <div class="form-nota__fuentes" id="fuentes-container">
                <div class="form-nota__fuente-item">
                    <label class="form-nota__label" for="fuente-1">Fuente 1</label>
                    <input
                        class="form-nota__input"
                        type="url"
                        id="fuente-1"
                        name="fuentes[]"
                        placeholder="https://..."
                        required
                        aria-label="URL de la fuente 1"
                    >
                    <button
                        class="form-nota__btn-eliminar"
                        type="button"
                        aria-label="Eliminar fuente 1"
                        hidden
                    >
                        Eliminar
                    </button>
                </div>
            </div>
            <button class="form-nota__btn-agregar" id="btn-agregar-fuente" type="button">
                Agregar fuente
            </button>
        </div>

        <div class="form-nota__grupo">
            <label class="form-nota__label" for="titular">Titular tentativo (opcional)</label>
            <input
                class="form-nota__input"
                type="text"
                id="titular"
                name="titular"
                placeholder="Ej: El gobierno anunció nuevas medidas económicas"
                maxlength="255"
            >
        </div>

        <div class="form-nota__grupo">
            <label class="form-nota__label" for="seccion">Sección del diario</label>
            <select
                class="form-nota__select"
                id="seccion"
                name="seccion"
                required
                aria-required="true"
            >
                <option value="" disabled selected>Seleccioná una sección</option>
                <option value="politica">Política</option>
                <option value="economia">Economía</option>
                <option value="deportes">Deportes</option>
                <option value="cultura">Cultura</option>
                <option value="tecnologia">Tecnología</option>
                <option value="sociedad">Sociedad</option>
            </select>
        </div>

        <fieldset class="form-nota__radio-grupo" role="radiogroup" aria-required="true">
            <legend class="form-nota__label">Extensión de la nota</legend>
            <label>
                <input type="radio" name="extension" value="corta" required>
                Corta (~300 palabras)
            </label>
            <label>
                <input type="radio" name="extension" value="media" checked>
                Media (~600 palabras)
            </label>
            <label>
                <input type="radio" name="extension" value="larga">
                Larga (~1000 palabras)
            </label>
        </fieldset>

        <div class="form-nota__grupo">
            <label class="form-nota__label" for="palabras-clave">Palabras clave SEO (opcional)</label>
            <input
                class="form-nota__input"
                type="text"
                id="palabras-clave"
                name="palabras_clave"
                placeholder="Ej: inflación, economía argentina, dólar"
                aria-describedby="palabras-clave-hint"
            >
            <p class="form-nota__hint" id="palabras-clave-hint">
                Separadas por coma. Estas palabras guiarán el enfoque SEO de la nota.
            </p>
        </div>

        <div class="form-nota__grupo">
            <label class="form-nota__label" for="instrucciones-extra">Instrucciones adicionales (opcional)</label>
            <textarea
                class="form-nota__textarea"
                id="instrucciones-extra"
                name="instrucciones_extra"
                placeholder="Ej: Enfocá el ángulo desde el impacto en las pymes"
                rows="4"
                maxlength="1000"
            ></textarea>
        </div>

        <button
            class="form-nota__btn-submit"
            id="btn-generar-nota"
            type="submit"
            aria-label="Enviar formulario para generar la nota"
        >
            Generar nota
        </button>
    </form>
</section>

<script src="/js/form-nueva-nota.js"></script>
<?php require_once __DIR__ . '/app/layout/footer.php'; ?>


