<?php

namespace Navindex\HtmlFormatter;

use Navindex\HtmlFormatter\Exceptions\IndentException;
use Navindex\HtmlFormatter\Helper;
use Navindex\HtmlFormatter\Logger;
use Navindex\HtmlFormatter\Pattern;
use Navindex\SimpleConfig\Config;

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
        [
            'pattern' => Pattern::IS_BLOCK,
            'rule'    => self::KEEP_INDENT,
            'name'    => 'BLOCK TAG',
        ], [
            'pattern' => Pattern::IS_DOCTYPE,
            'rule'    => self::KEEP_INDENT,
            'name'    => 'DOCTYPE',
        ], [
            'pattern' => null,
            'rule'    => self::KEEP_INDENT,
            'name'    => 'EMPTY TAG',
        ], [
            'pattern' => Pattern::IS_MARKER,
            'rule'    => self::KEEP_INDENT,
            'name'    => 'MARKER',
        ], [
            'pattern' => Pattern::IS_OPENING,
            'rule'    => self::INCREASE_INDENT,
            'name'    => 'OPENING TAG',
        ], [
            'pattern' => Pattern::IS_CLOSING,
            'rule'    => self::DECREASE_INDENT,
            'name'    => 'CLOSING TAG',
        ], [
            'pattern' => Pattern::IS_EMPTY_CLOSING,
            'rule'    => self::DECREASE_INDENT,
            'name'    => 'CLOSING EMPTY TAG',
        ], [
            'pattern' => Pattern::IS_WHITESPACE,
            'rule'    => self::DISCARD,
            'name'    => 'WHITESPACE',
        ], [
            'pattern' => Pattern::IS_TEXT,
            'rule'    => self::KEEP_INDENT,
            'name'    => 'TEXT',
        ],
    ];

    /**
     * HTML content.
     *
     * @var string
     */
    protected $content = '';

    /**
     * Temporary storage of content parts.
     *
     * @var array[]
     */
    protected $parts = [];

    /**
     * Configuration settings.
     *
     * @var \Navindex\SimpleConfig\Config
     */
    protected $config;

    /**
     * Logger instance.
     *
     * @var \Navindex\HtmlFormatter\Logger|null
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param null|string                   $content Text to be processed
     * @param \Navindex\SimpleConfig\Config $config  Configuration settings
     *
     * @return void
     */
    public function __construct(?string $content, Config $config)
    {
        $this->content = $content ?? '';
        $this->config = $config;
        $this->setPatterns();
    }

    /**
     * Sets the patters used in the indentation process.
     *
     * @return void
     */
    protected function setPatterns()
    {
        $tags = implode('|', $this->config->get('self-closing.tag', []));
        $this->patterns[2]['pattern'] = sprintf(Pattern::IS_EMPTY_OPENING, $tags);
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
    public function removeFormatted(): self
    {
        $tags = array_keys($this->config->get('formatted.tag', []));
        $pattern = sprintf(Pattern::PRE, implode('|', $tags));

        return $this->remove(static::PRE, $pattern, function (array $matches) {
            $cleanupEmpty = $this->config->get('formatted.cleanup-empty', false);
            $openingBreak = $this->config->get('formatted.opening-break', false);
            $closingBreak = $this->config->get('formatted.closing-break', false);
            $trim = $this->config->get('formatted.trim', false);

            foreach ($matches[0] as $index => &$value) {
                // Process formatted elements
                $tag = $matches[1][$index];
                $content = $originalContent = $matches[2][$index];

                if ($this->config->get("formatted.tag.{$tag}.trim", $trim)) {
                    $content = trim($content);
                }

                if ($this->config->get("formatted.tag.{$tag}.opening-break", $openingBreak)) {
                    $content = Helper::start($content, "\n");
                }

                if ($this->config->get("formatted.tag.{$tag}.closing-break", $closingBreak)) {
                    $content = Helper::finish($content, "\n");
                }

                if ($this->config->get("formatted.tag.{$tag}.cleanup-empty", $cleanupEmpty)) {
                    $content = empty(trim($content)) ? '' : $content;
                }

                if ($content !== $originalContent) {
                    $value = str_replace($originalContent, $content, $value);
                }
            }

            return $matches;
        });
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

                if ($this->config->get('attributes.cleanup', false)) {
                    $attrValue = preg_replace(Pattern::WHITESPACE, ' ', $matches[3][$index]) ?? $matches[3][$index];
                    $value = str_replace($matches[3][$index], $attrValue, $value);
                    $matches[3][$index] = $attrValue;
                }

                if ($this->config->get('attributes.trim', false)) {
                    $attrValue = trim($matches[3][$index]);
                    $value = str_replace($matches[3][$index], $attrValue, $value);
                    $matches[3][$index] = $attrValue;
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
        return $this->remove(static::CDATA, Pattern::CDATA, function (array $matches) {
            foreach ($matches[0] as $index => &$value) {
                if ($this->config->get('cdata.cleanup', false)) {
                    $content = preg_replace(Pattern::WHITESPACE, ' ', $matches[1][$index]) ?? $matches[1][$index];
                    $value = str_replace($matches[1][$index], $content, $value);
                    $matches[1][$index] = $content;
                }

                if ($this->config->get('cdata.trim', false)) {
                    $content = trim($matches[1][$index]);
                    $value = str_replace($matches[1][$index], $content, $value);
                    $matches[1][$index] = $content;
                }
            }

            return $matches;
        });
    }

    /**
     * Removes the inline elements.
     *
     * @return self
     */
    public function removeInlines(): self
    {
        $pattern = sprintf(Pattern::INLINE, implode('|', $this->config->get('inline.tag', [])));

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
    public function restoreFormatted(): self
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
        $this->content = preg_replace(Pattern::WHITESPACE, ' ', $this->content) ?? $this->content;

        return $this;
    }

    /**
     * Content indenting.
     *
     * @throws \Navindex\HtmlFormatter\Exceptions\IndentException
     *
     * @return self
     */
    public function indent(): self
    {
        $subject = $this->content;
        $output = '';
        $match = false;
        $pos = 0;

        do {
            foreach ($this->patterns as $action) {
                $match = preg_match($action['pattern'], $subject, $matches);
                if (1 === $match) {
                    $rule = $action['rule'];

                    if ($this->logger) {
                        $this->logger->push($this->ruleDesc[$rule], $action['name'], $subject, $matches[0]);
                    }

                    $subject = mb_substr($subject, mb_strlen($matches[0]));
                    $output .= $this->indentAction($pos, $rule, $matches[0]);

                    break;
                }
            }
        } while ($match);

        if ('' !== $subject) {
            throw new IndentException('Unable to create the indented content.', $subject);
        }

        $this->content = $output;

        return $this;
    }

    /**
     * Indenter core.
     *
     * @param integer $position
     * @param integer $rule
     * @param string  $match
     *
     * @return string
     */
    protected function indentAction(int &$position, int $rule, string $match): string
    {
        if (static::DISCARD === $rule) {
            return '';
        }

        $tab = $this->config->get('tab', '');
        $lineBreak = $this->config->get('line-break', PHP_EOL);

        switch ($rule) {
            case static::INCREASE_INDENT:
                $output = str_repeat($tab, $position++) . $match . $lineBreak;
                break;
            case static::DECREASE_INDENT:
                $position = --$position < 0 ? 0 : $position;
                $output = str_repeat($tab, $position) . $match . $lineBreak;
                break;
            default:
                $output = str_repeat($tab, $position) . $match . $lineBreak;
                break;
        }

        return $output;
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
