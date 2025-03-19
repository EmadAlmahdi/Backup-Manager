<?php declare(strict_types=1);

namespace Temant\BackupManager\CleanupStrategy;

use Temant\BackupManager\Interface\CleanupStrategyInterface;

/**
 * Cleanup strategy that limits the number of backup files in a directory.
 *
 * This strategy ensures that only the most recent backup files are retained. 
 * If the number of files exceeds the configured limit, the oldest ones are deleted.
 */
final readonly class MaxFilesCleanup implements CleanupStrategyInterface
{
    /**
     * Constructor for MaxFilesCleanup.
     *
     * @param int $maxFiles The maximum number of backup files to keep in the storage directory.
     *                      If the number of files exceeds this value, the oldest files will be deleted.
     */
    public function __construct(
        private int $maxFiles
    ) {
    }

    /**
     * Cleans up old backup files in the storage directory by deleting the oldest files 
     * when the number of files exceeds the configured maximum.
     *
     * @param string $storagePath Path to the backup storage directory.
     * @return void
     */
    public function cleanup(string $storagePath): void
    {
        $files = glob($storagePath . '/*.sql');

        if (!$files || count($files) <= $this->maxFiles) {
            return; // No cleanup needed if the number of files is within the limit
        }

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a)); // Sort files by modification time (newest first)

        // Delete the oldest files exceeding the maxFiles limit
        array_map('unlink', array_slice($files, $this->maxFiles));
    }
}