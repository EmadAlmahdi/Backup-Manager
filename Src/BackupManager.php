<?php declare(strict_types=1);

namespace Temant\BackupManager;

use PDO;
use PDOException;
use RuntimeException;
use Temant\BackupManager\Enum\CleanupStrategyEnum;
use Temant\BackupManager\Exceptions\ConnectionErrorException;
use Temant\BackupManager\Factory\CleanupStrategyFactory;
use Temant\BackupManager\Interface\CleanupStrategyInterface;

/**
 * BackupManager handles database backup creation and cleanup strategies.
 *
 * This class is responsible for backing up a MySQL database to the specified storage path.
 * It also manages the cleanup of old backups using different cleanup strategies (e.g., by days, file count, or size).
 */
final readonly class BackupManager
{
    private ?CleanupStrategyInterface $cleanupStrategy;

    /**
     * Constructor for BackupManager.
     *
     * Initializes the backup manager with the given storage path and optional cleanup strategy.
     *
     * @param string $storagePath Path to the directory where backups will be stored.
     * @param null|CleanupStrategyEnum $cleanupStrategyType Type of cleanup strategy to use (optional).
     * @param null|int $variable Variable used for the cleanup strategy (optional).
     *                           - For MAX_DAYS_BASED: Number of days to keep backups.
     *                           - For MAX_FILES_BASED: Maximum number of files to keep.
     *                           - For MAX_SIZE_BASED: Maximum size in MB to keep.
     */
    public function __construct(
        private string $storagePath,
        private ?CleanupStrategyEnum $cleanupStrategyType = null,
        private ?int $variable = null
    ) {
        $this->ensureDirectoryExists($this->storagePath);
        $this->cleanupStrategy = CleanupStrategyFactory::create($this->cleanupStrategyType, $this->variable);
    }

    /**
     * Creates a backup of a MySQL database.
     *
     * This method runs a `mysqldump` command to back up the specified database and saves
     * it as an SQL file in the configured storage directory. The backup is named using 
     * the current timestamp.
     *
     * @param string $username MySQL username.
     * @param string $password MySQL password.
     * @param string $database Name of the database to back up. 
     * @param array<int, string> $options Additional mysqldump options for customization.
     *                                    Supported options include:
     *                                    - `'--single-transaction'`  (Recommended for InnoDB, avoids table locking)
     *                                    - `'--quick'`               (Dumps tables row by row, useful for large tables)
     *                                    - `'--lock-tables'`         (Locks tables for consistency, default for MyISAM)
     *                                    - `'--no-create-info'`      (Exports only data, no table structures)
     *                                    - `'--routines'`            (Includes stored procedures and functions)
     *                                    - `'--triggers'`            (Includes triggers in the backup)
     *                                    - `'--add-drop-database'`   (Adds DROP DATABASE before CREATE DATABASE)
     *                                    - `'--default-character-set=utf8mb4'` (Ensures UTF-8 encoding for compatibility)
     *                                    - `'--compress'`            (Compresses the output to save space)
     *                                    - `'--hex-blob'`            (Dumps binary data as hexadecimal values)
     *                                    - `'--complete-insert'`     (Includes column names in INSERT statements)
     *                                    - `'--skip-comments'`       (Excludes comments from the dump)
     *                                   - `'--skip-extended-insert'` (Uses one INSERT statement per row)
     *                                   - `'--skip-opt'`            (Disables all optimizations)
     *                                   - `'--extended-insert'`     (Uses multiple rows per INSERT statement for efficiency)
     *                                   - `'--no-data'`             (Dumps only the structure, no data)
     *
     * @throws ConnectionErrorException If the connection to the database fails.
     * 
     * @return bool True on success, false on failure.
     */
    public function backup(string $username, string $password, string $database, array $options = []): bool
    {
        $this->testCredentials($username, $password, $database);

        $backupFile = sprintf('%s/%s__%s.sql', rtrim($this->storagePath, '/'), $database, date('Y-m-d_H-i-s'));
        $command = $this->buildMysqldumpCommand($username, $password, $database, $backupFile, $options);

        $success = $this->executeCommand($command);

        if ($success && $this->cleanupStrategy) {
            $this->cleanupStrategy->cleanup($this->storagePath);
        }

        return $success;
    }

    /**
     * Tests the provided MySQL credentials.
     *
     * This method attempts to connect to the MySQL database using the provided credentials.
     * If the connection fails, an exception is thrown.
     *
     * @param string $username MySQL username.
     * @param string $password MySQL password.
     * @param string $database Name of the database to test the connection against.
     * 
     * @throws ConnectionErrorException If the connection fails.
     * 
     * @return void
     */
    private function testCredentials(string $username, string $password, string $database): void
    {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=$database", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new ConnectionErrorException($e->getMessage());
        }
    }

    /**
     * Ensures that the specified directory exists, creating it if necessary.
     *
     * This method checks if the storage directory exists and attempts to create it
     * if it does not. If the directory creation fails, a RuntimeException is thrown.
     *
     * @param string $path The path of the directory to check.
     * @return void
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new RuntimeException("Failed to create backup directory: {$path}");
        }
    }

    /**
     * Builds the mysqldump command string for creating a backup.
     *
     * This method generates a shell command that can be used to back up a MySQL database 
     * using `mysqldump` with the specified parameters.
     *
     * @param string $username MySQL username.
     * @param string $password MySQL password.
     * @param string $database Name of the database.
     * @param string $backupFile Path to the backup file.
     * @param array<int, string> $options Optional mysqldump options.
     * @return string The mysqldump command string.
     */
    private function buildMysqldumpCommand(string $username, string $password, string $database, string $backupFile, array $options = []): string
    {
        $escapedUser = escapeshellarg($username);
        $escapedDatabase = escapeshellarg($database);
        $escapedFile = escapeshellarg($backupFile);
        $escapedOptions = implode(' ', array_map('escapeshellcmd', $options));

        return sprintf(
            'mysqldump --user=%s --password=%s --databases %s %s > %s',
            $escapedUser,
            $password,
            $escapedDatabase,
            $escapedOptions,
            $escapedFile
        );
    }

    /**
     * Executes a shell command.
     *
     * This method runs a shell command and returns whether it was successful or not.
     *
     * @param string $command The command to execute.
     * @return bool Returns true if the command was successful, false otherwise.
     */
    private function executeCommand(string $command): bool
    {
        exec("$command 2>&1", $output, $result_code);
        return $result_code === 0;
    }
}