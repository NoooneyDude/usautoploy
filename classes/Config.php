<?php

class Config
{
    private static array $config = [];

    public static function init(): void
    {
        $config = require_once '../config.php';

        if (!$config) {
            Logger::error('Config not available. Terminating.');
            exit(1);
        }

        self::$config = $config;
    }

    public static function get(string $keyPath)
    {
        $keys = explode('/', $keyPath);

        $config = self::$config;
        foreach ($keys as $key) {
            if (!is_array($config) || !array_key_exists($key, $config)) {
                throw new InvalidArgumentException("Invalid key path: $keyPath.");
            }

            $config = $config[$key];
        }

        return $config;
    }
}
