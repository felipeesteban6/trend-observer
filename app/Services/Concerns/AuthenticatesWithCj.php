<?php

namespace App\Services\Concerns;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

trait AuthenticatesWithCj
{
    private ?string $accessToken = null;

    protected function authenticate(Client $client): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $response = $client->post('authentication/getAccessToken', [
                'json' => [
                    'email' => config('services.cj_dropshipping.email'),
                    'password' => config('services.cj_dropshipping.api_key'),
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $this->accessToken = $data['data']['accessToken'] ?? null;

            return $this->accessToken;
        } catch (GuzzleException $e) {
            Log::warning('CJ Dropshipping: fallo de autenticación: '.$e->getMessage());
            return null;
        }
    }
}
