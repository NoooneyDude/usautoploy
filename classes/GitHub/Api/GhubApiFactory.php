<?php

namespace GitHub\Api;

class GhubApiFactory
{
    private static GhubApiFactory $instance;

    public static function getInstance(): GhubApiFactory
    {
        if (!isset(self::$instance)) {
            self::$instance = new GhubApiFactory();
        }

        return self::$instance;
    }

    public function getApi(): IGhubApi
    {
        return GhubApi::getInstance();
    }
}
