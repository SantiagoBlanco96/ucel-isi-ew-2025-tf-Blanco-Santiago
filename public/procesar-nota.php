<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/NotaService.php';
require_once __DIR__ . '/app/N8nService.php';

error_log('POST recibido: ' . print_r($_POST, true));

$auth = new Auth();
$auth->requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /nueva-nota.php');
    exit;
}

/**
 * @param mixed $value
 */
function sanitizeInput($value): string
{
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

/**
 * @param array<string, mixed> $data
 */
function extractContenidoGenerado(array $data): ?string
{
    $keys = ['contenido', 'contenido_generado', 'texto', 'nota', 'resultado'];

    foreach ($keys as $key) {
        if (isset($data[$key]) && is_string($data[$key]) && trim($data[$key]) !== '') {
            return trim($data[$key]);
        }
    }

    return null;
}

$seccionesPermitidas = ['politica', 'economia', 'deportes', 'cultura', 'tecnologia', 'sociedad'];
$extensionesPermitidas = ['corta', 'media', 'larga'];

$errores = [];

$rawUrls = $_POST['fuentes'] ?? [];
if (!is_array($rawUrls)) {
    $rawUrls = [];
}

$urlsNormalizadas = [];
foreach ($rawUrls as $rawUrl) {
    $url = trim((string) $rawUrl);

    if ($url === '') {
        continue;
    }

    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        $errores[] = sprintf('La URL "%s" no es válida.', $url);
        continue;
    }

    $urlsNormalizadas[] = sanitizeInput($url);
}

if (count($urlsNormalizadas) < 1) {
    $errores[] = 'Debés ingresar al menos una URL válida.';
}

if (count($urlsNormalizadas) > 5) {
    $errores[] = 'Solo se permiten hasta 5 URLs fuente.';
}

error_log('Errores tras validar urls: ' . print_r($errores, true));

$seccion = trim((string) ($_POST['seccion'] ?? ''));
if (!in_array($seccion, $seccionesPermitidas, true)) {
    $errores[] = 'La sección seleccionada no es válida.';
}

error_log('Errores tras validar seccion: ' . print_r($errores, true));

$extension = trim((string) ($_POST['extension'] ?? ''));
if (!in_array($extension, $extensionesPermitidas, true)) {
    $errores[] = 'La extensión seleccionada no es válida.';
}

$titular = trim((string) ($_POST['titular'] ?? ''));
if (mb_strlen($titular) > 255) {
    $errores[] = 'El titular no puede superar los 255 caracteres.';
}

$palabrasClave = trim((string) ($_POST['palabras_clave'] ?? ''));
if (mb_strlen($palabrasClave) > 500) {
    $errores[] = 'Las palabras clave no pueden superar los 500 caracteres.';
}

$instruccionesExtra = trim((string) ($_POST['instrucciones_extra'] ?? ''));
if (mb_strlen($instruccionesExtra) > 1000) {
    $errores[] = 'Las instrucciones adicionales no pueden superar los 1000 caracteres.';
}

error_log('Errores finales: ' . print_r($errores, true));

if ($errores !== []) {
    error_log('REDIRIGIENDO por errores');
    $_SESSION['errores'] = $errores;
    header('Location: /nueva-nota.php');
    exit;
}

error_log('PASÓ VALIDACIÓN, llamando a n8n');

$usuarioId = (int) ($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0);

if ($usuarioId <= 0) {
    $_SESSION['error_generacion'] = 'No se pudo identificar al usuario autenticado.';
    header('Location: /nueva-nota.php');
    exit;
}

$datos = [
    'titular' => sanitizeInput($titular),
    'seccion' => sanitizeInput($seccion),
    'extension' => sanitizeInput($extension),
    'palabras_clave' => sanitizeInput($palabrasClave),
    'instrucciones_extra' => sanitizeInput($instruccionesExtra),
];

$notaService = new NotaService();
$n8nService = new N8nService();

try {
    $notaId = $notaService->guardarBorrador($datos, $usuarioId);
    $notaService->guardarFuentes($notaId, $urlsNormalizadas);

    $payload = [
        'nota_id' => $notaId,
        'urls' => $urlsNormalizadas,
        'titular' => $datos['titular'],
        'seccion' => $datos['seccion'],
        'extension' => $datos['extension'],
        'palabras_clave' => $datos['palabras_clave'],
        'instrucciones_extra' => $datos['instrucciones_extra'],
    ];

    $respuesta = $n8nService->enviarPedido($payload);

    if ($respuesta['success'] !== true) {
        $_SESSION['error_generacion'] = (string) ($respuesta['error'] ?? 'No se pudo generar la nota.');
        header('Location: /nueva-nota.php');
        exit;
    }

    $contenido = extractContenidoGenerado($respuesta['data']);

    if ($contenido === null) {
        $_SESSION['error_generacion'] = 'La respuesta de n8n no incluye contenido generado.';
        header('Location: /nueva-nota.php');
        exit;
    }

    $notaService->actualizarContenido($notaId, sanitizeInput($contenido));

    header('Location: /resultado.php?id=' . $notaId);
    exit;
} catch (Throwable $exception) {
    $_SESSION['error_generacion'] = 'Ocurrió un error al procesar la nota.';
    header('Location: /nueva-nota.php');
    exit;
}
