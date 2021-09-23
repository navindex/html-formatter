<?php

namespace Navindex\HtmlFormatter\Exceptions;

use RuntimeException;

class IndenterException extends RuntimeException
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param string $leftover
     *
     * @return void
     */
    public function __construct(string $message, string $leftover = null)
    {
        parent::__construct(empty($leftover)
            ? $message
            : $message . " Extra content left at the end: {$leftover}");
    }
}
