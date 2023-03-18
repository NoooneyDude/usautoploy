<?php

namespace GitHub\Events;

use GitHub\Models\PullRequestFull;

/**
 * {@see https://docs.github.com/webhooks-and-events/webhooks/webhook-events-and-payloads#pull_request pull_request GitHub Documentation}
 */
class EPullRequest
{
    public string $action;
    public PullRequestFull $pullRequest;

    public function __construct(array $parameters)
    {
        $this->action = $parameters['action'] ?? '';
        $this->pullRequest = new PullRequestFull($parameters['pull_request'] ?? []);
    }
}
