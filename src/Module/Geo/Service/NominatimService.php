<?php

namespace App\Module\Geo\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class NominatimService
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function buscarDireccion(string $query): array
    {
        if (empty($query)) {
            throw new \InvalidArgumentException('El parámetro query es obligatorio.');
        }

        $response = $this->httpClient->request('GET', 'https://nominatim.openstreetmap.org/search', [
            'query' => [
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 5
            ],
            'headers' => [
                'User-Agent' => 'GeoBackendSymfony/1.0'
            ]
        ]);

        return $response->toArray();
    }
}
