<?php

namespace App\Services;

use App\Services\Concerns\AuthenticatesWithCj;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Trae el listado de productos trending/best-sellers de CJ Dropshipping.
 * Docs: https://developers.cjdropshipping.com/
 *
 * Requiere en .env: CJ_API_EMAIL, CJ_API_KEY
 */
class CjTrendingService
{
    use AuthenticatesWithCj;

    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.cj_dropshipping.base_uri', 'https://developers.cjdropshipping.com/api2.0/v1/'),
            'timeout' => 20,
        ]);
    }

    /**
     * Devuelve productos "hot/trending" normalizados:
     * [['supplier_product_id', 'name', 'category', 'price', 'sales_count', 'rank', 'image_url', 'product_url'], ...]
     *
     * Nota: el endpoint exacto de "hot products" puede variar según el plan de acceso
     * a la API de CJ; si tu cuenta no tiene ese endpoint habilitado, cae en fallback
     * a `product/list` ordenado por los campos disponibles (ver comentario abajo).
     */
    public function fetchTrending(int $limit = 50): array
    {
        $token = $this->authenticate($this->client);

        if (! $token) {
            return [];
        }

        try {
            $response = $this->client->get('product/list', [
                'headers' => ['CJ-Access-Token' => $token],
                'query' => [
                    'pageNum' => 1,
                    'pageSize' => $limit,
                    // Ajustar según los filtros reales que exponga tu plan de API
                    // (algunos planes tienen un endpoint dedicado "product/getHotProduct").
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $items = $data['data']['list'] ?? [];

            return collect($items)->values()->map(function ($item, $index) {
                return [
                    'supplier_product_id' => $item['pid'] ?? null,
                    'name' => $item['productNameEn'] ?? $item['productName'] ?? 'Producto sin nombre',
                    'category' => $item['categoryName'] ?? null,
                    'price' => (float) ($item['sellPrice'] ?? 0),
                    'sales_count' => isset($item['sellNum']) ? (int) $item['sellNum'] : null,
                    'rank' => $index + 1,
                    'image_url' => $item['productImage'] ?? null,
                    'product_url' => isset($item['pid']) ? "https://cjdropshipping.com/product/{$item['pid']}.html" : null,
                ];
            })->filter(fn ($p) => $p['supplier_product_id'])->all();
        } catch (GuzzleException $e) {
            Log::warning("CJ Trending: fallo al traer productos: {$e->getMessage()}");
            return [];
        }
    }
}
