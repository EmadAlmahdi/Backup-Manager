<?php declare(strict_types=1);

namespace Temant\BackupManager\CleanupStrategy;

use Temant\BackupManager\Interface\CleanupStrategyInterface;

/**
 * Cleanup strategy that limits the total size of backup files in a directory.
 *
 * This strategy ensures that the total size of all backup files does not exceed the 
 * specified limit. Older files are deleted to free up space when the total size exceeds 
 * the limit.
 */
final class MaxSizeCleanup implements CleanupStrategyInterface
{
    /**
     * Constructor for MaxSizeCleanup.
     *
     * @param int $maxSizeMB The maximum size (in MB) of the backup files to retain.
     *                       If the total size exceeds this value, older files will be deleted.
     */
    public function __construct(
        private int $maxSizeMB
    ) {
    }

    /**
     * Cleans up backup files by deleting the oldest files until the total size 
     * of the remaining files is within the configured size limit.
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

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a)); // Sort by newest first
        $totalSize = array_sum(array_map('filesize', $files));

        foreach ($files as $file) {
            if ($totalSize <= $this->maxSizeMB * 1024 * 1024) {
                break; // Stop deleting files when the total size is within the limit
            }
            $totalSize -= filesize($file);
            unlink($file); // Delete the file to reduce total size
        }
    }
}
