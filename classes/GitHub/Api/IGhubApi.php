<?php

namespace GitHub\Api;

interface IGhubApi
{
    public function get(string $url);

    public function post(string $url, $payload);
}
