<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$pageTitle = 'Redacta — Iniciar sesión';
$errorMessage = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailValue = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

    if ($auth->login($emailValue, $password)) {
        header('Location: /index.php');
        exit;
    }

    $errorMessage = 'No fue posible iniciar sesión. Verificá tu correo y contraseña.';
}

require_once __DIR__ . '/app/layout/header.php';
?>
<section class="login" aria-labelledby="login-heading">
    <h1 class="login__title" id="login-heading">Iniciar sesión</h1>

    <?php if ($errorMessage !== '') : ?>
        <div class="login-form__error" id="login-error" role="alert" aria-live="polite">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form class="login-form" method="post" action="/login.php" role="form" aria-label="Formulario de inicio de sesión">
        <div class="login-form__field">
            <label class="login-form__label" for="email">Correo electrónico</label>
            <input
                class="login-form__input"
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>"
                required
                aria-describedby="email-help<?= $errorMessage !== '' ? ' login-error' : '' ?>"
                autocomplete="email"
            >
            <p class="login-form__hint" id="email-help">Ingresá el correo asociado a tu cuenta.</p>
        </div>

        <div class="login-form__field">
            <label class="login-form__label" for="password">Contraseña</label>
            <input
                class="login-form__input"
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
            >
        </div>

        <button class="login-form__button" type="submit">Ingresar</button>
    </form>
</section>
<?php require_once __DIR__ . '/app/layout/footer.php'; ?>
