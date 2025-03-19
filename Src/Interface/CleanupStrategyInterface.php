<?php declare(strict_types=1);

namespace Temant\BackupManager\Interface;

/**
 * Interface for defining a cleanup strategy.
 *
 * Any class that implements this interface can be used as a cleanup strategy
 * for managing old backup files in a backup manager.
 */
interface CleanupStrategyInterface
{
    /**
     * Cleans up old backup files based on the strategy.
     *
     * This method is responsible for determining which files to delete
     * based on the implemented cleanup strategy.
     *
     * @param string $storagePath Path to the backup storage directory.
     * @return void
     */
    public function cleanup(string $storagePath): void;
}
