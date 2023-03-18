<?php

use GitHub\Events\EIssueComment;
use GitHub\Handlers\OnIssueComment;

require_once '../autoloader.php';

Config::init();

$githubEvent = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if (!$githubEvent) {
    Logger::warn("ghook endpoint invoked but no GitHub event was specified. Ignoring.");
    exit(1);
}

Logger::info("Processing event \"$githubEvent\".");

$postData = file_get_contents('php://input');

$providedSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
$expectedSignature = 'sha1=' . hash_hmac('sha1', $postData, Config::get('GITHUB/WEBHOOK_SECRET'));

if (!hash_equals($providedSignature, $expectedSignature)) {
    Logger::error("ghook endpoint invoked with an unexpected signature. Ignoring.");
    exit(1);
}

$parameters = json_decode($postData, true);

switch ($githubEvent) {
    case 'issue_comment':
        $event = new EIssueComment($parameters);
        $handler = new OnIssueComment($event);
        $handler->process();
        break;
}

exit(0);
