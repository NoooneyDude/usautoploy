<?php

namespace GitHub\Models;

use GitHub\Api\GhubApiFactory;

class PullRequestFull
{
    public int $number;
    public string $title;
    public Branch $head;

    public static function get(string $url): PullRequestFull
    {
        $ghubApi = GhubApiFactory::getInstance()->getApi();

        $json = $ghubApi->get($url);
        $parameters = json_decode($json, true);

        return new PullRequestFull($parameters);
    }

    public function __construct(array $parameters)
    {
        $this->number = $parameters['number'] ?? -1;
        $this->title = $parameters['title'] ?? '';
        $this->head = new Branch($parameters['head'] ?? []);
    }
}
