<?php

declare(strict_types=1);

/**
 * Servicio HTTP para integración con webhook de n8n.
 */
final class N8nService
{
    public function enviarPedido(array $payload): array
    {
        $webhookUrl = $this->getWebhookUrl();

        if ($webhookUrl === '') {
            return [
                'success' => false,
                'data' => [],
                'error' => 'La variable N8N_WEBHOOK_URL no está configurada.',
            ];
        }

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($jsonPayload === false) {
            return [
                'success' => false,
                'data' => [],
                'error' => 'No fue posible serializar el payload a JSON.',
            ];
        }

        $httpResult = function_exists('curl_init')
            ? $this->postWithCurl($webhookUrl, $jsonPayload)
            : $this->postWithStream($webhookUrl, $jsonPayload);

        if ($httpResult['success'] !== true) {
            return [
                'success' => false,
                'data' => [],
                'error' => (string) $httpResult['error'],
            ];
        }

        $httpCode = (int) $httpResult['http_code'];
        $response = (string) $httpResult['body'];

        error_log('n8n HTTP code: ' . $httpCode);
        error_log('n8n response: ' . substr($response, 0, 500));

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'success' => false,
                'data' => [],
                'error' => sprintf('n8n respondió con código HTTP %d.', $httpCode),
            ];
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            return [
                'success' => false,
                'data' => [],
                'error' => 'La respuesta de n8n no es JSON válido.',
            ];
        }

        return [
            'success' => true,
            'data' => $decoded,
            'error' => null,
        ];
    }

    private function getWebhookUrl(): string
    {
        $value = getenv('N8N_WEBHOOK_URL');

        if ($value === false || $value === null) {
            $value = $_ENV['N8N_WEBHOOK_URL'] ?? $_SERVER['N8N_WEBHOOK_URL'] ?? '';
        }

        return trim((string) $value);
    }

    private function postWithCurl(string $webhookUrl, string $jsonPayload): array
    {
        $curl = curl_init($webhookUrl);

        error_log('Iniciando cURL a: ' . $webhookUrl);

        if ($curl === false) {
            return [
                'success' => false,
                'http_code' => 0,
                'body' => '',
                'error' => 'No fue posible inicializar cURL.',
            ];
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 120,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            error_log('cURL error #' . $curlErrno . ': ' . $curlError);
            curl_close($curl);
            return [
                'success' => false,
                'http_code' => 0,
                'body' => '',
                'error' => 'cURL error #' . $curlErrno . ': ' . $curlError,
            ];
        }

        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return [
            'success' => true,
            'http_code' => $httpCode,
            'body' => (string) $response,
            'error' => null,
        ];
    }

    private function postWithStream(string $webhookUrl, string $jsonPayload): array
    {
        error_log('Iniciando stream HTTP a: ' . $webhookUrl);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $jsonPayload,
                'timeout' => 120,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($webhookUrl, false, $context);
        $headers = isset($http_response_header) && is_array($http_response_header) ? $http_response_header : [];
        $httpCode = $this->extractHttpStatusCode($headers);

        if ($response === false) {
            $lastError = error_get_last();
            return [
                'success' => false,
                'http_code' => $httpCode,
                'body' => '',
                'error' => (string) ($lastError['message'] ?? 'No fue posible completar la solicitud HTTP.'),
            ];
        }

        return [
            'success' => true,
            'http_code' => $httpCode,
            'body' => (string) $response,
            'error' => null,
        ];
    }

    /**
     * @param array<int, string> $headers
     */
    private function extractHttpStatusCode(array $headers): int
    {
        if ($headers === []) {
            return 0;
        }

        $statusLine = (string) $headers[0];

        if (preg_match('/\s(\d{3})\s/', $statusLine, $matches) === 1) {
            return (int) $matches[1];
        }

        return 0;
    }
}

