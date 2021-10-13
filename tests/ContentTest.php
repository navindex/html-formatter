<?php

declare(strict_types=1);

namespace Navindex\HtmlFormatter\Tests;

use Iterator;
use Navindex\HtmlFormatter\Exceptions\IndentException;
use Navindex\HtmlFormatter\Content;
use Navindex\SimpleConfig\Config;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Navindex\HtmlFormatter\Content
 */
final class ContentTest extends TestCase
{
    /**
     * Default config to use.
     *
     * @var array <string, mixed>
     */
    protected $config = [
        'tab' => '    ',
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
                'script' => ['closing-break' => true, 'trim' => true],
                'pre' => [],
                'textarea' => [],
            ],
            'cleanup-empty' => true,
            'opening-break' => true,
            'closing-break' => false,
            'trim' => false,
        ],
        'attributes' => [
            'trim' => true,
            'cleanup' => true,
        ],
        'cdata' => [
            'trim' => true,
            'cleanup' => true,
        ],
    ];

    /**
     * @dataProvider providerConstructor
     *
     * @param string $html
     *
     * @return void
     */
    public function testConstructorContent(string $html)
    {
        $hc = new Content($html, new Config($this->config));
        $this->assertSame($html, $hc->get());
    }

    /**
     * @return void
     */
    public function testConstructorConfig()
    {
        $hc = new class('some content', new Config($this->config)) extends Content
        {
            /**
             * @return null|array <string, mixed>
             */
            public function _getConfig(): ?array
            {
                return $this->config->toArray();
            }
        };

        $this->assertSame($this->config, $hc->_getConfig());
    }

    /**
     * @dataProvider providerConstructor
     *
     * @param string $html
     *
     * @return void
     */
    public function testContentToString(string $html)
    {
        $hc = new Content($html, new Config($this->config));
        $this->assertSame($html, (string)$hc);
    }

    /**
     * @return void
     */
    public function testUseLog()
    {
        $hc = new Content('some content', new Config($this->config));
        $this->assertIsArray($hc->useLog(true)->getLog());
    }

    /**
     * @return void
     */
    public function testUseLogNoAttribute()
    {
        $hc = new Content('some content', new Config($this->config));
        $this->assertIsArray($hc->useLog()->getLog());
    }

    /**
     * @return void
     */
    public function testDoNotUseLog()
    {
        $hc = new Content('some content', new Config($this->config));
        $this->assertNull($hc->useLog(false)->getLog());
    }

    /**
     * @dataProvider providerFormatted
     *
     * @param string   $html
     * @param string[] $parts
     * @param string   $expected
     *
     * @return void
     */
    public function testRemoveFormatted(string $html, array $parts, string $expected)
    {
        $hc = new Content($html, new Config($this->config));
        $hc->removeFormatted();
        $this->assertSame($expected, (string)$hc);
    }

    /**
     * @dataProvider providerFormatted
     *
     * @param string   $html
     * @param string[] $expected
     * @param string   $htmlReplaced
     *
     * @return void
     */
    public function testFormattedParts(string $html, array $expected, string $htmlReplaced)
    {
        $hc = new class($html, new Config($this->config)) extends Content
        {
            /**
             * @return null|array <int, string>
             */
            public function _getPreformatParts(): ?array
            {
                return $this->parts[static::PRE] ?? null;
            }
        };
        $hc->removeFormatted();
        $this->assertSame($expected, $hc->_getPreformatParts());
    }

    /**
     * @dataProvider providerRestoreFormatted
     *
     * @param string   $html
     * @param string   $expected
     *
     * @return void
     */
    public function testRestoreFormatted(string $html, string $expected)
    {
        $hc = new Content($html, new Config($this->config));
        $hc->removeFormatted()->restoreFormatted();
        $this->assertSame($expected, (string)$hc);
    }

    /**
     * @dataProvider providerAttributes
     *
     * @param string   $html
     * @param string[] $parts
     * @param string   $expected
     *
     * @return void
     */
    public function testRemoveAttributes(string $html, array $parts, string $expected)
    {
        $hc = new Content($html, new Config());
        $this->assertSame($expected, (string)$hc->removeAttributes());
    }

    /**
     * @dataProvider providerRestoreAttributes
     *
     * @param string $html
     * @param string $expected
     *
     * @return void
     */
    public function testRestoreAttributes(string $html, string $expected)
    {
        $config =       [
            'attributes' => [
                'trim' => true,
                'cleanup' => true,
            ]
        ];

        $hc = new Content($html, new Config($config));
        $hc->removeAttributes()->restoreAttributes();
        $this->assertSame($expected, (string)$hc);
    }

    /**
     * @dataProvider providerAttributes
     *
     * @param string   $html
     * @param string[] $expected
     * @param string   $htmlReplaced
     *
     * @return void
     */
    public function testAttributeParts(string $html, array $expected, string $htmlReplaced)
    {
        $hc = new class($html, new Config()) extends Content
        {
            /**
             * @return null|array <int, string>
             */
            public function _getAttributeParts(): ?array
            {
                return $this->parts[static::ATTRIBUTE] ?? null;
            }
        };
        $hc->removeAttributes();
        $this->assertSame($expected, $hc->_getAttributeParts());
    }

    /**
     * @dataProvider providerAttributeConfig
     *
     * @param string                   $html
     * @param array <string, string[]> $config
     * @param string                   $expected
     *
     * @return void
     */
    public function testAttributeConfig(string $html, array $config, string $expected)
    {
        $c = $this->config;
        $c['attributes'] = $config;
        $hc = new Content($html, new Config($c));
        $hc->removeAttributes()->restoreAttributes();
        $this->assertSame($expected, (string)$hc);
    }

    /**
     * @dataProvider providerCdata
     *
     * @param string   $html
     * @param string[] $parts
     * @param string   $expected
     *
     * @return void
     */
    public function testRemoveCdata(string $html, array $parts, string $expected)
    {
        $hc = new Content($html, new Config());
        $this->assertSame($expected, (string)$hc->removeCdata());
    }

    /**
     * @dataProvider providerCdata
     *
     * @param string   $html
     * @param string[] $parts
     * @param string   $htmlReplaced
     *
     * @return void
     */
    public function testRestoreCdata(string $html, array $parts, string $htmlReplaced)
    {
        $hc = new Content($html, new Config());
        $hc->removeCdata()->restoreCdata();
        $this->assertSame($html, (string)$hc);
    }

    /**
     * @dataProvider providerCdata
     *
     * @param string   $html
     * @param string[] $expected
     * @param string   $htmlReplaced
     *
     * @return void
     */
    public function testCdataParts(string $html, array $expected, string $htmlReplaced)
    {
        $hc = new class($html, new Config()) extends Content
        {
            /**
             * @return null|array <int, string>
             */
            public function getCdataParts(): ?array
            {
                return $this->parts[static::CDATA] ?? null;
            }
        };
        $hc->removeCdata();
        $this->assertSame($expected, $hc->getCdataParts());
    }

    /**
     * @dataProvider providerCdataConfig
     *
     * @param string                   $html
     * @param array <string, string[]> $config
     * @param string                   $expected
     *
     * @return void
     */
    public function testCdataConfig(string $html, array $config, string $expected)
    {
        $c = $this->config;
        $c['cdata'] = $config;
        $hc = new Content($html, new Config($c));
        $hc->removeCdata()->restoreCdata();
        $this->assertSame($expected, (string)$hc);
    }

    /**
     * @dataProvider providerInlines
     *
     * @param string   $html
     * @param string[] $parts
     * @param string   $expected
     *
     * @return void
     */
    public function testRemoveInlines(string $html, array $parts, string $expected)
    {
        $hc = new Content($html, new Config($this->config));
        $this->assertSame($expected, (string)$hc->removeInlines());
    }

    /**
     * @dataProvider providerRestoreInlines
     *
     * @param string $html
     * @param string $expected
     *
     * @return void
     */
    public function testRestoreInlines(string $html, string $expected)
    {
        $hc = new Content($html, new Config($this->config));
        $hc->removeInlines()->restoreInlines();
        $this->assertSame($expected, (string)$hc);
    }

    /**
     * @dataProvider providerInlines
     *
     * @param string   $html
     * @param string[] $expected
     * @param string   $htmlReplaced
     *
     * @return void
     */
    public function testInlineParts(string $html, array $expected, string $htmlReplaced)
    {
        $hc = new class($html, new Config($this->config)) extends Content
        {
            /**
             * @return null|array <int, string>
             */
            public function _getInlineParts(): ?array
            {
                return $this->parts[static::INLINE] ?? null;
            }
        };
        $hc->removeInlines();
        $this->assertSame($expected, $hc->_getInlineParts());
    }

    /**
     * @dataProvider providerWhitespace
     *
     * @param string $html
     * @param string $expected
     *
     * @return void
     */
    public function testRemoveExtraWhitespace(string $html, string $expected)
    {
        $hc = new Content($html, new Config());

        $this->assertSame($expected, (string)$hc->removeExtraWhitespace());
    }

    /**
     * @dataProvider providerIndent
     *
     * @param string $html
     * @param string $expected
     *
     * @return void
     */
    public function testIndent(string $html, string $expected)
    {
        $hc = new Content($html, new Config($this->config));
        $this->assertSame($expected, (string)$hc->indent());
    }

    /**
     * @dataProvider providerIndentWithLog
     *
     * @param string $html
     * @param array[] $expected
     *
     * @return void
     */
    public function testIndentWithLog(string $html, array $expected)
    {
        $hc = new Content($html, new Config($this->config));
        $this->assertSame($expected, $hc->useLog()->indent()->getLog());
    }

    /**
     * @dataProvider providerIndent
     *
     * @param string $html
     * @param string $output
     *
     * @return void
     */
    public function testIndentException(string $html, string $output)
    {
        $hc = new class($html, new Config($this->config)) extends Content
        {
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
                parent::__construct($content, $config);
                $this->patterns[0]['pattern'] = '/^(xxx)$/';
            }
        };

        $this->expectException(IndentException::class);
        $hc->indent();
    }

    /**
     * @dataProvider providerWhen
     *
     * @param bool   $value
     * @param string $originalContent
     * @param string $newContent
     * @param string $expected
     *
     * @return void
     */
    public function testWhen(bool $value, string $originalContent, string $newContent, string $expected)
    {
        $hc = new class($originalContent, new Config()) extends Content
        {
            /**
             * @param string $content
             *
             * @return self
             */
            public function _setContent(string $content): self
            {
                $this->content = $content;
                return $this;
            }
        };

        $hc->when($value, function ($html) use ($newContent) {
            $html->_setContent($newContent);
        });

        $this->assertSame($expected, (string)$hc);
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string>>
     */
    public function providerConstructor(): Iterator
    {
        yield [''];
        yield ['something'];
        yield ['line with a number of words'];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string|array>>
     */
    public function providerFormatted(): Iterator
    {
        yield [
            <<<INPUT
            <html>
                <head>
                    <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript">
                    </script>
                    <meta name='auth' content=1 id="auth">
                    <title>
                        Edit product
                    </title>
                    <script> Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement('style'), rxEsc = /([.*+?^$()|\[\]\/\\])/g</script>

                    <meta name="robots" content="noindex" />>
                </head>
                <body>
                    <pre>   something
                    comes
                    here </pre>
                    <textarea>
                        something comes
                        here too </textarea>
                </body></html>
            INPUT,
            [
                '<script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript"></script>',
                '<script>' . PHP_EOL . 'Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement(\'style\'), rxEsc = /([.*+?^$()|\[\]\/\\])/g' . PHP_EOL . '</script>',
                '<pre>' . PHP_EOL . '   something' . PHP_EOL . '        comes' . PHP_EOL . '        here </pre>',
                '<textarea>' . PHP_EOL . '            something comes' . PHP_EOL . '            here too </textarea>',
            ],
            <<<OUTPUT
            <html>
                <head>
                    ᐃpre:0:preᐃ
                    <meta name='auth' content=1 id="auth">
                    <title>
                        Edit product
                    </title>
                    ᐃpre:1:preᐃ

                    <meta name="robots" content="noindex" />>
                </head>
                <body>
                    ᐃpre:2:preᐃ
                    ᐃpre:3:preᐃ
                </body></html>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string|array>>
     */
    public function providerRestoreFormatted(): Iterator
    {
        yield [
            <<<INPUT
            <html>
                <head>
                    <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript">
                    </script>
                    <meta name='auth' content=1 id="auth">
                    <title>
                        Edit product
                    </title>
                    <script> Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement('style'), rxEsc = /([.*+?^$()|\[\]\/\\])/g</script>

                    <meta name="robots" content="noindex" />>
                </head>
                <body>
                    <pre>   something
                    comes
                    here </pre>
                    <textarea>
                        something comes
                        here too </textarea>
                </body></html>
            INPUT,
            <<<OUTPUT
            <html>
                <head>
                    <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript"></script>
                    <meta name='auth' content=1 id="auth">
                    <title>
                        Edit product
                    </title>
                    <script>
            Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement('style'), rxEsc = /([.*+?^$()|\[\]\/\\])/g
            </script>

                    <meta name="robots" content="noindex" />>
                </head>
                <body>
                    <pre>
               something
                    comes
                    here </pre>
                    <textarea>
                        something comes
                        here too </textarea>
                </body></html>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string|array>>
     */
    public function providerAttributes(): Iterator
    {
        yield [
            <<<INPUT
            <html lang = "en_AU" data-controller="html-load">
                <head>
                    <meta charset  ="utf-8  ">
                    <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                    <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript" defer ></script>
                    <meta name='auth' content=1
                            id="auth">
                    <title>
                        Edit product
                    </title>
                    <link rel="stylesheet" href="http://localhost/common-vendor.css">
                    <link rel="stylesheet" href="http://localhost/css/dashboard.css">
                    <meta name="robots" content="noindex" />>
                </head>
                <body>
                    <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" reserveAspectRatio="xMidYMid meet" viewBox="0 0 111.62013 21.110666" >
                        <g class="a">
                            <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
                        </g>
                    </svg>
                    <button data-anibutton-label="Deleting..." data-action="click->anibutton#confirm" data-anibutton-confirm="Are you sure you want to delete this product?">
                        Delete
                    </button>
                </body></html>
            INPUT,
            [
                'lang="en_AU"',
                'data-controller="html-load"',
                'charset="utf-8  "',
                'http-equiv="X-UA-Compatible"',
                'content="IE=edge"',
                'name="viewport"',
                'content="width=device-width, initial-scale=1, shrink-to-fit=no"',
                'src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3"',
                'type="text/javascript"',
                'name=\'auth\'',
                'id="auth"',
                'rel="stylesheet"',
                'href="http://localhost/common-vendor.css"',
                'rel="stylesheet"',
                'href="http://localhost/css/dashboard.css"',
                'name="robots"',
                'content="noindex"',
                'class="  header-brand   order-last "',
                'href="http://localhost/dashboard/"',
                'xmlns="http://www.w3.org/2000/svg"',
                'xmlns:xlink="http://www.w3.org/1999/xlink"',
                'reserveAspectRatio="xMidYMid meet"',
                'viewBox="0 0 111.62013 21.110666"',
                'class="a"',
                'd="m30.286 20.4727h-4.264v-19.8613h4.264z"',
                'data-anibutton-label="Deleting..."',
                'data-action="click->anibutton#confirm"',
                'data-anibutton-confirm="Are you sure you want to delete this product?"',
            ],
            <<<OUTPUT
            <html ᐃattr:0:attrᐃ ᐃattr:1:attrᐃ>
                <head>
                    <meta ᐃattr:2:attrᐃ>
                    <meta ᐃattr:3:attrᐃ ᐃattr:4:attrᐃ>
                    <meta ᐃattr:5:attrᐃ ᐃattr:6:attrᐃ>
                    <script ᐃattr:7:attrᐃ ᐃattr:8:attrᐃ defer ></script>
                    <meta ᐃattr:9:attrᐃ content=1
                            ᐃattr:10:attrᐃ>
                    <title>
                        Edit product
                    </title>
                    <link ᐃattr:11:attrᐃ ᐃattr:12:attrᐃ>
                    <link ᐃattr:13:attrᐃ ᐃattr:14:attrᐃ>
                    <meta ᐃattr:15:attrᐃ ᐃattr:16:attrᐃ />>
                </head>
                <body>
                    <a ᐃattr:17:attrᐃ ᐃattr:18:attrᐃ >   Dashboard   </a>
                    <svg ᐃattr:19:attrᐃ ᐃattr:20:attrᐃ ᐃattr:21:attrᐃ ᐃattr:22:attrᐃ >
                        <g ᐃattr:23:attrᐃ>
                            <path ᐃattr:24:attrᐃ />
                        </g>
                    </svg>
                    <button ᐃattr:25:attrᐃ ᐃattr:26:attrᐃ ᐃattr:27:attrᐃ>
                        Delete
                    </button>
                </body></html>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string|array>>
     */
    public function providerRestoreAttributes(): Iterator
    {
        yield [
            <<<INPUT
            <html lang = "en_AU" data-controller="html-load">
                <head>
                    <meta charset  ="utf-8  ">
                    <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                    <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript" defer ></script>
                    <meta name='auth' content=1
                            id="auth">
                    <title>
                        Edit product
                    </title>
                    <link rel="stylesheet" href="http://localhost/common-vendor.css">
                    <link rel="stylesheet" href="http://localhost/css/dashboard.css">
                    <meta name="robots" content="noindex" />>
                </head>
                <body>
                    <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" reserveAspectRatio="xMidYMid meet" viewBox="0 0 111.62013 21.110666" >
                        <g class="a">
                            <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
                        </g>
                    </svg>
                    <button data-anibutton-label="Deleting..." data-action="click->anibutton#confirm" data-anibutton-confirm="Are you sure you want to delete this product?">
                        Delete
                    </button>
                </body></html>
            INPUT,
            <<<OUTPUT
            <html lang="en_AU" data-controller="html-load">
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                    <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript" defer ></script>
                    <meta name='auth' content=1
                            id="auth">
                    <title>
                        Edit product
                    </title>
                    <link rel="stylesheet" href="http://localhost/common-vendor.css">
                    <link rel="stylesheet" href="http://localhost/css/dashboard.css">
                    <meta name="robots" content="noindex" />>
                </head>
                <body>
                    <a class="header-brand order-last" href="http://localhost/dashboard/" >   Dashboard   </a>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" reserveAspectRatio="xMidYMid meet" viewBox="0 0 111.62013 21.110666" >
                        <g class="a">
                            <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
                        </g>
                    </svg>
                    <button data-anibutton-label="Deleting..." data-action="click->anibutton#confirm" data-anibutton-confirm="Are you sure you want to delete this product?">
                        Delete
                    </button>
                </body></html>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string|array>>
     */
    public function providerAttributeConfig(): Iterator
    {
        yield [
            <<<INPUT
            <html lang = "  en_AU">
                <head>
                    <meta charset  ="utf-8 ">
                    <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
                    <meta name='viewport   ' content=" width=device-width,    initial-scale=1,
                        shrink-to-fit=no">
                    <meta name='   auth' content=1
                            id="auth">
                </head>
                <body>
                    <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
                </body></html>
            INPUT,
            ['trim' => true],
            <<<OUTPUT
            <html lang="en_AU">
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name='viewport' content="width=device-width,    initial-scale=1,
                        shrink-to-fit=no">
                    <meta name='auth' content=1
                            id="auth">
                </head>
                <body>
                    <a class="header-brand   order-last" href="http://localhost/dashboard/" >   Dashboard   </a>
                </body></html>
            OUTPUT,
        ];
        yield [
            <<<INPUT
            <html lang = "  en_AU">
                <head>
                    <meta charset  ="utf-8 ">
                    <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
                    <meta name='viewport   ' content=" width=device-width,    initial-scale=1,
                        shrink-to-fit=no">
                    <meta name='   auth' content=1
                            id="auth">
                </head>
                <body>
                    <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
                </body></html>
            INPUT,
            ['trim' => true, 'cleanup' => true],
            <<<OUTPUT
            <html lang="en_AU">
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name='viewport' content="width=device-width, initial-scale=1, shrink-to-fit=no">
                    <meta name='auth' content=1
                            id="auth">
                </head>
                <body>
                    <a class="header-brand order-last" href="http://localhost/dashboard/" >   Dashboard   </a>
                </body></html>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string|array>>
     */
    public function providerCdata(): Iterator
    {
        yield [
            <<<INPUT
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <style><![CDATA[
                    .a {
                        fill: #28231d;
                    }
                ]]></style>
                <g class="a">
                    <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
                    <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
                </g>
            </svg>
            INPUT,
            [
                '<![CDATA[' . PHP_EOL . '        .a {' . PHP_EOL . '            fill: #28231d;' . PHP_EOL . '        }' . PHP_EOL . '    ]]>',
            ],
            <<<OUTPUT
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <style>ᐃcdata:0:cdataᐃ</style>
                <g class="a">
                    <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
                    <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
                </g>
            </svg>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string|array>>
     */
    public function providerCdataConfig(): Iterator
    {
        yield [
            <<<INPUT
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <style><![CDATA[
                    .a {
                        fill: #28231d;
                    }
                ]]></style>
                <g class="a">
                    <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
                    <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
                </g>
            </svg>
            INPUT,
            ['trim' => true],
            <<<OUTPUT
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <style><![CDATA[.a {
                        fill: #28231d;
                    }]]></style>
                <g class="a">
                    <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
                    <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
                </g>
            </svg>
            OUTPUT,
        ];
        yield [
            <<<INPUT
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <style><![CDATA[
                    .a {
                        fill: #28231d;
                    }
                ]]></style>
                <g class="a">
                    <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
                    <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
                </g>
            </svg>
            INPUT,
            ['trim' => true, 'cleanup' => true],
            <<<OUTPUT
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <style><![CDATA[.a { fill: #28231d; }]]></style>
                <g class="a">
                    <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
                    <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
                </g>
            </svg>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string|array>>
     */
    public function providerInlines(): Iterator
    {
        yield [
            <<<INPUT
            <body>
                <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
                <br><br />
                <span>This is <strong>bold</strong>.</span>
                <button data-anibutton-label="Deleting..." data-action="click-anibutton#confirm" data-anibutton-confirm="Are you sure you want to delete this product?">
                    Delete
                </button>
            </body>
            INPUT,
            [
                '<a class="  header-brand   order-last " href="http://localhost/dashboard/">Dashboard</a>',
                '<strong>bold</strong>',
                '<button data-anibutton-label="Deleting..." data-action="click-anibutton#confirm" ' .
                    'data-anibutton-confirm="Are you sure you want to delete this product?">Delete</button>',
                '<span>This is ᐃinline:1:inlineᐃ.</span>'
            ],
            <<<OUTPUT
            <body>
                ᐃinline:0:inlineᐃ
                <br><br />
                ᐃinline:3:inlineᐃ
                ᐃinline:2:inlineᐃ
            </body>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string|array>>
     */
    public function providerRestoreInlines(): Iterator
    {
        yield [
            <<<INPUT
            <body>
                <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
                <br><br />
                <span>This is <strong>bold</strong>.</span>
                <button data-anibutton-label="Deleting..." data-action="click-anibutton#confirm" data-anibutton-confirm="Are you sure you want to delete this product?">
                    Delete
                </button>
            </body>
            INPUT,
            <<<OUTPUT
            <body>
                <a class="  header-brand   order-last " href="http://localhost/dashboard/">Dashboard</a>
                <br><br />
                <span>This is <strong>bold</strong>.</span>
                <button data-anibutton-label="Deleting..." data-action="click-anibutton#confirm" data-anibutton-confirm="Are you sure you want to delete this product?">Delete</button>
            </body>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, string[]>
     */
    public function providerWhitespace(): Iterator
    {
        yield [
            <<<INPUT
            <svg
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" >
                <style><![CDATA[
                    .a {
                        fill: #28231d;
                    }
                ]]></style>
                <g      class="a">
                    <path d="m30.286 20.4727h-4.264v-19.8613h4.264z"              />
                    <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
                </g>
            </svg>
            INPUT,
            '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" > ' .
                '<style><![CDATA[ .a { fill: #28231d; } ]]></style> ' .
                '<g class="a"> <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" /> ' .
                '<path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" /> </g> </svg>',
        ];
        yield [
            <<<INPUT
            <html lang = "  en_AU">
                <head>
                    <meta charset  ="utf-8  ">
                    <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content=" width=device-width,    initial-scale=1,
                        shrink-to-fit=no">
                    <meta name="auth" content="1 "
                            id="auth">
                </head>
                <body>
                    <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
                </body></html>
            INPUT,
            '<html lang = " en_AU"> <head> <meta charset ="utf-8 "> <meta http-equiv= "X-UA-Compatible" content="IE=edge"> ' .
                '<meta name="viewport" content=" width=device-width, initial-scale=1, shrink-to-fit=no"> ' .
                '<meta name="auth" content="1 " id="auth"> </head> <body> ' .
                '<a class=" header-brand order-last " href="http://localhost/dashboard/" > Dashboard </a> ' .
                '</body></html>',
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array<int, string|array>>
     */
    public function providerIndent(): Iterator
    {
        yield [
            <<<INPUT
            <svg
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" >
            <g class="a"><path d="m30.286 20.4727h-4.264v-19.8613h4.264z"              />
                <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" /></g>
            </svg>
            INPUT,
            <<<OUTPUT
            <svg
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink">
                <g class="a">
                    <path d="m30.286 20.4727h-4.264v-19.8613h4.264z"/>
                    <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z"/>
                </g>
            </svg>
            OUTPUT,
        ];
        yield [
            <<<INPUT
            <html lang = "  en_AU">
                <head>
                    <meta charset  ="utf-8  ">
                        <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content=" width=device-width,    initial-scale=1,
                            shrink-to-fit=no">
                    <meta name="auth" content="1 "
                            id="auth">
                </head><body>
                <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
                </body></html>
            INPUT,
            <<<OUTPUT
            <html lang = "  en_AU">
                <head>
                    <meta charset  ="utf-8  ">
                    <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content=" width=device-width,    initial-scale=1,
                            shrink-to-fit=no">
                    <meta name="auth" content="1 "
                            id="auth">
                </head>
                <body>
                    <a class="  header-brand   order-last " href="http://localhost/dashboard/">Dashboard</a>
                </body>
            </html>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array<int, string|array>>
     */
    public function providerIndentWithLog(): Iterator
    {
        yield [
            <<<INPUT
            <html>
            <head> something </head>
            <body> something,
            something else </body>
            </html>
            INPUT,
            [
                [
                    'rule'    => 'OPENING TAG: increase indent',
                    'subject' => '<html>' . PHP_EOL . '<head> something </head>' . PHP_EOL . '<body> something,' . PHP_EOL . 'something else </body>' . PHP_EOL . '</html>',
                    'matches' => "<html>",
                ],
                [
                    'rule'    => 'WHITESPACE: discard',
                    'subject' => PHP_EOL . '<head> something </head>' . PHP_EOL . '<body> something,' . PHP_EOL . 'something else </body>' . PHP_EOL . '</html>',
                    'matches' => PHP_EOL,
                ],
                [

                    'rule'    => 'BLOCK TAG: keep indent',
                    'subject' => '<head> something </head>' . PHP_EOL . '<body> something,' . PHP_EOL . 'something else </body>' . PHP_EOL . '</html>',
                    'matches' => '<head> something </head>',
                ],
                [
                    'rule'    => 'WHITESPACE: discard',
                    'subject' => PHP_EOL . '<body> something,' . PHP_EOL . 'something else </body>' . PHP_EOL . '</html>',
                    'matches' => PHP_EOL,
                ],
                [
                    'rule'    => 'BLOCK TAG: keep indent',
                    'subject' => '<body> something,' . PHP_EOL . 'something else </body>' . PHP_EOL . '</html>',
                    'matches' => '<body> something,' . PHP_EOL . 'something else </body>',
                ],
                [
                    'rule'    => 'WHITESPACE: discard',
                    'subject' => PHP_EOL . '</html>',
                    'matches' => PHP_EOL,
                ],
                [
                    'rule'    => 'CLOSING TAG: decrease indent',
                    'subject' => '</html>',
                    'matches' => '</html>',
                ],
            ],
            <<<OUTPUT
            <html>
                <head>
                    something
                </head>
                <body>
                    something,
                    something else
                </body>
            </html>
            OUTPUT,
        ];
        yield [
            <<<INPUT
            <ul>
            <li><input type="text"></li>
            <li><input type="text" ></li>
            <li><input type="text"/></li>
            <li><input type="text" /></li>
            </ul>
            INPUT,
            [
                [
                    'rule'    => 'OPENING TAG: increase indent',
                    'subject' => '<ul>' . PHP_EOL . '<li><input type="text"></li>' . PHP_EOL . '<li><input type="text" ></li>' . PHP_EOL . '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => "<ul>",
                ],
                [
                    'rule'    => 'WHITESPACE: discard',
                    'subject' => PHP_EOL . '<li><input type="text"></li>' . PHP_EOL . '<li><input type="text" ></li>' . PHP_EOL . '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => PHP_EOL,
                ],
                [
                    'rule'    => 'OPENING TAG: increase indent',
                    'subject' => '<li><input type="text"></li>' . PHP_EOL . '<li><input type="text" ></li>' . PHP_EOL . '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '<li>',
                ],
                [
                    'rule'    => 'SELF CLOSING: keep indent',
                    'subject' => '<input type="text"></li>' . PHP_EOL . '<li><input type="text" ></li>' . PHP_EOL . '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '<input type="text">',
                ],
                [
                    'rule'    => 'CLOSING TAG: decrease indent',
                    'subject' => '</li>' . PHP_EOL . '<li><input type="text" ></li>' . PHP_EOL . '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '</li>',
                ],
                [
                    'rule'    => 'WHITESPACE: discard',
                    'subject' => PHP_EOL . '<li><input type="text" ></li>' . PHP_EOL . '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => PHP_EOL,
                ],
                [
                    'rule'    => 'OPENING TAG: increase indent',
                    'subject' => '<li><input type="text" ></li>' . PHP_EOL . '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '<li>',
                ],
                [
                    'rule'    => 'SELF CLOSING: keep indent',
                    'subject' => '<input type="text" ></li>' . PHP_EOL . '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '<input type="text" >',
                ],
                [
                    'rule'    => 'CLOSING TAG: decrease indent',
                    'subject' => '</li>' . PHP_EOL . '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '</li>',
                ],
                [
                    'rule'    => 'WHITESPACE: discard',
                    'subject' => PHP_EOL . '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => PHP_EOL,
                ],
                [
                    'rule'    => 'OPENING TAG: increase indent',
                    'subject' => '<li><input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '<li>',
                ],
                [
                    'rule'    => 'SELF CLOSING: keep indent',
                    'subject' => '<input type="text"/></li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '<input type="text"/>',
                ],
                [
                    'rule'    => 'CLOSING TAG: decrease indent',
                    'subject' => '</li>' . PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '</li>',
                ],
                [
                    'rule'    => 'WHITESPACE: discard',
                    'subject' => PHP_EOL . '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => PHP_EOL,
                ],
                [
                    'rule'    => 'OPENING TAG: increase indent',
                    'subject' => '<li><input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '<li>',
                ],
                [
                    'rule'    => 'SELF CLOSING: keep indent',
                    'subject' => '<input type="text" /></li>' . PHP_EOL . '</ul>',
                    'matches' => '<input type="text" />',
                ],
                [
                    'rule'    => 'CLOSING TAG: decrease indent',
                    'subject' => '</li>' . PHP_EOL . '</ul>',
                    'matches' => '</li>',
                ],
                [
                    'rule'    => 'WHITESPACE: discard',
                    'subject' => PHP_EOL . '</ul>',
                    'matches' => PHP_EOL,
                ],
                [
                    'rule'    => 'CLOSING TAG: decrease indent',
                    'subject' => '</ul>',
                    'matches' => '</ul>',
                ],
            ],
            <<<OUTPUT
            <ul>
                <li>
                    <input type="text">
                </li>
                <li>
                    <input type="text" >
                </li>
                <li>
                    <input type="text"/>
                </li>
                <li>
                    <input type="text" />
                </li>
            </ul>
            OUTPUT,
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, bool|string>>
     */
    public function providerWhen(): Iterator
    {
        yield [true,  'abc', 'xyz', 'xyz'];
        yield [false, 'abc', 'xyz', 'abc'];
    }
}
