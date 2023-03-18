<?php

namespace Utils;

use Exception;
use InvalidArgumentException;

/**
 * Utility class for miscellaneous file operations.
 */
class FileUtils
{
    /**
     * Recursively remove a directory by removing all files and folders within it.
     *
     * @param string $dir directory to remove.
     * @return void
     * @throws Exception
     */
    public static function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException("\"$dir\" must be a directory");
        }

        $files = glob("$dir/*") ?: [];
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::removeDirectory($file);
            } else {
                unlink($file);
            }
        }

        if (!rmdir($dir)) {
            throw new Exception("couldn't remove directory \"$dir\"");
        }
    }
}
