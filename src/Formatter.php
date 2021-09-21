<?php

namespace Navindex\HtmlFormatter;

use Navindex\HtmlFormatter\HtmlContent;
use Navindex\HtmlFormatter\Pattern;

/**
 * Formatter class.
 */
class Formatter
{
    /**
     * Configuration settings.
     *
     * @var array
     */
    protected $options = [
        'tab'         => '    ',
        'empty_tags'  => [
            'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen',
            'link', 'menuitem', 'meta', 'meta', 'param', 'path', 'source', 'track', 'use', 'wbr',
        ],
        'inline_tags' => [
            'a', 'abbr', 'acronym', 'b', 'bdo', 'big', 'br', 'button', 'cite', 'code', 'dfn', 'em',
            'i', 'img', 'kbd', 'label', 'samp', 'small', 'span', 'strong', 'sub', 'sup', 'tt', 'var',
        ],
        'attribute_trim' => false,
        'attribute_cleanup' => false,
        'cdata_cleanup' => false,
    ];

    /**
     * Constructor.
     *
     * @param array|null $options  Associative array of option names and values
     *
     * @return void
     */
    public function __construct(?array $options = null)
    {
        if (!empty($options)) {
            $this->options($options);
        }
    }

    /**
     * Sets the formatter options.
     *
     * @param array $options Associative array of option names and values
     *
     * @return self
     */
    public function options(array $options): self
    {
        foreach ($this->options as $key => &$value) {
            if (is_scalar($value) && is_scalar($options[$key] ?? null)) {
                $value = $options[$key];
            }
            if (is_array($value) && is_array($options[$key] ?? null)) {
                $value = array_unique(array_merge($value, $options[$key]));
            }
        }

        return $this;
    }

    /**
     * Beautify the HTML code.
     *
     * @param string $input
     *
     * @return string
     */
    public function beautify(string $input): string
    {
        $html = new HtmlContent($input, $this->options);
        $attrCleanup = $this->options['attribute_cleanup'] ?? false;
        $cdataCleanup = $this->options['cdata_cleanup'] ?? false;

        return $html
            ->remove('script', Pattern::SCRIPT)
            ->remove('pre', Pattern::PRE)
            ->when(!$attrCleanup, function ($html) {
                $html->removeAttributes();
            })
            ->when(!$cdataCleanup, function ($html) {
                $html->removeCdata();
            })
            ->removeExtraWhitespace()
            ->when($attrCleanup, function ($html) {
                $html->removeAttributes();
            })
            ->when($cdataCleanup, function ($html) {
                $html->removeCdata();
            })
            ->removeInlines()
            ->indent()
            ->restoreInlines()
            ->restoreCdata()
            ->restoreAttributes()
            ->restore('pre')
            ->restore('script');
    }

    /**
     * Minify the HTML code.
     *
     * @param string $input
     *
     * @return string
     */
    public function minify(string $input): string
    {
        $attrCleanup = $this->options['attribute_cleanup'] ?? false;
        $cdataCleanup = $this->options['cdata_cleanup'] ?? false;

        $html = new HtmlContent($input, $this->options);

        return $html
            ->remove('script', Pattern::SCRIPT)
            ->remove('pre', Pattern::PRE)
            ->when(!$attrCleanup, function ($html) {
                $html->removeAttributes();
            })
            ->when(!$cdataCleanup, function ($html) {
                $html->removeCdata();
            })
            ->removeExtraWhitespace()
            ->when(!$attrCleanup, function ($html) {
                $html->restoreAttributes();
            })
            ->when(!$cdataCleanup, function ($html) {
                $html->restoreCdata();
            })
            ->restore('pre')
            ->restore('script');
    }
}
