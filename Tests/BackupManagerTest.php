<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Temant\BackupManager\BackupManager;
use Temant\BackupManager\Enum\CleanupStrategyEnum;
use Temant\BackupManager\Exceptions\BackupException;

class BackupManagerTest extends TestCase
{
    private string $storagePath = __DIR__ . '/BackupTest';

    public function testBackupManagerSuccessConstruct(): void
    {
        $backupManager = new BackupManager(
            $this->storagePath,
            CleanupStrategyEnum::MAX_FILES_BASED,
            5
        );

        $this->assertInstanceOf(BackupManager::class, $backupManager);
    }

    public function testBackupManagerFailedConstruct(): void
    {
        $this->expectException(BackupException::class);
        new BackupManager(
            "some:path:that:is:invalid?",
            CleanupStrategyEnum::MAX_FILES_BASED,
            5
        );
    }

    public function testBackupManagerSuccessBackup(): void
    {
        $backupManager = new BackupManager(
            $this->storagePath,
            CleanupStrategyEnum::MAX_FILES_BASED,
            5
        );

        $backupPath = $backupManager->backup(
            "intradb",
            "Proto!728agt22Ws",
            "intradb"
        );

        $this->assertStringContainsString($this->storagePath, $backupPath);
    }

    public function testBackupManagerFailedBackup(): void
    {
        $this->expectException(BackupException::class);

        $backupManager = new BackupManager(
            $this->storagePath,
            CleanupStrategyEnum::MAX_FILES_BASED,
            5
        );

        $result = $backupManager->backup(
            "intradb",
            "SIMULATE ERROR PASSOWRD FOR TESTING",
            "intradb"
        );
    }
}