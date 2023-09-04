<?php

namespace App\Services\Interfaces;

interface AmoToken
{
    public function checkAccessToken($clientId, $clientSecret, $redirectUri): void;

    public function updateToken($clientId, $clientSecret, $redirectUri): void;

    public function getTokens(): ?array;

    public function saveTokens(array $arrayToken): void;
}
