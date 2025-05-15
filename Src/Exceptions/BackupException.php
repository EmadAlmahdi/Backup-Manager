<?php declare(strict_types=1);

namespace Temant\BackupManager\Exceptions;

use Exception;
use Throwable;

class BackupException extends Exception implements Throwable
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
