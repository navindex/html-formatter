<?php

namespace Navindex\HtmlFormatter;

use Navindex\HtmlFormatter\Exceptions\IndentException;
use Navindex\SimpleConfig\Config;

/**
 * HTML content.
 */
class Content
{
    const
        KEEP_INDENT = 0;
    const
        DECREASE_INDENT = 1;
    const
        INCREASE_INDENT = 2;
    const
        DISCARD = 3;

    const
        PRE = 'pre';
    const
        ATTRIBUTE = 'attr';
    const
        CDATA = 'cdata';
    const
        INLINE = 'inline';

    /**
     * Regex patterns and instructions.
     *
     * @var array[]
     */
    protected $patterns = [
        [
            'pattern' => Pattern::IS_WHITESPACE,
            'rule'    => self::DISCARD,
            'name'    => 'WHITESPACE: discard',
        ], [
            'pattern' => Pattern::IS_MARKER,
            'rule'    => self::KEEP_INDENT,
            'name'    => 'MARKER: keep indent',
        ], [
            'pattern' => null,
            'rule'    => self::KEEP_INDENT,
            'name'    => 'SELF CLOSING: keep indent',
        ], [
            'pattern' => Pattern::IS_BLOCK,
            'rule'    => self::KEEP_INDENT,
            'name'    => 'BLOCK TAG: keep indent',
        ], [
            'pattern' => Pattern::IS_DOCTYPE,
            'rule'    => self::KEEP_INDENT,
            'name'    => 'DOCTYPE: keep indent',
        ], [
            'pattern' => Pattern::IS_OPENING,
            'rule'    => self::INCREASE_INDENT,
            'name'    => 'OPENING TAG: increase indent',
        ], [
            'pattern' => Pattern::IS_CLOSING,
            'rule'    => self::DECREASE_INDENT,
            'name'    => 'CLOSING TAG: decrease indent',
        ], [
            'pattern' => Pattern::IS_TEXT,
            'rule'    => self::KEEP_INDENT,
            'name'    => 'TEXT: keep indent',
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
     * @param  null|string                   $content Text to be processed
     * @param  \Navindex\SimpleConfig\Config $config  Configuration settings
     * @return void
     */
    public function __construct(?string $content, Config $config)
    {
        $this->content = $content ?? '';
        $this->config = $config;
        $this->setPatterns();
        $this->useLog();
    }

    /**
     * Sets the patters used in the indentation process.
     *
     * @return void
     */
    protected function setPatterns()
    {
        $tags = implode('|', $this->config->get('self-closing.tag', []));
        $this->patterns[2]['pattern'] = sprintf(Pattern::IS_SELFCLOSING, $tags);
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
     * @param  string        $type     Part type to remove
     * @param  string        $pattern  Regex pattern
     * @param  callable|null $callback Process the matches
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
     * @param  string        $type     Part type to remove
     * @param  string        $pattern  Regex pattern
     * @param  callable|null $callback Process the matches
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
     * @param  string        $type        Part type to remove
     * @param  string        $placeholder Part type to remove
     * @param  string        $pattern     Regex pattern
     * @param  callable|null $callback    Process the matches
     * @param  int|null      $offset      Index offset
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
                $tag = $matches[1][$index];
                $originalContent = $matches[2][$index];
                $config = $this->config->split("formatted.tag.{$tag}");

                $content = $this->action($originalContent, $config, 'trim', $trim);
                $content = $this->action($content, $config, 'opening-break', $openingBreak);
                $content = $this->action($content, $config, 'closing-break', $closingBreak);
                $content = $this->action($content, $config, 'cleanup-empty', $cleanupEmpty);

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
                $config = $this->config->split('attributes');

                $content = $this->action($matches[3][$index], $config, 'cleanup', false);
                $content = $this->action($content, $config, 'trim', false);

                $value = $matches[1][$index] . '=' . $matches[2][$index] . $content . $matches[2][$index];
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
                $originalContent = $matches[1][$index];
                $config = $this->config->split('cdata');

                $content = $this->action($originalContent, $config, 'cleanup', false);
                $content = $this->action($content, $config, 'trim', false);

                if ($content !== $originalContent) {
                    $value = str_replace($originalContent, $content, $value);
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
                $originalAttributes = $matches[2][$index];
                $attributes = trim($originalAttributes);
                $attributes = empty($attributes) ? '' : Helper::start(trim($originalAttributes), ' ');

                $originalContent = $matches[3][$index];
                $content = trim($originalContent);

                if ($attributes . $content !== $originalAttributes . $originalContent) {
                    $value = str_replace(
                        $originalAttributes . '>' . $originalContent,
                        $attributes . '>' . $content,
                        $value
                    );
                }
            }

            return $matches;
        });
    }

    /**
     * Restores the content parts.
     *
     * @param  string $type Part type to restore
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
        $this->restore(static::PRE);

        $tags = array_keys($this->config->get('formatted.tag', []));
        $this->content = preg_replace(sprintf(Pattern::MOVE_TO_LEFT, implode('|', $tags)), '\1', $this->content) ?? $this->content;

        return $this;
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
     * Run a specific action on the content.
     *
     * @param  string                        $content
     * @param  \Navindex\SimpleConfig\Config $config
     * @param  string                        $action
     * @param  mixed                         $default
     * @return string
     */
    protected function action(string $content, Config $config, string $action, $default): string
    {
        if ($config->get($action, $default)) {
            switch ($action) {
                case 'cleanup':
                    $content = preg_replace(Pattern::WHITESPACE, ' ', $content) ?? $content;
                    break;

                case 'trim':
                    $content = trim($content);
                    break;

                case 'opening-break':
                    $content = Helper::start($content, $this->config->get('line-break', PHP_EOL));
                    break;

                case 'closing-break':
                    $content = Helper::finish($content, $this->config->get('line-break', PHP_EOL));
                    break;

                case 'cleanup-empty':
                    $content = empty(trim($content)) ? '' : $content;
                    break;
            }
        }

        return $content;
    }

    /**
     * Content indenting.
     *
     * @return self
     *
     * @throws \Navindex\HtmlFormatter\Exceptions\IndentException
     */
    public function indent(): self
    {
        $subject = $this->content;
        $output = '';
        $match = false;
        $pos = 0;

        do {
            foreach ($this->patterns as $action) {
                if (is_string($action['pattern'])) {
                    $match = preg_match($action['pattern'], $subject, $matches);
                    if (1 === $match) {
                        $rule = $action['rule'];

                        if ($this->logger) {
                            $this->logger->push($action['name'], $subject, $matches[0]);
                        }

                        $subject = mb_substr($subject, mb_strlen($matches[0]));
                        $output .= $this->indentAction($pos, $rule, $matches[0]);

                        break;
                    }
                }
            }
        } while ($match);

        if ('' !== $subject) {
            throw new IndentException('Unable to create the indented content.', $subject);
        }

        $output = preg_replace(Pattern::TRAILING_SPACE_IN_OPENING_TAG, '\1\2', $output) ?? $output;
        $output = preg_replace(Pattern::SPACE_BEFORE_CLOSING_TAG, '\1\2', $output) ?? $output;
        $output = preg_replace(Pattern::SPACE_AFTER_OPENING_TAG, '\1\2', $output) ?? $output;
        $output = preg_replace(Pattern::TRAILING_LINE_SPACE, '\1\2', $output) ?? $output;

        $this->content = $output;

        return $this;
    }

    /**
     * Indenter core.
     *
     * @param  int    $position
     * @param  int    $rule
     * @param  string $match
     * @return string
     */
    protected function indentAction(int &$position, int $rule, string $match): string
    {
        if (static::DISCARD === $rule) {
            return '';
        }

        $tab = $this->config->get('tab', '');

        switch ($rule) {
            case static::INCREASE_INDENT:
                $indent = str_repeat($tab, $position++);
                break;
            case static::DECREASE_INDENT:
                $position = --$position < 0 ? 0 : $position;
                $indent = str_repeat($tab, $position);
                break;
            default:
                $indent = str_repeat($tab, $position);
                break;
        }

        return $indent . $match . $this->config->get('line-break', PHP_EOL);
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
     * @param  bool|null $useIt
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
     * @param  bool     $value
     * @param  callable $callback
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
