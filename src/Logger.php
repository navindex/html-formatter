<?php

namespace Navindex\HtmlFormatter;

/**
 * Very simple logger.
 */
class Logger
{
    /**
     * Log entries.
     *
     * @var array[]
     */
    protected $log = [];

    /**
     * Retrieves the log.
     *
     * @return array[]
     */
    public function get(): array
    {
        return $this->log;
    }

    /**
     * Clears the log.
     *
     * @return self
     */
    public function clearLog(): self
    {
        $this->log = [];

        return $this;
    }

    /**
     * Creates a new log entry.
     *
     * @param mixed $rule
     * @param mixed $subject
     * @param mixed $matches
     *
     * @return self
     */
    public function push($rule, $subject, $matches): self
    {
        $this->log[] = [
            'rule'    => $rule,
            'subject' => $subject,
            'matches' => $matches,
        ];

        return $this;
    }
}
