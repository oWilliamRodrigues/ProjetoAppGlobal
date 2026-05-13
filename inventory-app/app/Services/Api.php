<?php

namespace App\Services;

class Api
{
    public function __construct(private string $baseUrl)
    {
        // Initialize any necessary properties or configurations here
    }
    public function get($endpoint = '')
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}