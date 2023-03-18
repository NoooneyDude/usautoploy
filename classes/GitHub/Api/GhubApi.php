<?php

namespace GitHub\Api;

use Config;
use Logger;

class GhubApi implements IGhubApi
{
    private static GhubApi $instance;

    private string $token;

    public static function getInstance(): GhubApi
    {
        if (!isset(self::$instance)) {
            self::$instance = new GhubApi();
        }

        self::$instance->token = Config::get('GITHUB/PERSONAL_ACCESS_TOKEN');

        return self::$instance;
    }

    public function get(string $url)
    {
        $candle = self::getNewGetCandle();
        curl_setopt($candle, CURLOPT_URL, $url);

        $result = curl_exec($candle);

        if (curl_errno($candle)) {
            Logger::error('Error: ' . curl_error($candle));
        }

        return $result;
    }

    public function post(string $url, $payload)
    {
        $body = json_encode($payload);
        if ($payload === false) {
            Logger::error('Unable to send payload.');
            return;
        }

        $candle = self::getNewPostCandle();
        curl_setopt($candle, CURLOPT_URL, $url);
        curl_setopt($candle, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($candle);

        if (curl_errno($candle)) {
            Logger::error('Error: ' . curl_error($candle));
            return;
        }

        curl_close($candle);

        return $result;
    }

    private function getNewGetCandle()
    {
        $token = $this->token;

        $headers = [
            'Accept: application/vnd.github+json',
            "Authorization: token $token",
            'X-Github-Api-Version: 2022-11-28',
        ];

        $curlOptions = [
            CURLOPT_USERAGENT => 'USAutoPloy/1.0',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => $headers,
        ];

        $candle = curl_init();
        curl_setopt_array($candle, $curlOptions);

        return $candle;
    }

    private function getNewPostCandle()
    {
        $token = $this->token;

        $headers = [
            'Accept: application/vnd.github+json',
            "Authorization: Bearer $token",
            'X-Github-Api-Version: 2022-11-28',
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $curlOptions = [
            CURLOPT_USERAGENT => 'USAutoPloy/1.0',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => 1,
        ];

        $candle = curl_init();
        curl_setopt_array($candle, $curlOptions);

        return $candle;
    }
}
