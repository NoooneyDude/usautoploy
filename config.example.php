<?php

$config = [
    'LOGGER' => [
        'FILEPATH' => '/var/log/usautoploy.log',
    ],
    'GITHUB' => [
        'WEBHOOK_SECRET' => 'clownMime123',
        'PERSONAL_ACCESS_TOKEN' => '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',
    ],
    'DAEMON' => [
        'QUEUE_FILEPATH' => __DIR__ . DIRECTORY_SEPARATOR . 'queue.json',
    ],
    'UNITY' => [
        'LICENSE_DIRECTORY' => '',
        'LICENSE_FILENAME' => 'Unity_lic.ulf',
        'VERSION' => '2021.3.16f1',
    ],
    'GIT' => [
        'REMOTE_URL' => 'https://github.com/NoooneyDude/unitystation',
        'LOCAL_DIRECTORY' => 'unitystation', // Relative to current working directory.
    ],
    'BUILDER' => [
        'DEFAULT_TARGETS' => [
            'StandaloneLinux64',
            'StandaloneWindows64',
            'StandaloneOSX',
        ],
        'OUTPUT_DIRECTORY' => 'builds', // Relative to current working directory.
        'RELEASE_BUILD' => [
            'FORK_NAME' => 'UnityStation',
        ],
        'STAGING_BUILD' => [
            'FORK_NAME' => 'UnityStationStaging',
        ],
        'DEVELOP_BUILD' => [
            'FORK_NAME' => 'UnityStationDevelop',
        ],
        'PR_BUILD' => [
            'FORK_NAME' => 'UnityStationPR',
        ],
    ],
    'CDN' => [
        'DOWNLOAD_URL' => 'https://unitystationfile.b-cdn.net/%s/%s/%s.zip',
    ],
];

return $config;
