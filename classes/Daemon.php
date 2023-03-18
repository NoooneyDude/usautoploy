<?php

use Daemon\Licenser;
use Gitonomy\Git\Admin;
use Gitonomy\Git\Repository;

class Daemon
{
    private static Daemon $instance;

    private SerialisedQueue $queue;

    public static function getInstance(): Daemon
    {
        if (!self::$instance) {
            self::$instance = new Daemon();
        }

        return self::$instance;
    }

    public function start(): void
    {
        Licenser::getInstance()->ensureLicense();
        $this->ensureLocalRepo();

        $this->queue = new SerialisedQueue(Config::get('DAEMON/QUEUE_FILEPATH'));
    }

    public function run(): void
    {
        if ($this->queue->size() <= 0) return;

        $job = $this->queue->pop();

        if (!is_array($job)) {
            throw new Exception("Unexpected job. Was expecting an associative array."); // TODO custom exception?
        }

        $this->processJob($job);
    }

    private function processJob(array $job)
    {
        $jobHandlers = ['build-pr' => '\Daemon\Jobs\Builder']; // TODO static member?

        $jobType = $job['action'] ?? '';
        if (!in_array($jobType, $jobHandlers)) {
            Logger::warn("Unexpected job type \"$jobType\". Discarding."); // TODO use logger instance?
            return;
        }

        $className = $jobHandlers[$jobType];
        if (!in_array('IJob', class_implements($className) ?: [])) {
            // // TODO log and discard;
        }

        $parameters = $job['parameters'] ?? [];
        $jobHandler = new $className();
        $jobHandler->processJob($parameters);
    }

    private function ensureLocalRepo()
    {
        $projectDir = getcwd() . DIRECTORY_SEPARATOR . Config::get('GIT/LOCAL_DIRECTORY');

        if (!is_dir($projectDir)) {
            $projectUrl = Config::get('PROJECT/URL');
            Logger::info("Local project repository not found. Cloning from \"$projectUrl\" to \"$projectDir\"...");
            Admin::cloneTo($projectDir, $projectUrl);
        }
    }
}
