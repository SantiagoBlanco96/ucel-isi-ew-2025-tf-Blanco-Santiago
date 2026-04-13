<?php
/**
 * Layout parcial: cabecera del sitio.
 * Renderiza el <head> completo, la apertura del <body> y el <header> de navegación.
 *
 * Variables esperadas del contexto que incluye este archivo:
 *   string $pageTitle  Título de la página actual.
 */

if (!isset($pageTitle)) {
    $pageTitle = 'Redacta';
}

$isLoggedIn  = isset($_SESSION['id']) || isset($_SESSION['user_id']);
$userName    = $isLoggedIn ? htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/css/redacta.css">
</head>
<body>
<header class="header" role="banner">
    <div class="header__brand">
        <a href="/index.php" class="header__logo" aria-label="Redacta — volver al inicio">
            <span class="header__logo-icon" aria-hidden="true">✒</span>
            Redacta
        </a>
    </div>

    <?php if ($isLoggedIn) : ?>
    <nav class="header__nav" role="navigation" aria-label="Navegación principal">
        <ul class="header__nav-list">
            <li class="header__nav-item">
                <a class="header__nav-link" href="/index.php">Inicio</a>
            </li>
            <li class="header__nav-item">
                <a class="header__nav-link header__nav-link--cta" href="/nueva-nota.php">Nueva Nota</a>
            </li>
            <li class="header__nav-item">
                <a class="header__nav-link" href="/historial.php">Historial</a>
            </li>
        </ul>
    </nav>

    <div class="header__user" aria-label="Usuario autenticado">
        <span class="header__user-name"><?= $userName ?></span>
        <a class="header__user-logout" href="/logout.php">Cerrar sesión</a>
    </div>
    <?php endif; ?>
</header>

<main class="layout">
    <div class="layout__content">

