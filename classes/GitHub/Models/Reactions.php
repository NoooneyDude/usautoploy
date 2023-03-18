<?php

namespace GitHub\Models;

use GitHub\Api\GhubApiFactory;
use GitHub\Api\IGhubApi;

class Reactions
{
    public string $url;

    private IGhubApi $ghubApi;

    public function __construct(array $parameters)
    {
        $this->ghubApi = GhubApiFactory::getInstance()->getApi();

        $this->url = $parameters['url'];
    }

    public function add(string $reactionName)
    {
        $payload = [
            'content' => $reactionName,
        ];

        $response = $this->ghubApi->post($this->url, $payload);

        return $response;
    }
}
