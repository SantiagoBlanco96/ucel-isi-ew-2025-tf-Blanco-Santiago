<?php

declare(strict_types=1);

/**
 * Bootstrap compartido para todo el proyecto.
 * Carga variables de entorno, define rutas base e incluye clases globales.
 */

$projectRoot = dirname(__DIR__, 2);
$envPath = $projectRoot . '/.env';

if (!is_file($envPath)) {
    throw new RuntimeException('No se encontró el archivo .env en la raíz del proyecto.');
}

$envValues = parse_ini_file($envPath, false, INI_SCANNER_TYPED);

if ($envValues === false) {
    throw new RuntimeException('No fue posible leer el archivo .env.');
}

foreach ($envValues as $key => $value) {
    $stringValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
    putenv($key . '=' . $stringValue);
    $_ENV[$key] = $stringValue;
    $_SERVER[$key] = $stringValue;
}

if (!defined('APP_ROOT')) {
    define('APP_ROOT', $projectRoot);
}

if (!defined('APP_PUBLIC_PATH')) {
    define('APP_PUBLIC_PATH', $projectRoot . '/public');
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Redacta');
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
