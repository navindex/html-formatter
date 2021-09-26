<?php

declare(strict_types=1);

namespace Navindex\HtmlFormatter\Tests;

use Iterator;
use Navindex\HtmlFormatter\Formatter;
use Navindex\SimpleConfig\Config;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Navindex\HtmlFormatter\Formatter
 */
final class FormatterTest extends TestCase
{
    /**
     * @dataProvider providerConstructor
     *
     * @param null|mixed[] $config
     * @param \Navindex\SimpleConfig\Config $expected
     *
     * @return void
     */
    public function testConstructor(?array $config, Config $expected)
    {
        $f = new Formatter($config);
        $this->assertSame($expected->toArray(), $f->getConfig()->toArray());
    }
    /**
     * @dataProvider providerConfig
     *
     * @param null|\Navindex\SimpleConfig\Config|mixed[] $config
     * @param mixed[]                                    $expected
     *
     * @return void
     */
    public function testConfig($config, array $expected)
    {
        $f = new Formatter();
        $f->setConfig($config);
        $this->assertSame($expected, $f->getConfigArray());
    }

    /**
     * @dataProvider providerBeautify
     *
     * @param string $html
     * @param string $expected
     *
     * @return void
     */
    public function testBeautify(string $html, string $expected)
    {
        $f = new Formatter();
        $this->assertSame($expected, $f->beautify($html));
    }

    /**
     * @dataProvider providerMinify
     *
     * @param string $html
     * @param string $expected
     *
     * @return void
     */
    public function testMinify(string $html, string $expected)
    {
        $f = new Formatter();
        $this->assertSame($expected, $f->minify($html));
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, mixed[]>
     */
    public function providerConstructor(): Iterator
    {
        $defaultConfig = (new class() extends Formatter
        {
            /**
             * @return mixed[]
             */
            public function _getDefaultConfig(): array
            {
                return $this->defaultConfig;
            }
        })->_getDefaultConfig();

        $config = [
            'tab'         => 'xx',
            'empty_tags'  => ['empty_tag', 'another_empty_tag'],
            'inline_tags' => ['inline_tag', 'another_inline_tag'],
            'keep_format' => ['preformatted_tag', 'another_preformatted_tag'],
            'attribute_trim' => true,
            'attribute_cleanup' => true,
            'cdata_cleanup' => true,
        ];

        yield [null, new Config($defaultConfig)];
        yield [[], new Config([])];
        yield [$config, new Config($config)];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, mixed[]>
     */
    public function providerConfig(): Iterator
    {
        $config = [
            'tab'         => '',
            'empty_tags'  => ['empty_tag', 'another_empty_tag'],
            'inline_tags' => ['inline_tag', 'another_inline_tag'],
            'keep_format' => ['preformatted_tag', 'another_preformatted_tag'],
            'attribute_trim' => false,
            'attribute_cleanup' => false,
            'cdata_cleanup' => false,
        ];

        yield [null, []];
        yield [[], []];
        yield [new Config(), []];
        yield [new Config([]), []];
        yield [new Config(null), []];
        yield [$config, $config];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, mixed[]>
     */
    public function providerBeautify(): Iterator
    {
        yield [
            <<<INPUT
            <!DOCTYPE html><html> <head>
            <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript">
            </script>
            <meta name='auth' content=1 id="auth">
            <title>
                Edit product
                </title>
            <script> Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement('style'), rxEsc = /([.*+?^$()|\[\]\/\\])/g</script>

            <meta name="robots" content="noindex" />
            </head>
            <body>
                    <pre>   something
                    comes
                    here </pre>
                    <textarea>
                        something comes
                        here too </textarea>
            <div class="container-fluid" data-controller="    base
            " >

                    <div class="row">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    reserveAspectRatio="xMidYMid meet" viewBox="0 0 111.62013 21.110666" >
                    <g     class="    a"><path d="m30.286 20.4727h-4.264v-19.8613h4.264z" /></g>
            </svg>
                </body> </html>
            INPUT,
            <<<OUTPUT
            <!DOCTYPE html>
            <html>
                <head>
                    <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript"></script>
                    <meta name='auth' content=1 id="auth">
                    <title> Edit product </title>
                    <script> Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement('style'), rxEsc = /([.*+?^$()|\[\]\/\\])/g</script>
                    <meta name="robots" content="noindex" />
                </head>
                <body>
                    <pre>   something
                    comes
                    here </pre>
                    <textarea>
                        something comes
                        here too </textarea>
                    <div class="container-fluid" data-controller="base" >
                        <div class="row">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" reserveAspectRatio="xMidYMid meet" viewBox="0 0 111.62013 21.110666" >
                                <g class="a">
                                    <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
                                </g>
                            </svg>
                        </body>
                    </html>
            OUTPUT,
        ];
        // yield[];
        // yield[];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, mixed[]>
     */
    public function providerMinify(): Iterator
    {
        yield [
            <<<INPUT
            <!DOCTYPE html><html> <head>
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
            <div class="container-fluid" data-controller="    base
            " >

                    <div class="row">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    reserveAspectRatio="xMidYMid meet" viewBox="0 0 111.62013 21.110666" >
                    <g     class="    a"><path d="m30.286 20.4727h-4.264v-19.8613h4.264z" /></g>
            </svg>
                </body> </html>
            INPUT,
            <<<OUTPUT
            <!DOCTYPE html><html> <head> <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript">
            </script> <meta name='auth' content=1 id="auth"> <title> Edit product </title> <script> Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement('style'), rxEsc = /([.*+?^$()|\[\]\/\])/g</script> <meta name="robots" content="noindex" />> </head> <body> <pre>   something
                    comes
                    here </pre> <textarea>
                        something comes
                        here too </textarea> <div class="container-fluid" data-controller="base" > <div class="row"> <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" reserveAspectRatio="xMidYMid meet" viewBox="0 0 111.62013 21.110666" > <g class="a"><path d="m30.286 20.4727h-4.264v-19.8613h4.264z" /></g> </svg> </body> </html>
            OUTPUT,
        ];
        // yield[];
        // yield[];
    }
}
