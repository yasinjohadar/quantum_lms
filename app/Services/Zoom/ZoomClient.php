<?php

namespace App\Services\Zoom;

use App\Models\ZoomAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZoomClient
{
    protected Client $client;
    protected string $baseUrl;
    protected ?ZoomAccount $account;
    protected ?string $accountId;
    protected ?string $clientId;
    protected ?string $clientSecret;

    public function __construct(?ZoomAccount $account = null)
    {
        $this->account = $account ?? ZoomAccount::getDefault();
        
        if ($this->account) {
            $this->baseUrl = config('zoom.api_base_url', 'https://api.zoom.us/v2');
            $this->accountId = $this->account->account_id;
            $this->clientId = $this->account->client_id;
            $this->clientSecret = $this->account->decrypted_client_secret;
        } else {
            // Fallback to config for backward compatibility
            $this->baseUrl = config('zoom.api_base_url', 'https://api.zoom.us/v2');
            $this->accountId = config('zoom.account_id');
            $this->clientId = config('zoom.client_id');
            $this->clientSecret = config('zoom.client_secret');
        }

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
        ]);
    }

    /**
     * Get Server-to-Server OAuth access token (cached)
     */
    public function getAccessToken(): string
    {
        $accountId = $this->account?->id ?? 'default';
        $cacheKey = "zoom_access_token_{$accountId}";
        $ttl = config('zoom.token_cache_ttl', 3600) - 60; // Cache for expires_in - 60 seconds

        return Cache::remember($cacheKey, $ttl, function () {
            try {
                $client = new Client(['timeout' => 30]);
                
                $response = $client->post('https://zoom.us/oauth/token', [
                    'auth' => [$this->clientId, $this->clientSecret],
                    'form_params' => [
                        'grant_type' => 'account_credentials',
                        'account_id' => $this->accountId,
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if (!isset($data['access_token'])) {
                    throw new \Exception('Access token not found in Zoom response');
                }

                return $data['access_token'];
            } catch (GuzzleException $e) {
                Log::error('Failed to get Zoom access token: ' . $e->getMessage());
                throw new \Exception('Failed to authenticate with Zoom API: ' . $e->getMessage());
            }
        });
    }

    /**
     * Create a Zoom meeting
     */
    public function createMeeting(array $data): array
    {
        try {
            $response = $this->client->post('/users/me/meetings', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Failed to create Zoom meeting: ' . $e->getMessage());
            throw new \Exception('Failed to create Zoom meeting: ' . $e->getMessage());
        }
    }

    /**
     * Update a Zoom meeting
     */
    public function updateMeeting(string $meetingId, array $data): array
    {
        try {
            $response = $this->client->patch("/meetings/{$meetingId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Failed to update Zoom meeting: ' . $e->getMessage());
            throw new \Exception('Failed to update Zoom meeting: ' . $e->getMessage());
        }
    }

    /**
     * Delete a Zoom meeting
     */
    public function deleteMeeting(string $meetingId): bool
    {
        try {
            $this->client->delete("/meetings/{$meetingId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
            ]);

            return true;
        } catch (GuzzleException $e) {
            Log::error('Failed to delete Zoom meeting: ' . $e->getMessage());
            throw new \Exception('Failed to delete Zoom meeting: ' . $e->getMessage());
        }
    }

    /**
     * Get a Zoom meeting
     */
    public function getMeeting(string $meetingId): array
    {
        try {
            $response = $this->client->get("/meetings/{$meetingId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Failed to get Zoom meeting: ' . $e->getMessage());
            throw new \Exception('Failed to get Zoom meeting: ' . $e->getMessage());
        }
    }

    /**
     * Get meeting participants (for future webhook reconciliation)
     */
    public function getMeetingParticipants(string $meetingId): array
    {
        try {
            $response = $this->client->get("/meetings/{$meetingId}/participants", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Failed to get Zoom meeting participants: ' . $e->getMessage());
            return [];
        }
    }
}

