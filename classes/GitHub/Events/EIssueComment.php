<?php

namespace GitHub\Events;

use GitHub\Models\Comment;
use GitHub\Models\Issue;

/**
 * {@see https://docs.github.com/en/webhooks-and-events/webhooks/webhook-events-and-payloads#issue_comment issue_comment GitHub Documentation}
 */
class EIssueComment
{
    public string $action;
    public Issue $issue;
    public Comment $comment;

    public function __construct(array $parameters)
    {
        $this->action = $parameters['action'] ?? '';
        $this->issue = new Issue($parameters['issue'] ?? []);
        $this->comment = new Comment($parameters['comment'] ?? []);
    }
}
