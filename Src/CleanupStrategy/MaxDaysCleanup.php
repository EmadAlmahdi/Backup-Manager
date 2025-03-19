<?php declare(strict_types=1);

namespace Temant\BackupManager\CleanupStrategy;

use Temant\BackupManager\Interface\CleanupStrategyInterface;

/**
 * Cleanup strategy that deletes backup files older than a specified number of days.
 *
 * This class checks all backup files in the specified storage path and deletes those
 * that have not been modified in the last specified number of days.
 */
final class MaxDaysCleanup implements CleanupStrategyInterface
{
    /**
     * Constructor for MaxDaysCleanup.
     *
     * @param int $maxDays The maximum number of days to retain backup files.
     *                     Files older than this value will be deleted.
     */
    public function __construct(
        private int $maxDays
    ) {
    }

    /**
     * Cleans up backup files that are older than the configured maximum days.
     *
     * This method scans the storage directory for `.sql` backup files. If a file's 
     * last modification time is older than the maximum allowed days, it will be deleted.
     *
     * @param string $storagePath Path to the backup storage directory.
     * @return void
     */
    public function cleanup(string $storagePath): void
    {
        $files = glob($storagePath . '/*.sql');
        if (!$files) {
            return;
        }

        $now = time();
        foreach ($files as $file) {
            if ($now - filemtime($file) > ($this->maxDays * 86400)) {
                unlink($file); // Delete the file if it's older than maxDays
            }
        }
    }
}