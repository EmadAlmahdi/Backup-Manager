<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Temant\BackupManager\CleanupStrategy\MaxDaysCleanup;
use Temant\BackupManager\CleanupStrategy\MaxFilesCleanup;
use Temant\BackupManager\CleanupStrategy\MaxSizeCleanup;

final class CleanupStrategyTest extends TestCase
{
    private string $testBackupPath;

    protected function setUp(): void
    {
        $this->testBackupPath = __DIR__ . '/BackupTest';

        if (!is_dir($this->testBackupPath)) {
            mkdir($this->testBackupPath, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $files = glob("{$this->testBackupPath}/*.sql");

        if (is_array($files)) {
            array_map('unlink', $files);
        }

        rmdir($this->testBackupPath);
    }

    public function testMaxFilesCleanup(): void
    {
        $cleanup = new MaxFilesCleanup(3);
        $cleanup->cleanup($this->testBackupPath);

        for ($i = 0; $i < 5; $i++) {
            file_put_contents("{$this->testBackupPath}/backup_$i.sql", "dummy data");
            touch("{$this->testBackupPath}/backup_$i.sql", time() - ($i * 60));
        }

        $cleanup->cleanup($this->testBackupPath);

        $remainingFiles = glob("{$this->testBackupPath}/*.sql");
        $this->assertCount(3, (array) $remainingFiles);
    }

    public function testMaxDaysCleanup(): void
    {
        $cleanup = new MaxDaysCleanup(2);
        $cleanup->cleanup($this->testBackupPath);

        file_put_contents("{$this->testBackupPath}/old_backup.sql", "dummy data");
        touch("{$this->testBackupPath}/old_backup.sql", time() - 3 * 86400);

        $cleanup->cleanup($this->testBackupPath);

        $this->assertFileDoesNotExist("{$this->testBackupPath}/old_backup.sql");
    }

    public function testMaxSizeCleanup(): void
    {
        $cleanup = new MaxSizeCleanup(2);
        $cleanup->cleanup($this->testBackupPath);

        file_put_contents("{$this->testBackupPath}/big_backup.sql", str_repeat('A', 3 * 1024 * 1024));
        file_put_contents("{$this->testBackupPath}/small_backup.sql", "dummy data");

        $cleanup->cleanup($this->testBackupPath);

        $this->assertFileDoesNotExist("{$this->testBackupPath}/big_backup.sql");
        $this->assertFileExists("{$this->testBackupPath}/small_backup.sql");
    }
}