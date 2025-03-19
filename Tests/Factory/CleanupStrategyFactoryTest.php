<?php declare(strict_types=1);

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Temant\BackupManager\CleanupStrategy\MaxDaysCleanup;
use Temant\BackupManager\CleanupStrategy\MaxFilesCleanup;
use Temant\BackupManager\CleanupStrategy\MaxSizeCleanup;
use Temant\BackupManager\Enum\CleanupStrategyEnum;
use Temant\BackupManager\Factory\CleanupStrategyFactory;

final class CleanupStrategyFactoryTest extends TestCase
{
    #[TestWith([CleanupStrategyEnum::MAX_FILES_BASED, 5, MaxFilesCleanup::class])]
    #[TestWith([CleanupStrategyEnum::MAX_DAYS_BASED, 5, MaxDaysCleanup::class])]
    #[TestWith([CleanupStrategyEnum::MAX_SIZE_BASED, 5, MaxSizeCleanup::class])]
    public function testSuccessCreate(CleanupStrategyEnum $startegy, int $variable, string $expected): void
    {
        if (!class_exists($expected)) {
            return;
        }

        $this->assertInstanceOf($expected, CleanupStrategyFactory::create($startegy, $variable));
    }

    #[TestWith([CleanupStrategyEnum::MAX_FILES_BASED, null, "Variable is required for cleanup strategy type"])]
    #[TestWith([null, 5, "Please provide a valid cleanup strategy type."])]
    public function testFailureCreate(?CleanupStrategyEnum $startegy, ?int $variable, string $message): void
    {
        $this->expectExceptionMessage($message);
        $this->expectException(RuntimeException::class);

        CleanupStrategyFactory::create($startegy, $variable);
    }
}