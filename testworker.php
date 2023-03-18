<?php

class TestWorker extends Worker
{
    public function run()
    {
        echo "running\n";
    }
}

echo "Instantiating worker A\n";
$workerA = new TestWorker();
echo "Instantiating worker B\n";
$workerB = new TestWorker();

echo "Workers instantiated.\n";
