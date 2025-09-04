<?php

// src/Service/BanService.php

namespace App\Security;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class BanService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function searchCity(string $query): ?array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                'https://api-adresse.data.gouv.fr/search/',
                [
                    'query' => [
                        'q' => $query,
                        'limit' => 1,
                    ],
                ]
            );

            if ($response->getStatusCode() !== 200) {
                $this->logger->error('API data.gouv.fr request failed.', ['status' => $response->getStatusCode(), 'query' => $query]);
                return null;
            }

            $data = $response->toArray();
            return $data['features'][0]['properties'] ?? null;
        } catch (\Exception $e) {
            $this->logger->error('Exception caught during API data.gouv.fr request.', ['exception' => $e->getMessage()]);
            return null;
        }
    }
}
