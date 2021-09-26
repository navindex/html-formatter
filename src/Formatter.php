<?php

namespace Navindex\HtmlFormatter;

use Navindex\HtmlFormatter\HtmlContent;
use Navindex\SimpleConfig\Config;

/**
 * Formatter class.
 */
class Formatter
{
    /**
     * Configuration settings.
     *
     * @var array <string, mixed>
     */
    protected $options = [
        'tab'         => '    ',
        'empty_tags'  => [
            'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input',
            'keygen', 'link', 'menuitem', 'meta', 'param', 'source', 'track', 'wbr',
            'animate', 'stop', 'path', 'circle', 'line', 'polyline', 'rect', 'use',
        ],
        'inline_tags' => [
            'a', 'abbr', 'acronym', 'b', 'bdo', 'big', 'br', 'button', 'cite', 'code', 'dfn', 'em',
            'i', 'img', 'kbd', 'label', 'samp', 'small', 'span', 'strong', 'sub', 'sup', 'tt', 'var',
        ],
        'keep_format' => ['script', 'pre', 'textarea'],
        'attribute_trim' => false,
        'attribute_cleanup' => false,
        'cdata_cleanup' => false,
    ];

    /**
     * Configuration settings.
     *
     * @var \Navindex\SimpleConfig\Config
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param null|array <string, mixed> $config Associative array of option names and values
     *
     * @return void
     */
    public function __construct(?array $config = null)
    {
        $this->config = new Config($this->options);
        if (is_array($config)) {
            $this->config->merge($config);
        }
    }

    /**
     * Sets the formatter options.
     *
     * @param array <string, mixed> $options Associative array of option names and values
     *
     * @return self
     */
    public function setConfig(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Gets the formatter options.
     *
     * @param array <string, mixed> $options Associative array of option names and values
     *
     * @return self
     */
    public function getConfig(): Config
    {
        return $this->config;
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
        $html = new HtmlContent($input, $this->config);
        $attrCleanup = $this->options['attribute_cleanup'] ?? false;
        $cdataCleanup = $this->options['cdata_cleanup'] ?? false;

        return $html
            ->removePreformats()
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
            ->restorePreformats();
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

        $html = new HtmlContent($input, $this->config);

        return $html
            ->removePreformats()
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
            ->restorePreformats();
    }
}
