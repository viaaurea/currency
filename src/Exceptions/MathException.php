<?php

declare(strict_types=1);

namespace VA\Currency\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when a runtime math error occurs / would occur.
 */
class MathException extends RuntimeException
{
    public function __construct($message = null, $code = null, Throwable $previous = null)
    {
        parent::__construct($message ?? '', $code ?? 0, $previous);
    }
}
