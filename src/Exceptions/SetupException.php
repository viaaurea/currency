<?php

declare(strict_types=1);

namespace VA\Currency\Exceptions;

use LogicException;
use Throwable;

/**
 * Exception thrown when the setup or configuration of the currency service or its parts is not valid.
 */
class SetupException extends LogicException
{
    public function __construct($message = null, $code = null, Throwable $previous = null)
    {
        parent::__construct($message ?? '', $code ?? 0, $previous);
    }
}
