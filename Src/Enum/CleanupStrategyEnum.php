<?php declare(strict_types=1);

namespace Temant\BackupManager\Enum;

enum CleanupStrategyEnum: string
{
    case MAX_DAYS_BASED = 'max_days_based';

    case MAX_FILES_BASED = 'max_files_based';

    case MAX_SIZE_BASED = 'max_size_based';
}