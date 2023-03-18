<?php

class SerialisedQueue
{
    private array $queue;
    private string $filename;
    private $fandle;

    public function __construct($filename)
    {
        $this->queue = [];
        $this->filename = $filename;
    }

    public function push($item): void
    {
        $this->load(LOCK_EX);
        $this->queue[] = $item;
        $this->save();
    }

    public function pop()
    {
        $this->load(LOCK_EX);
        $value = array_shift($this->queue);
        $this->save();

        return $value;
    }

    public function peek()
    {
        $this->load();

        return $this->queue[0] ?? null;
    }

    public function clear(): void
    {
        $this->queue = [];
        $this->save();
    }

    public function size(): int
    {
        $this->load();

        return count($this->queue);
    }

    public function toArray(): array
    {
        $this->load();

        return $this->queue;
    }

    public function fromArray(array $array)
    {
        $this->queue = $array;
        $this->save();
    }

    private function toJson(): string
    {
        $value = json_encode($this->queue); // TODO

        return $value;
    }

    private function fromJson($json): void
    {
        $this->queue = json_decode($json, true);
    }

    // If explicit lock, be sure to save() to unlock.
    private function load(bool $explicitLock = false): void
    {
        // If the file exists, get the queue from it.
        if (file_exists($this->filename)) {
            $this->fandle = fopen($this->filename, $explicitLock ? 'r+' : 'r');
            if (!$this->fandle) {
                throw new Exception("Unable to open queue file \"$this->filename\"");
            }

            if (!flock($this->fandle, $explicitLock ? LOCK_EX : LOCK_SH)) {
                throw new Exception("Unable to lock queue file \"$this->filename\"");
            }

            $filesize = filesize($this->filename);
            if ($filesize === false) {
                throw new Exception("Unable to get the size of queue file \"$this->filename\"");
            }

            if ($filesize === 0) {
                $this->queue = [];
                return;
            }

            $json = fread($this->fandle, $filesize);
            if ($json === false) {
                throw new Exception("Unable to read queue file \"$this->filename\"");
            }

            // If we didn't lock the file with write mode, then since we're done reading we can unlock it again,
            if (!$explicitLock) {
                if (!flock($this->fandle, LOCK_UN)) {
                    throw new Exception("Unable to unlock queue file \"$this->filename\"");
                }

                if (!fclose($this->fandle)) {
                    throw new Exception("Unable to close resource handle for queue file \"$this->filename\"");
                }
            }

            $this->fromJson($json);
        }
        else {
            $this->queue = [];
        }
    }

    private function save(): void
    {
        $json = $this->toJson();

        if (!file_exists($this->filename)) {
            touch($this->filename);
        }

        file_put_contents($this->filename, $json, 0, $this->fandle);
        fclose($this->fandle);
    }
}
