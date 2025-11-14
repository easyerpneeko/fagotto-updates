<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UberEatsService
{
    private $httpClient;
    private $clientId;
    private $clientSecret;
    private $tokenUrl = 'https://login.uber.com/oauth/v2/token';
    private $orderUrl = 'https://api.uber.com/v1/delivery';
    private $menuUrl = 'https://api.uber.com/v2/eats/stores';

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->clientId = env('UBER_EATS_CLIENT_ID');
        $this->clientSecret = env('UBER_EATS_CLIENT_SECRET');
    }

    /**
     * Get access token with automatic caching
     */
    private function getAccessToken()
    {
        $cacheKey = 'uber_eats_access_token';
        $accessToken = Cache::get($cacheKey);

        if ($accessToken) {
            return $accessToken;
        }

        try {
            $response = $this->httpClient->post($this->tokenUrl, [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'eats.store eats.store.status.write eats.order eats.store.orders.read',
                    'grant_type' => 'client_credentials',
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            // Cache for almost the full duration (minus 30 seconds for safety)
            Cache::put($cacheKey, $data['access_token'], ($data['expires_in'] - 30) / 60);

            return $data['access_token'];
        } catch (\Exception $e) {
            Log::error('Uber Eats API Token Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Make authenticated request to Uber API
     */
    private function makeRequest($method, $url, $data = null)
    {
        $accessToken = $this->getAccessToken();
        
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ];

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options['json'] = $data;
        }

        try {
            $response = $this->httpClient->request($method, $url, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Uber Eats API Request Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all orders for a store
     */
    public function getOrders($storeId)
    {
        $url = $this->orderUrl . "/store/{$storeId}/orders?expand=deliveries,carts,payment";
        return $this->makeRequest('GET', $url);
    }

    /**
     * Get specific order details
     */
    public function getOrder($orderId)
    {
        $url = $this->orderUrl . "/order/{$orderId}?expand=deliveries,carts,payment";
        return $this->makeRequest('GET', $url);
    }

    /**
     * Accept an order
     */
    public function acceptOrder($orderId, $pickupTime = null, $externalId = null, $acceptedBy = null)
    {
        $data = [];
        
        if ($pickupTime) {
            $data['ready_for_pickup_time'] = date('c', strtotime($pickupTime));
        }
        
        if ($externalId) {
            $data['external_id'] = $externalId;
        }
        
        if ($acceptedBy) {
            $data['accepted_by'] = $acceptedBy;
        }

        $url = $this->orderUrl . "/order/{$orderId}/accept";
        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Deny an order
     */
    public function denyOrder($orderId, $reasonInfo, $reasonType = 'OTHER')
    {
        $data = [
            'reason' => [
                'info' => $reasonInfo,
                'type' => $reasonType
            ]
        ];

        $url = $this->orderUrl . "/order/{$orderId}/deny";
        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Cancel an order
     */
    public function cancelOrder($orderId, $reasonInfo, $reasonType = 'OTHER')
    {
        $data = [
            'reason' => [
                'info' => $reasonInfo,
                'type' => $reasonType
            ]
        ];

        $url = $this->orderUrl . "/order/{$orderId}/cancel";
        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Mark order as ready for pickup
     */
    public function markOrderReady($orderId)
    {
        $url = $this->orderUrl . "/order/{$orderId}/ready";
        return $this->makeRequest('POST', $url, []);
    }

    /**
     * Update order ready time
     */
    public function updateOrderReadyTime($orderId, $readyTime)
    {
        $data = [
            'ready_for_pickup_time' => date('c', strtotime($readyTime))
        ];

        $url = $this->orderUrl . "/order/{$orderId}/update-ready-time";
        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Get store menu
     */
    public function getMenu($storeId)
    {
        $url = $this->menuUrl . "/{$storeId}/menus";
        return $this->makeRequest('GET', $url);
    }

    /**
     * Update store menu
     */
    public function updateMenu($storeId, $menuData)
    {
        $url = $this->menuUrl . "/{$storeId}/menus";
        return $this->makeRequest('PUT', $url, $menuData);
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $token = $this->getAccessToken();
            return [
                'success' => true,
                'message' => 'Connection successful',
                'token_preview' => substr($token, 0, 20) . '...'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }
}
