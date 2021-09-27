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
     * Common configuration settings.
     *
     * @var array <string, mixed>
     */
    protected $commonConfig = [
        'self-closing'  => [
            'tag' => [
                'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input',
                'keygen', 'link', 'menuitem', 'meta', 'param', 'source', 'track', 'wbr',
                'animate', 'stop', 'path', 'circle', 'line', 'polyline', 'rect', 'use',
            ],
        ],
        'inline' => [
            'tag' => [
                'a', 'abbr', 'acronym', 'b', 'bdo', 'big', 'br', 'button', 'cite', 'code', 'dfn', 'em',
                'i', 'img', 'kbd', 'label', 'samp', 'small', 'span', 'strong', 'sub', 'sup', 'tt', 'var',
            ],
        ],
        'formatted' => [
            'tag' => [
                'script' => [],
                'pre' => [],
                'textarea' => [],
            ],
            'cleanup-empty' => true,
            'closing-break' => false,
            'trim' => false,
        ],
        'attributes' => [
            'trim' => true,
        ],
        'cdata' => [
            'trim' => true,
            'cleanup' => true,
        ],
    ];

    /**
     * Configuration settings for beautification.
     *
     * @var array <string, mixed>
     */
    protected $beautifyConfig = [
        'tab' => '    ',
        'line-break' => PHP_EOL,
        'formatted' => [
            'tag' => [
                'script' => ['closing-break' => true, 'trim' => true],
            ],
            'opening-break' => true,
        ],
        'attributes' => [
            'cleanup' => false,
        ],
    ];

    /**
     * Configuration settings for minification.
     *
     * @var array <string, mixed>
     */
    protected $minifyConfig = [
        'tab' => '',
        'line-break' => '',
        'formatted' => [
            'tag' => [
                'script' => ['trim' => true],
            ],
            'opening-break' => false,
        ],
        'attributes' => [
            'cleanup' => true,
        ],
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
     * @param null|mixed[] $config Associative array of option names and values
     *
     * @return void
     */
    public function __construct(?array $config = null)
    {
        if ($config) {
            $this->config = new Config($config);
        }
    }

    /**
     * Sets the formatter config.
     *
     * @param null|\Navindex\SimpleConfig\Config|mixed[] $config Associative array of option names and values
     *
     * @return self
     */
    public function setConfig($config): self
    {
        $this->config = ($config instanceof Config)
            ? $config
            : new Config($config);

        return $this;
    }

    /**
     * Gets the formatter config.
     *
     * @return \Navindex\SimpleConfig\Config
     */
    public function getConfig(): Config
    {
        return $this->config ?? new Config();
    }

    /**
     * Gets the formatter config.
     *
     * @return mixed[]
     */
    public function getConfigArray(): array
    {
        return (null === $this->config) ? [] : $this->config->toArray();
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
        $commonConfig = new Config($this->commonConfig);
        $beautifyConfig = $commonConfig->merge($this->beautifyConfig);

        $config = $this->config ?? new Config();
        $config->merge($beautifyConfig, Config::MERGE_KEEP);

        $html = new HtmlContent($input, $config);

        return $html
            ->removeFormatted()
            ->removeAttributes()
            ->removeCdata()
            ->removeExtraWhitespace()
            ->removeInlines()
            ->indent()
            ->restoreInlines()
            ->restoreCdata()
            ->restoreAttributes()
            ->restoreFormatted();
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
        $commonConfig = new Config($this->commonConfig);
        $minifyConfig = $commonConfig->merge($this->minifyConfig);

        $config = $this->config ?? new Config();
        $config->merge($minifyConfig, Config::MERGE_KEEP);

        $html = new HtmlContent($input, $config);

        return $html
            ->removeFormatted()
            ->removeAttributes()
            ->removeCdata()
            ->removeExtraWhitespace()
            ->indent()
            ->restoreCdata()
            ->restoreAttributes()
            ->restoreFormatted();
    }
}
