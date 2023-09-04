<?php

namespace App\Services;

use App\Services\Interfaces\AmoToken;
use HttpException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class AmoTokenService implements AmoToken
{
    public ?string $access_token;
    public ?string $refresh_token;
    public ?string $expires;

    public function __construct()
    {
        $arrayTokens = $this->getTokens();
        $this->access_token = $arrayTokens['accessToken'] ?? null;
        $this->refresh_token = $arrayTokens['refreshToken'] ?? null;
        $this->expires = $arrayTokens['expires'] ?? null;
    }

    /**
     * @throws HttpException
     */
    public function checkAccessToken($clientId, $clientSecret, $redirectUri): void
    {
        if (now()->timestamp >= $this->expires) {
            $this->updateToken($clientId, $clientSecret, $redirectUri);
        }
    }

    /**
     * @throws HttpException
     */
    public function updateToken($clientId, $clientSecret, $redirectUri): void
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post('https://leorl1k.amocrm.ru/oauth2/access_token',
                [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->refresh_token,
                    'redirect_uri' => $redirectUri,
                ]
            );

        if ($response->status() === 200) {
            $responseData = json_decode($response->body(), true);
            $this->access_token = $responseData['access_token'];
            $this->refresh_token = $responseData['refresh_token'];
            $this->expires = $responseData['expires_in'];
            $this->saveTokens(
                [
                    'accessToken' => $this->access_token,
                    'refreshToken' => $this->refresh_token,
                    'expires' => Carbon::now()->addSeconds($this->expires)->timestamp,
                ]
            );
        } else {
            throw new HttpException('Не удалось обменять refresh_token на access_token', $response->status());
        }
    }

    public function getTokens(): ?array
    {
        $filePath = storage_path("amo_crm/tokens/token.json");
        return json_decode(File::get($filePath), true);
    }

    public function saveTokens(array $arrayToken): void
    {
        $filePath = storage_path("amo_crm/tokens/token.json");
        File::put($filePath, json_encode($arrayToken));
    }
}
