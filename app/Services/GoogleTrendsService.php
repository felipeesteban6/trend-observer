<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Cliente para los endpoints NO oficiales que usa la web de Google Trends
 * (los mismos que usa la librería pytrends). Google no publica una API
 * pública soportada para esto, así que:
 *   - No hay SLA ni garantía de estabilidad: si Google cambia el endpoint,
 *     esto puede romperse y hay que ajustarlo.
 *   - Hay que ser conservador con la frecuencia de consultas (rate limit
 *     no documentado) para evitar bloqueos temporales de IP.
 *   - Hacemos como máximo 1 consulta por keyword por día (ver
 *     FetchGoogleTrendsJob) y con backoff ante errores.
 */
class GoogleTrendsService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://trends.google.com/trends/api/',
            'timeout' => 15,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            ],
        ]);
    }

    /**
     * Devuelve el interés diario (0-100) de los últimos ~90 días para un término.
     * Estructura de retorno: [['date' => 'YYYY-MM-DD', 'interest' => int], ...]
     */
    public function dailyInterest(string $term, string $language = 'es', ?string $geo = ''): array
    {
        try {
            $token = $this->requestWidgetToken($term, $language, $geo);

            if (! $token) {
                return [];
            }

            $response = $this->client->get('widgetdata/multiline', [
                'query' => [
                    'req' => json_encode($token['request']),
                    'token' => $token['token'],
                    'tz' => -240,
                ],
            ]);

            $data = $this->decode((string) $response->getBody());
            $timeline = $data['default']['timelineData'] ?? [];

            return collect($timeline)->map(fn ($point) => [
                'date' => date('Y-m-d', (int) $point['time']),
                'interest' => (int) ($point['value'][0] ?? 0),
            ])->all();
        } catch (GuzzleException $e) {
            Log::warning("Google Trends: fallo al consultar '{$term}': {$e->getMessage()}");
            return [];
        }
    }

    private function requestWidgetToken(string $term, string $language, ?string $geo): ?array
    {
        $exploreRequest = [
            'comparisonItem' => [[
                'keyword' => $term,
                'geo' => $geo ?: '',
                'time' => 'today 3-m',
            ]],
            'category' => 0,
            'property' => '',
        ];

        $response = $this->client->get('explore', [
            'query' => [
                'hl' => $language,
                'tz' => -240,
                'req' => json_encode($exploreRequest),
            ],
        ]);

        $data = $this->decode((string) $response->getBody());
        $widget = collect($data['widgets'] ?? [])->firstWhere('id', 'TIMESERIES');

        if (! $widget) {
            return null;
        }

        return [
            'token' => $widget['token'],
            'request' => $widget['request'],
        ];
    }

    /**
     * Las respuestas de Google Trends vienen con un prefijo ")]}'," antes del JSON
     * (protección anti-hijacking estándar de Google). Hay que quitarlo antes de decodificar.
     */
    private function decode(string $body): array
    {
        $clean = preg_replace('/^\)\]\}\',?\n?/', '', $body);

        return json_decode($clean, true) ?? [];
    }
}
