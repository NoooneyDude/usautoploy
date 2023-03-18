<?php

class Logger
{
    private string $prefix;

    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }

    public function iInfo(string $message)
    {
        self::log('INFO', $message, $this->prefix);
    }

    public function iWarn(string $message)
    {
        self::log('WARN', $message, $this->prefix);
    }

    public function iError(string $message)
    {
        self::log('ERROR', $message, $this->prefix);
    }

    public function iEmerg(string $message)
    {
        self::log('EMERG', $message, $this->prefix);
    }

    public static function info(string $message)
    {
        self::log('INFO', $message,);
    }

    public static function warn(string $message)
    {
        self::log('WARN', $message);
    }

    public static function error(string $message)
    {
        self::log('ERROR', $message);
    }

    public static function emerg(string $message)
    {
        self::log('EMERG', $message);
    }

    private static function log(string $level, string $message, string $prefix = '')
    {
        $date = date('Y-m-d H:i:s');
        $line = $prefix
            ? "[$date] [$level] [$prefix] $message" . PHP_EOL
            : "[$date] [$level] $message" . PHP_EOL;

        $filepath = Config::get('LOGGER/FILEPATH');

        error_log($line, 3, $filepath);
    }
}

set_exception_handler(function (Throwable $exception) {
    Logger::emerg($exception);
});
