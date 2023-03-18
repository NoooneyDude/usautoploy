<?php

namespace GitHub\Models;

class Issue
{
    public string $state;
    public PullRequest $pullRequest;

    public function __construct(array $parameters)
    {
        $this->state = $parameters['state'] ?? '';
        $this->pullRequest = new PullRequest($parameters['pull_request'] ?? []);
    }
}
