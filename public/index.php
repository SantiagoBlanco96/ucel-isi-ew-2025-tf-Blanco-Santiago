<?php
/**
 * Página de inicio (landing interna).
 * Redirige a login si no hay sesión activa.
 * Sin lógica de negocio: toda la presentación delega en los parciales de layout.
 */

require_once __DIR__ . '/app/bootstrap.php';

$auth = new Auth();
$auth->requireLogin();

$pageTitle = 'Redacta — Inicio';
$userName  = htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8');

require_once __DIR__ . '/app/layout/header.php';
?>

<section class="welcome" aria-labelledby="welcome-heading">
    <h2 class="welcome__heading" id="welcome-heading">
        Bienvenido/a, <span class="welcome__name"><?= $userName ?></span>
    </h2>
    <p class="welcome__subtitle">
        ¿Listo para redactar tu próxima nota con inteligencia artificial?
    </p>
    <a class="btn btn--primary" href="/nueva-nota.php" role="button">
        ✒ Nueva Nota
    </a>
</section>

<?php require_once __DIR__ . '/app/layout/footer.php'; ?>

