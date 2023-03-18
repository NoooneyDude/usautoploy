<?php

$lockFile = '/tmp/usautoploy.lock';

if (file_exists($lockFile)) {
    $existingPid = file_get_contents($lockFile);
    echo "Daemon is already running with PID $existingPid. See \"ps ax | grep $existingPid\". Quitting.";
    exit(1);
}

register_shutdown_function(function() use ($lockFile) {
    unlink($lockFile);
});

$pid = getmypid();

echo "Daemon started up with PID $pid.";

file_put_contents($lockFile, $pid);

$daemon = Daemon::getInstance();
$daemon->start();

$shutdown = function () use (&$run, $daemon) {
    $run = false;
    if ($daemon->isBusy()) {
        echo "Process is busy, finishing the current task %s before exiting...";
    }
    else {
        echo "daemon shut down";
        exit(0);
    }
};

pcntl_async_signals(true);
pcntl_signal(SIGINT, $shutdown);
pcntl_signal(SIGTERM, $shutdown);

try {
    $daemon->start($isRunManually);
}
catch (Exception $e) {
    switch ($e->getCode()) {
        case Exception::SOME_REASON:
            echo "printf $e getMessage quitting.";
    }
}

while ($run) {
    try {
        $thing->run();
    }
    catch (Throwable $e) {

    }

    sleep (RunInteval);
}
