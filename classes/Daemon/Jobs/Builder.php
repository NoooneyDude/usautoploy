<?php

namespace Daemon\Jobs;

use Config;
use Daemon\Licenser;
use Exception;
use Logger;
use Symfony\Component\Process\Process;
use Utils\FileUtils;

class Builder implements IJob
{
    const CONFIG_BUILDS_DIR = 'BUILDS/DIR';

    private Logger $logger;
    private string $buildsDir;

    private int $prNumber;
    private string $prTitle;
    private string $prHeadRef;
    private array $targetPlatforms;

    private string $buildIdentifier;

    public function __construct(array $jobDetails)
    {
        $this->logger = new Logger('Builder');

        $this->buildsDir = Config::get(self::CONFIG_BUILDS_DIR);
        $this->ensureRootBuildsDir();

        $this->prNumber = $jobDetails['number'] ?? 0;
        $this->prTitle = $jobDetails['title'] ?? '';
        $this->prHeadRef = $jobDetails['headRef'] ?? '';
        $this->targetPlatforms = $parameters['target-platforms'] ?? [];

        if (empty($this->targetPlatforms)) {
            $this->targetPlatforms = Config::get('TARGET_PLATFORMS');
        }

        if (!$this->prNumber || !$this->prTitle || !$this->prHeadRef) {
            throw new Exception("Job details incomplete.");
        }

        $this->buildIdentifier = "{$this->prNumber}-{$this->prHeadRef}";
    }

    public function processJob(array $_ = [])
    {
        $this->ensureBuildsDir();
        $this->setBuildInfo();
        $this->setBuildConfig();
        $this->executeBuild();
    }

    private function ensureRootBuildsDir()
    {
        if (!$this->buildsDir) {
            throw new Exception('Config option ' . self::CONFIG_BUILDS_DIR . ' is not set.');
        }

        if (!is_dir($this->buildsDir)) {
            $this->logger->iInfo("Creating builds directory (\"$$this->buildsDir\").");
            if (!mkdir($this->buildsDir)) {
                throw new Exception ("Couldn't create builds directory (\"$this->buildsDir\").");
            }
        }
    }

    private function ensureBuildsDir()
    {
        $buildDir = $this->buildsDir . DIRECTORY_SEPARATOR . $this->buildIdentifier;

        if (is_dir($buildDir)) {
            FileUtils::removeDirectory($buildDir);
            // We've probably previously built this build. Ensure a clean slate.
        }

        if (!mkdir($buildDir)) {
            throw new Exception("Couldn't create build directory \"$buildDir\" for \"$this->buildIdentifier\".");
        }

        foreach ($this->targetPlatforms as $target) {
            $platformBuildDir = $buildDir . DIRECTORY_SEPARATOR . $target;

            if (!mkdir($platformBuildDir)) {
                throw new Exception("Couldn't create build directory \"$platformBuildDir\" for \"$this->buildIdentifier\", platform \"$target\".");
            }
        }
    }

    private function setBuildInfo()
    {
        $streamingAssetsPath = getcwd() . DIRECTORY_SEPARATOR . Config::get('GIT/LOCAL_DIRECTORY') . '/Assets/StreamingAssets/';

        $buildInfoFile = file_get_contents($streamingAssetsPath . 'buildinfo.json');
        if ($buildInfoFile === false) {
            throw new Exception("File \"buildinfo.json\" not found in \"$streamingAssetsPath\".");
        }
        $buildInfo = json_decode($buildInfoFile, true);
        if (!is_array($buildInfo)) {
            throw new Exception("Failed to parse \"buildinfo.json\".");
        }

        $buildInfo['ForkName'] = Config::get('BUILDS/PR_FORK_NAME');
        $buildInfo['BuildNumber'] = $this->buildIdentifier;

        $json = json_encode($buildInfo, JSON_PRETTY_PRINT);
        file_put_contents($streamingAssetsPath . 'buildinfo.json', $json);
    }

    private function setBuildConfig()
    {
        $streamingAssetsPath = getcwd() . DIRECTORY_SEPARATOR . Config::get('GIT/LOCAL_DIRECTORY') . '/Assets/StreamingAssets/';

        $configFile = file_get_contents($streamingAssetsPath . 'config/config.json');
        if ($configFile === false) {
            throw new Exception("File \"buildinfo.json\" not found in \"$streamingAssetsPath/config\".");
        }
        $config = json_decode($configFile, true);
        if (!is_array($config)) {
            throw new Exception("Failed to parse \"config.json\".");
        }

        $url = Config::get('CDN/DOWNLOAD_URL');
        $prForkName = Config::get('BUILDS/PR_FORK_NAME');

        $config['WinDownload'] = sprintf($url, $prForkName, 'StandaloneWindows64', $this->buildIdentifier);
        $config['OSXDownload'] = sprintf($url, $prForkName, 'StandaloneOSX', $this->buildIdentifier);
        $config['LinuxDownload'] = sprintf($url, $prForkName, 'StandaloneLinux64', $this->buildIdentifier);

        $json = json_encode($config, JSON_PRETTY_PRINT);
        file_put_contents($streamingAssetsPath . 'config/config.json', $json);
    }

    private function executeBuild()
    {
        $unityVersion = Config::get('UNITY/VERSION');

        $projectDir = getcwd() . DIRECTORY_SEPARATOR . Config::get('GIT/LOCAL_DIRECTORY');
        $outputDir = getcwd() . DIRECTORY_SEPARATOR . Config::get('BUILDS/OUTPUT_DIRECTORY') . DIRECTORY_SEPARATOR . $this->buildIdentifier;
        $logsDir = getcwd() . DIRECTORY_SEPARATOR . 'logs';
        $licenseFile = Licenser::getInstance()->getLicenseFilepath();

        $mountArgs = join(' ', [
            "--volume $projectDir:/root/UnityProject",
            "--volume $outputDir:/root/builds",
            "--volume $logsDir:/root/logs",
            "--volume $licenseFile:/root/.local/share/unity3d/Unity/Unity_lic.ulf",
        ]);

        // In sequence for now.
        foreach ($this->targetPlatforms as $platform) {
            $image = "unityci/editor:{$unityVersion}{$platform}";

            $buildTarget = getRealTarget($platform);
            $buildPath = '';

            $unityArgs = join(' ', [
                '-nographics',
                '-ignoreCompilerErrors',
                "-logfile /root/logs/$platform.log",
                '-projectPath /root/UnityProject',
                "-buildTarget $buildTarget",
                '-executeMethod BuildScript.BuildProject',
                "-customBuildPath $buildPath",
                '-quit',
            ]);

            $exitCode = (new Process(['docker', "pull -q $image"]))->setTimeout(60)->wait();
            if ($exitCode !== 0) {
                throw new Exception("Unable to pull docker image \"$image\"!");
            }

            $process = new Process(['docker run', "--rm $mountArgs $image unity-editor $unityArgs"]);
            $process->setTimeout(3600); // Shouldn't take more than an hour.
            $exitCode = $process->wait();

            if ($exitCode !== 0) {
                throw new Exception("Build for \"$platform\" failed!");
            }
        }
    }
}
