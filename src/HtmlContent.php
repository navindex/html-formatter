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
        DISCARD_LINE    = 3;

    const
        SCRIPT    = 'script',
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
     * @var array
     */
    protected $options = [];

    /**
     * Temporary storage of content parts.
     *
     * @var array[]
     */
    protected $parts = [];

    /**
     * Rules for logging.
     *
     * @var string[]
     */
    protected $rules = [
        'KEEP INDENT',
        'DECREASE INDENT',
        'INCREASE INDENT',
        'DISCARD',
    ];

    /**
     * Regex patterns and instructions.
     *
     * @var array
     */
    protected $patterns = [];

    /**
     * Logger instance.
     *
     * @var \Navindex\HtmlFormatter\Logger
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param string $content Text to be processed
     * @param array  $options Configuration settings
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
        $emptyTags = $this->options['empty_tags'] ?? [];

        $this->patterns = [
            Pattern::IS_BLOCK => static::KEEP_INDENT, // block tag
            Pattern::IS_DOCTYPE => static::KEEP_INDENT, // DOCTYPE
            Pattern::IS_MARKER => static::KEEP_INDENT, // earlier replaced node
            sprintf(Pattern::IS_EMPTY_OPENING, implode('|', $emptyTags)) => static::KEEP_INDENT, // tag with implied closing
            Pattern::IS_OPENING => static::INCREASE_INDENT, // opening tag
            Pattern::IS_CLOSING => static::DECREASE_INDENT, // closing tag
            Pattern::IS_EMPTY_CLOSING => static::DECREASE_INDENT, // self-closing tag
            Pattern::IS_WHITESPACE => static::DISCARD_LINE, // whitespace
            Pattern::IS_TEXT => static::KEEP_INDENT, // text node
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
        int $offset = 0
    ) {
        if (preg_match_all($pattern, $this->content, $matches)) {
            foreach ($matches[0] as $index => $part) {
                $this->content = str_replace($part, sprintf($placeholder, $index + $offset), $this->content);
            }
            $matches = is_null($callback) ? $matches : $callback($matches);
            $this->parts[$type] = array_merge($this->parts[$type], $matches[0]);
        }
    }

    /**
     * Wrapper to remove scripts.
     *
     * @return self
     */
    public function removeScripts(): self
    {
        return $this->remove(static::SCRIPT, Pattern::SCRIPT);
    }

    /**
     * Wrapper to remove preformatted elements.
     *
     * @return self
     */
    public function removePreformats(): self
    {
        return $this->remove(static::PRE, Pattern::PRE);
    }

    /**
     * Removes the HTML attributes.
     *
     * @return self
     */
    public function removeAttributes(): self
    {
        if ($this->options['attribute_trim'] ?? false) {
            return $this->remove(static::ATTRIBUTE, Pattern::ATTRIBUTE, function (array $matches) {
                foreach ($matches[0] as $index => &$value) {
                    $attrValue = trim($matches[3][$index]);
                    $value = str_replace($matches[3][$index], $attrValue, $value);
                }

                return $matches;
            });
        }

        return $this->remove(static::ATTRIBUTE, Pattern::ATTRIBUTE);
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
     * Wrapper to restore previously removed scripts.
     *
     * @return self
     */
    public function restoreScripts(): self
    {
        return $this->restore(static::SCRIPT);
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
        $this->content = preg_replace('/(\s+)/', ' ', $this->content);

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

            foreach ($this->patterns as $pattern => $rule) {
                $match = preg_match($pattern, $subject, $matches);
                if (1 === $match) {
                    if ($useLog) {
                        $this->logger->push($this->rules[$rule], $pattern, $subject, $matches[0]);
                    }

                    $subject = mb_substr($subject, mb_strlen($matches[0]));

                    switch ($rule) {
                        case static::DISCARD_LINE:
                            break 2;
                        case static::INCREASE_INDENT:
                            $nextPos++;
                            break;
                        case static::DECREASE_INDENT:
                            $nextPos--;
                            $pos = $pos > 0 ? --$pos : 0;
                            break;
                    }

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
     * @return array
     */
    public function getLog(): array
    {
        return isset($this->logger) ? $this->logger->get() : [];
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
        $this->useLog = $useIt ? new Logger() : null;

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
