<?php declare(strict_types=1);

namespace Temant\BackupManager\Factory;

use Temant\BackupManager\CleanupStrategy\MaxDaysCleanup;
use Temant\BackupManager\CleanupStrategy\MaxFilesCleanup;
use Temant\BackupManager\CleanupStrategy\MaxSizeCleanup;
use Temant\BackupManager\Enum\CleanupStrategyEnum;
use Temant\BackupManager\Exceptions\BackupException;
use Temant\BackupManager\Interface\CleanupStrategyInterface;

final class CleanupStrategyFactory
{
    /**
     * Creates a cleanup strategy based on the provided type and variable.
     *
     * @param null|CleanupStrategyEnum $cleanupStrategyType The type of cleanup strategy to create.
     * @param null|int $variable The variable associated with the cleanup strategy.
     * 
     * @return CleanupStrategyInterface|null The created cleanup strategy or null if invalid type.
     */
    public static function create(?CleanupStrategyEnum $cleanupStrategyType = null, ?int $variable = null): ?CleanupStrategyInterface
    {
        if (isset($cleanupStrategyType) && $variable === null) {
            throw new BackupException("Variable is required for cleanup strategy type {$cleanupStrategyType->name}.");
        }

        if (!isset($cleanupStrategyType) && $variable !== null) {
            throw new BackupException("Please provide a valid cleanup strategy type.");
        }

        return match ($cleanupStrategyType) {
            CleanupStrategyEnum::MAX_FILES_BASED => new MaxFilesCleanup(intval($variable)),
            CleanupStrategyEnum::MAX_DAYS_BASED => new MaxDaysCleanup(intval($variable)),
            CleanupStrategyEnum::MAX_SIZE_BASED => new MaxSizeCleanup(intval($variable)),
            default => null,
        };
    }
}