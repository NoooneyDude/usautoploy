<?php

namespace Daemon;

use Config;
use Exception; // TODO custom?
use Logger;

class Licenser
{
    const CONFIG_LICENSE_DIR = 'UNITY/LICNESE_DIRECTORY';
    const CONFIG_LICENSE_FILE = 'UNITY/LICENSE_FILENAME';
    const CONFIG_UNITY_VERSION = 'UNITY/VERSION';

    private static Licenser $instance;

    private Logger $logger;

    private string $licenseFilepath;

    public static function getInstance(): Licenser
    {
        if (!isset(self::$instance)) {
            self::$instance = new Licenser();
        }

        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->logger = new Logger('Licenser');

        $licenseDir = Config::get(self::CONFIG_LICENSE_DIR);
        $licenseFilename = Config::get(self::CONFIG_LICENSE_FILE);

        if (!$licenseDir) {
            throw new Exception('Config option ' . self::CONFIG_LICENSE_DIR . ' is not set.');
        }

        if (!$licenseFilename) {
            throw new Exception('Config option ' . self::CONFIG_LICENSE_FILE . ' is not set.');
        }

        if (!is_dir($licenseDir)) {
            $this->logger->iInfo("Creating license directory (\"$licenseDir\").");
            if (!mkdir($licenseDir)) {
                throw new Exception ("Couldn't create license directory (\"$licenseDir\").");
            }
        }

        $this->licenseFilepath = $licenseDir . DIRECTORY_SEPARATOR . $licenseFilename;
    }

    public function getLicenseFilepath(): string
    {
        return $this->licenseFilepath;
    }

    /**
     * @throws Exception
     */
    public function ensureLicense(): void
    {
        if (!is_file($this->licenseFilepath)) {
            $this->logger->iInfo("License file not found at \"$this->licenseFilepath\". Generating license...");
            $this->generateLicense();
            $this->logger->iInfo("License file generated: \"$this->licenseFilepath\".");
        }
    }

    /**
     * @throws Exception
     */
    private function generateLicense(): void
    {
        $unityVersion = Config::get(self::CONFIG_UNITY_VERSION);

        if (!$unityVersion) {
            throw new Exception('Config option ' . self::CONFIG_UNITY_VERSION . ' is not set.');
        }

        $arguments = [
            '--rm', // Clean up container when it exits
            "--volume $this->licenseFilepath:/root/licenses",
            "--volume {cwd/'logs'}:/root/logs", // TODO check the pycharm thing
            '--workdir /root/licenses',
            'unityci/editor:$unityVersion-base-0', // Docker image to load
            'unity-editor -batchmode -nographics', // Command to run
            '-createManualActivationFile',
            '-logfile /root/logs/licenser.log',
        ];

        $output = [];

        $lastLine = exec('docker run ' . join(' ', $arguments), $output, $exitCode);

        // TODO do something with this stuff.
    }
}
