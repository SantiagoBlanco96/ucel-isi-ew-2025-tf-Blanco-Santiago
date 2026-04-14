<?php

declare(strict_types=1);

/**
 * Convierte un subset de Markdown a HTML seguro para mostrar notas generadas.
 */
function markdownToHtml(string $markdown): string
{
    $markdown = str_replace(["\r\n", "\r"], "\n", trim($markdown));

    if ($markdown === '') {
        return '<p>Sin contenido generado.</p>';
    }

    $lines = explode("\n", $markdown);
    $htmlParts = [];
    $paragraphBuffer = [];

    $flushParagraph = static function () use (&$paragraphBuffer, &$htmlParts): void {
        if ($paragraphBuffer === []) {
            return;
        }

        $text = implode(' ', $paragraphBuffer);
        $text = applyMarkdownInline($text);
        $htmlParts[] = '<p>' . $text . '</p>';
        $paragraphBuffer = [];
    };

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '') {
            $flushParagraph();
            continue;
        }

        if (preg_match('/^###\s+(.+)$/', $trimmed, $matches) === 1) {
            $flushParagraph();
            $htmlParts[] = '<h3>' . applyMarkdownInline($matches[1]) . '</h3>';
            continue;
        }

        if (preg_match('/^##\s+(.+)$/', $trimmed, $matches) === 1) {
            $flushParagraph();
            $htmlParts[] = '<h2>' . applyMarkdownInline($matches[1]) . '</h2>';
            continue;
        }

        if (preg_match('/^#\s+(.+)$/', $trimmed, $matches) === 1) {
            $flushParagraph();
            $htmlParts[] = '<h1>' . applyMarkdownInline($matches[1]) . '</h1>';
            continue;
        }

        $paragraphBuffer[] = $trimmed;
    }

    $flushParagraph();

    return implode("\n", $htmlParts);
}

function applyMarkdownInline(string $text): string
{
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // Aplica negrita primero para evitar conflictos con cursiva.
    $escaped = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
    $escaped = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $escaped) ?? $escaped;

    return $escaped;
}
