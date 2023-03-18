<?php

namespace GitHub\Handlers;

use Config;
use GitHub\Events\EIssueComment;
use GitHub\Models\PullRequestFull;
use Logger;
use SerialisedQueue;

class OnIssueComment
{
    private static array $authorisedAuthorAssociations = [
        'MEMBER',
        'OWNER',
    ];

    private static array $affirmReactions = ['+1', 'hooray', 'rocket'];
    private static array $denyReactions = ['-1', 'confused', 'eyes'];

    private EIssueComment $issueComment;

    public function __construct(EIssueComment $issueComment)
    {
        $this->issueComment = $issueComment;
    }

    public function process(): void
    {
        if ($this->issueComment->issue->state !== 'open') return;

        if ($this->issueComment->action !== 'created' && $this->issueComment->action !== 'edited') {
            return;
        }

        if ($this->issueComment->comment->body == '/build') {
            if (in_array($this->issueComment->comment->authorAssociation, self::$authorisedAuthorAssociations)) {
                $this->addToQueue();
                $this->addAffirmReaction();
            }
            else {
                $this->addDenyReaction();
            }

            exit(0);
        }
    }

    private function addToQueue(): void
    {
        $pullRequest = PullRequestFull::get($this->issueComment->issue->pullRequest->url);

        $queue = new SerialisedQueue(Config::get('DAEMON/QUEUE_FILEPATH'));
        $queue->push([
            'action' => 'build-pr',
            'number' => $pullRequest->number,
            'title' => $pullRequest->title,
            'headRef' => $pullRequest->head->ref,
        ]);

        Logger::info("Added \"{$pullRequest->number}-{$pullRequest->head->ref}\" to queue.");
    }

    private function addAffirmReaction(): void
    {
        $randomKey = array_rand(self::$affirmReactions);
        $reactionName = self::$affirmReactions[$randomKey];

        $this->issueComment->comment->reactions->add($reactionName);
    }

    private function addDenyReaction(): void
    {
        $randomKey = array_rand(self::$denyReactions);
        $reactionName = self::$denyReactions[$randomKey];

        $this->issueComment->comment->reactions->add($reactionName);
    }
}
