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

        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'data' => [],
                'error' => 'La extensión cURL no está disponible en el servidor.',
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

        $curl = curl_init($webhookUrl);

        if ($curl === false) {
            return [
                'success' => false,
                'data' => [],
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

        $rawResponse = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($rawResponse === false) {
            return [
                'success' => false,
                'data' => [],
                'error' => $curlError !== '' ? $curlError : 'Error desconocido al contactar n8n.',
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'data' => [],
                'error' => sprintf('n8n respondió con código HTTP %d.', $httpCode),
            ];
        }

        $decoded = json_decode($rawResponse, true);

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
}

