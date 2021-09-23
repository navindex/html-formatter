<?php

namespace Navindex\HtmlFormatter;

use Navindex\HtmlFormatter\Helper;
use Navindex\HtmlFormatter\Logger;
use Navindex\HtmlFormatter\Pattern;

/**
 * HTML content.
 */
class HtmlContent
{
    const
        KEEP_INDENT     = 0,
        DECREASE_INDENT = 1,
        INCREASE_INDENT = 2,
        DISCARD         = 3;

    const
        PRE       = 'pre',
        ATTRIBUTE = 'attr',
        CDATA     = 'cdata',
        INLINE    = 'inline';

    /**
     * HTML content.
     *
     * @var string
     */
    protected $content;

    /**
     * Configuration settings.
     *
     * @var array <string, mixed>
     */
    protected $options = [];

    /**
     * Temporary storage of content parts.
     *
     * @var array[]
     */
    protected $parts = [];

    /**
     * Rule descriptions for logging.
     *
     * @var string[]
     */
    protected $ruleDesc = [
        'KEEP INDENT',
        'DECREASE INDENT',
        'INCREASE INDENT',
        'DISCARD',
    ];

    /**
     * Regex patterns and instructions.
     *
     * @var array[]
     */
    protected $patterns = [
        Pattern::IS_BLOCK => [
            'rule' => self::KEEP_INDENT,
            'name' => 'BLOCK TAG',
        ],
        Pattern::IS_DOCTYPE => [
            'rule' => self::KEEP_INDENT,
            'name' => 'DOCTYPE',
        ],
        Pattern::IS_MARKER => [
            'rule' => self::KEEP_INDENT,
            'name' => 'MARKER',
        ],
        Pattern::IS_OPENING => [
            'rule' => self::INCREASE_INDENT,
            'name' => 'OPENING TAG',
        ],
        Pattern::IS_CLOSING => [
            'rule' => self::DECREASE_INDENT,
            'name' => 'CLOSING TAG',
        ],
        Pattern::IS_EMPTY_CLOSING => [
            'rule' => self::DECREASE_INDENT,
            'name' => 'CLOSING EMPTY TAG',
        ],
        Pattern::IS_WHITESPACE => [
            'rule' => self::DISCARD,
            'name' => 'WHITESPACE',
        ],
        Pattern::IS_TEXT => [
            'rule' => self::KEEP_INDENT,
            'name' => 'TEXT',
        ],
    ];

    /**
     * Logger instance.
     *
     * @var \Navindex\HtmlFormatter\Logger
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param string                $content Text to be processed
     * @param array <string, mixed> $options Configuration settings
     *
     * @return void
     */
    public function __construct(string $content, array $options)
    {
        $this->content = $content;
        $this->options = $options;
        $this->setPatterns();
    }

    /**
     * Sets the patters used in the indentation process.
     *
     * @return void
     */
    protected function setPatterns()
    {
        $pattern = sprintf(Pattern::IS_EMPTY_OPENING, implode('|', $this->options['empty_tags'] ?? []));
        $this->patterns[$pattern] = [
            'rule' => static::KEEP_INDENT,
            'name' => 'CLOSING EMPTY TAG',
        ];
    }

    /**
     * Retrieves the HTML content.
     *
     * @return string
     */
    public function get(): string
    {
        return trim($this->content);
    }

    /**
     * Retrieves the HTML content.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->get();
    }

    /**
     * Removes parts of the content by pattern.
     *
     * @param string        $type     Part type to remove
     * @param string        $pattern  Regex pattern
     * @param callable|null $callback Process the matches
     *
     * @return self
     */
    public function remove(string $type, string $pattern, callable $callback = null): self
    {
        $this->parts[$type] = [];
        $placeholder = Helper::placeholder($type);
        $this->removeCore($type, $placeholder, $pattern, $callback);

        return $this;
    }

    /**
     * Removes parts of the content by pattern in multiple steps.
     *
     * @param string        $type     Part type to remove
     * @param string        $pattern  Regex pattern
     * @param callable|null $callback Process the matches
     *
     * @return self
     */
    public function deepRemove(string $type, string $pattern, callable $callback = null): self
    {
        $this->parts[$type] = [];
        $placeholder = Helper::placeholder($type);

        do {
            $original = $this->content;
            $offset = count($this->parts[$type]);
            $this->removeCore($type, $placeholder, $pattern, $callback, $offset);
        } while ($original !== $this->content);

        return $this;
    }

    /**
     * Core function to remove parts.
     *
     * @param string        $type        Part type to remove
     * @param string        $placeholder Part type to remove
     * @param string        $pattern     Regex pattern
     * @param callable|null $callback    Process the matches
     * @param int|null      $offset      Index offset
     *
     * @return void
     */
    protected function removeCore(
        string $type,
        string $placeholder,
        string $pattern,
        callable $callback = null,
        ?int $offset = 0
    ) {
        if (preg_match_all($pattern, $this->content, $matches)) {
            foreach ($matches[0] as $index => $part) {
                // Replace the first occurrence only
                $pos = strpos($this->content, $part);
                if (false !== $pos) {
                    $this->content = substr_replace(
                        $this->content,
                        sprintf($placeholder, $index + $offset),
                        $pos,
                        strlen($part)
                    );
                }
            }
            $matches = is_null($callback) ? $matches : $callback($matches);
            $this->parts[$type] = array_merge($this->parts[$type], $matches[0]);
        }
    }

    /**
     * Wrapper to remove preformatted elements.
     *
     * @return self
     */
    public function removePreformats(): self
    {
        $pattern = sprintf(Pattern::PRE, implode('|', $this->options['keep_format']));

        return $this->remove(static::PRE, $pattern);
    }

    /**
     * Removes the HTML attributes.
     *
     * @return self
     */
    public function removeAttributes(): self
    {
        return $this->remove(static::ATTRIBUTE, Pattern::ATTRIBUTE, function (array $matches) {
            foreach ($matches[0] as $index => &$value) {
                // Remove whitespace around the equal sign


                // Trim attributes
                if ($this->options['attribute_trim'] ?? false) {
                    $attrValue = trim($matches[3][$index]);
                    $value = str_replace($matches[3][$index], $attrValue, $value);
                }
            }

            return $matches;
        });
    }

    /**
     * Wrapper to remove CDATA.
     *
     * @return self
     */
    public function removeCdata(): self
    {
        return $this->remove(static::CDATA, Pattern::CDATA);
    }

    /**
     * Removes the inline elements.
     *
     * @return self
     */
    public function removeInlines(): self
    {
        $pattern = sprintf(Pattern::INLINE, implode('|', $this->options['inline_tags']));

        return $this->deepRemove(static::INLINE, $pattern, function (array $matches) {
            foreach ($matches[0] as $index => &$value) {
                // $attrValue = trim($matches[3][$index]);
                // $value = str_replace($matches[3][$index], $attrValue, $value);
            }

            return $matches;
        });
    }

    /**
     * Restores the content parts.
     *
     * @param string $type Part type to restore
     *
     * @return self
     */
    public function restore(string $type): self
    {
        $placeholder = Helper::placeholder($type);
        $parts = $this->parts[$type];

        // Reverse iteration without index change
        for (end($parts); ($index = key($parts)) !== null; prev($parts)) {
            $this->content = str_replace(sprintf($placeholder, $index), current($parts), $this->content);
        }
        unset($this->parts[$type]);

        return $this;
    }

    /**
     * Wrapper to restore previously removed preformatted elements.
     *
     * @return self
     */
    public function restorePreformats(): self
    {
        return $this->restore(static::PRE);
    }

    /**
     * Wrapper to restore previously removed attributes.
     *
     * @return self
     */
    public function restoreAttributes(): self
    {
        return $this->restore(static::ATTRIBUTE);
    }

    /**
     * Wrapper to restore previously removed CDATA.
     *
     * @return self
     */
    public function restoreCdata(): self
    {
        return $this->restore(static::CDATA);
    }

    /**
     * Wrapper to restore previously removed attributes.
     *
     * @return self
     */
    public function restoreInlines(): self
    {
        return $this->restore(static::INLINE);
    }

    /**
     * Replaces all whitespace with a single space character.
     *
     * @return self
     */
    public function removeExtraWhitespace(): self
    {
        $this->content = preg_replace(Pattern::WHITESPACE, ' ', $this->content);

        return $this;
    }

    public function indent(): self
    {
        $useLog = isset($this->logger);
        $tab = $this->options['tab'] ?? '';
        $subject = $this->content;
        $output = '';
        $nextPos = 0;

        do {
            $pos = $nextPos;
            $match = false;

            foreach ($this->patterns as $pattern => $action) {
                $rule = $action['rule'];
                $match = preg_match($pattern, $subject, $matches);
                if (1 === $match) {
                    if ($useLog) {
                        $this->logger->push($this->ruleDesc[$rule], $action['name'], $subject, $matches[0]);
                    }

                    $subject = mb_substr($subject, mb_strlen($matches[0]));

                    switch ($rule) {
                        case static::DISCARD:
                            break 2;
                        case static::INCREASE_INDENT:
                            $nextPos++;
                            break;
                        case static::DECREASE_INDENT:
                            $nextPos--;
                            $pos--;
                            break;
                    }

                    $pos = $pos > 0 ? $pos : 0;
                    $output .= str_repeat($tab, $pos) . $matches[0] . "\n";
                    $match = false;
                }
            }
        } while ($match);

        $this->content = $output;

        return $this;
    }

    /**
     * Retrieves the log.
     *
     * @return null|array[]
     */
    public function getLog(): ?array
    {
        return isset($this->logger) ? $this->logger->get() : null;
    }

    /**
     * Enables internal logging.
     *
     * @param bool|null $useIt
     *
     * @return self
     */
    public function useLog(?bool $useIt = true): self
    {
        $this->logger = $useIt ? new Logger() : null;

        return $this;
    }

    /**
     * Apply the callback if the value is truthy.
     *
     * @param bool     $value
     * @param callable $callback
     *
     * @return static
     */
    public function when(bool $value, callable $callback)
    {
        if ($value) {
            $callback($this);
        }

        return $this;
    }
}
