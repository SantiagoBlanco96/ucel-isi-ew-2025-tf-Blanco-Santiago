<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/NotaService.php';
require_once __DIR__ . '/app/helpers.php';

$auth = new Auth();
$auth->requireLogin();

$notaIdParam = $_GET['id'] ?? null;

if (!is_string($notaIdParam) || !ctype_digit($notaIdParam)) {
    header('Location: /index.php');
    exit;
}

$notaId = (int) $notaIdParam;
$usuarioId = (int) ($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0);

if ($usuarioId <= 0) {
    header('Location: /index.php');
    exit;
}

$notaService = new NotaService();
$nota = $notaService->obtenerNota($notaId, $usuarioId);

if ($nota === null) {
    header('Location: /index.php');
    exit;
}

$pageTitle = 'Redacta — Resultado';
$errorGeneracion = $_SESSION['error_generacion'] ?? null;
unset($_SESSION['error_generacion']);

$titular = trim((string) ($nota['titulo'] ?? ''));
$titularMostrado = $titular !== '' ? $titular : 'Sin titular';
$contenidoMarkdown = (string) ($nota['contenido_generado'] ?? '');
$contenidoHtml = markdownToHtml($contenidoMarkdown);
$fechaCreacion = isset($nota['created_at']) ? date('d/m/Y H:i', strtotime((string) $nota['created_at'])) : '';

$seccionMap = [
    'politica' => 'Política',
    'economia' => 'Economía',
    'deportes' => 'Deportes',
    'cultura' => 'Cultura',
    'tecnologia' => 'Tecnología',
    'sociedad' => 'Sociedad',
];

$extensionMap = [
    'corta' => 'Corta',
    'media' => 'Media',
    'larga' => 'Larga',
];

$seccionLabel = $seccionMap[$nota['seccion']] ?? (string) $nota['seccion'];
$extensionLabel = $extensionMap[$nota['extension']] ?? (string) $nota['extension'];

require_once __DIR__ . '/app/layout/header.php';
?>
<section class="resultado" aria-labelledby="resultado-titulo" data-nota-id="<?= (int) $nota['id'] ?>">
    <header class="resultado__header">
        <h1 id="resultado-titulo"><?= htmlspecialchars($titularMostrado, ENT_QUOTES, 'UTF-8') ?></h1>

        <div class="resultado__meta" aria-label="Metadatos de la nota">
            <span class="resultado__meta-badge"><?= htmlspecialchars($seccionLabel, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="resultado__meta-badge"><?= htmlspecialchars($extensionLabel, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="resultado__meta-badge"><?= htmlspecialchars($fechaCreacion, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </header>

    <?php if (is_string($errorGeneracion) && $errorGeneracion !== '') : ?>
        <div class="resultado__error" role="alert">
            <?= htmlspecialchars($errorGeneracion, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <article class="resultado__contenido" aria-label="Contenido generado de la nota">
        <?= $contenidoHtml ?>
    </article>

    <pre class="sr-only" id="resultado-markdown" aria-hidden="true"><?= htmlspecialchars($contenidoMarkdown, ENT_QUOTES, 'UTF-8') ?></pre>

    <div class="resultado__acciones" role="group" aria-label="Acciones sobre la nota generada">
        <button type="button" class="resultado__btn resultado__btn--copiar" id="btn-copiar-nota">
            Copiar nota
        </button>
        <button type="button" class="resultado__btn resultado__btn--descargar" id="btn-descargar-nota">
            Descargar .txt
        </button>
        <a class="resultado__btn resultado__btn--nueva" href="/nueva-nota.php">Nueva nota</a>
        <a class="resultado__btn resultado__btn--historial" href="/historial.php">Ver historial</a>
    </div>
</section>
<script src="/js/resultado.js"></script>
<?php require_once __DIR__ . '/app/layout/footer.php'; ?>
