<?php

namespace GitHub\Models;

class PullRequest
{
    public string $url;

    public function __construct(array $parameters)
    {
        $this->url = $parameters['url'] ?? '';
    }
}
