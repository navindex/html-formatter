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
        $config = [
            'tab'           => 'xx',
            'self-closing'  => ['tag' => ['empty_tag', 'another_empty_tag']],
            'inline'        => ['tag' => ['inline_tag', 'another_inline_tag']],
            'formatted'     => ['formatted_tag' => [], 'another_formatted_tag' => []],
            'attributes'    => ['trim' => true, 'cleanup' => true],
            'cdata'         => ['trim' => true, 'cleanup' => true],
        ];

        yield [null, new Config()];
        yield [[], new Config()];
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
            'self-closing'  => ['empty_tag', 'another_empty_tag'],
            'inline' => ['inline_tag', 'another_inline_tag'],
            'keep_format' => ['preformatted_tag', 'another_preformatted_tag'],
            'attributes' => [
                'trim' => false,
                'cleanup' => false,
            ],
            'cdata' => [
                'trim' => false,
                'cleanup' => false,
            ],
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
                <meta charset  ="utf-8 ">
                <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
            <meta name='viewport   ' content=" width=device-width,    initial-scale=1,
                shrink-to-fit=no">
            <meta name='   auth' content=1
                    id="auth">
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
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name='viewport' content="width=device-width, initial-scale=1, shrink-to-fit=no">
                    <meta name='auth' content=1 id="auth">
                    <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript"></script>
                    <meta name='auth' content=1 id="auth">
                    <title>Edit product</title>
            <script>
            Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement('style'), rxEsc = /([.*+?^$()|\[\]\/\\])/g
            </script>
                    <meta name="robots" content="noindex"/>
                </head>
                <body>
            <pre>
               something
                    comes
                    here </pre>
            <textarea>
                        something comes
                        here too </textarea>
                    <div class="container-fluid" data-controller="base">
                        <div class="row">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" reserveAspectRatio="xMidYMid meet" viewBox="0 0 111.62013 21.110666">
                                <g class="a">
                                    <path d="m30.286 20.4727h-4.264v-19.8613h4.264z"/>
                                </g>
                            </svg>
                        </body>
                    </html>
            OUTPUT,
        ];
        yield [
            <<<INPUT
            <form id="post-form"
            class="mb-md-4"
            method="post"
            enctype="multipart/form-data"
            data-controller="form"
            data-action="keypress->form#disableKey
                             form#submit"
            data-form-validation="Please check the entered data and make sure you filled all the required fields."
            novalidate
                >
                    <fieldset class="row g-0 mb-3">
                <div class="col p-0 px-3">
                    <legend class="text-black">
                        Product details

                        <p class="small text-muted mt-2">

                        </p>
                    </legend>
                </div>
                <div class="col-12 col-md-7 shadow-sm">

                    <div class="bg-white d-flex flex-column layout-wrapper rounded-top">
                                                            <fieldset class="mb-3" data-async>


                <div class="bg-white rounded shadow-sm p-4 py-4 d-flex flex-column">
                    <div class="form-group">
                        <label for="field-id-23e1f8b8e7681e5af2a66f182a8c41a9d5916081" class="form-label">Unique ID

                                </label>

                <p id="field-id-23e1f8b8e7681e5af2a66f182a8c41a9d5916081" title="Unique ID">
                        19
                    </p>

                </div>

            <div class="form-group">
                        <label for="field-slug-84fe911928d54b5502b48428b12dda85fc00a86a" class="form-label">Internal name

                                </label>

                <p id="field-slug-84fe911928d54b5502b48428b12dda85fc00a86a" title="Internal name">
                        abra-coffee-table
                    </p>

                </div>

            <div class="form-group">
                        <label for="field-name-3898511a8e888bb3bcb768023fabb414f5d97397" class="form-label">Product name
                                        <sup class="text-danger">*</sup>

                                </label>

                <div data-controller="input"
                    data-input-mask=""
                >
                    <input class="form-control" name="name" type="text" max="255" required="required" title="Product name" placeholder="Type the product name here" value="Abra" id="field-name-3898511a8e888bb3bcb768023fabb414f5d97397">
                </div>

                </div>


                </div>
            </fieldset>


                <button
                    data-controller="anibutton"
                    data-turbo="true"
                    data-anibutton-animated="true"
                    data-anibutton-label="Saving..."
                                data-action="anibutton#animate"
                            class="btn  btn-default" type="submit" form="post-form" formaction="http://localhost:8000/dashboard/products/19/edit/store">

                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="1em" height="1em" viewBox="0 0 32 32" class="me-2" role="img" fill="currentColor" componentName="orchid-icon">
                <path d="M16 0c-8.836 0-16 7.163-16 16s7.163 16 16 16c8.837 0 16-7.163 16-16s-7.163-16-16-16zM16 30.032c-7.72 0-14-6.312-14-14.032s6.28-14 14-14 14 6.28 14 14-6.28 14.032-14 14.032zM22.386 10.146l-9.388 9.446-4.228-4.227c-0.39-0.39-1.024-0.39-1.415 0s-0.391 1.023 0 1.414l4.95 4.95c0.39 0.39 1.024 0.39 1.415 0 0.045-0.045 0.084-0.094 0.119-0.145l9.962-10.024c0.39-0.39 0.39-1.024 0-1.415s-1.024-0.39-1.415 0z"></path>
            </svg>

                    Save
                </button>

                </div>


                                        </div>
                        </div>
            </fieldset>



                    <input type="hidden" name="_token" value="DrxuWS7kYgDOMAShNzlimhC2d4YB0MenqrTY8DiE">        <div class="modal fade" id="confirm-dialog" data-controller="confirm" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title text-black fw-light">Are you sure?</h4>
                            <button type="button" class="btn-close" title="Close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="p-4" data-confirm-target="message"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
                            <div data-confirm-target="button"></div>
                        </div>
                    </div>
                </div>
            </div>
                </form>
            INPUT,
            <<<OUTPUT
            <form id="post-form" class="mb-md-4" method="post" enctype="multipart/form-data" data-controller="form" data-action="keypress->form#disableKey form#submit" data-form-validation="Please check the entered data and make sure you filled all the required fields." novalidate>
                <fieldset class="row g-0 mb-3">
                    <div class="col p-0 px-3">
                        <legend class="text-black">
                            Product details
                            <p class="small text-muted mt-2"></p>
                        </legend>
                    </div>
                    <div class="col-12 col-md-7 shadow-sm">
                        <div class="bg-white d-flex flex-column layout-wrapper rounded-top">
                            <fieldset class="mb-3" data-async>
                                <div class="bg-white rounded shadow-sm p-4 py-4 d-flex flex-column">
                                    <div class="form-group">
                                        <label for="field-id-23e1f8b8e7681e5af2a66f182a8c41a9d5916081" class="form-label">Unique ID</label>
                                        <p id="field-id-23e1f8b8e7681e5af2a66f182a8c41a9d5916081" title="Unique ID">19</p>
                                    </div>
                                    <div class="form-group">
                                        <label for="field-slug-84fe911928d54b5502b48428b12dda85fc00a86a" class="form-label">Internal name</label>
                                        <p id="field-slug-84fe911928d54b5502b48428b12dda85fc00a86a" title="Internal name">abra-coffee-table</p>
                                    </div>
                                    <div class="form-group">
                                        <label for="field-name-3898511a8e888bb3bcb768023fabb414f5d97397" class="form-label">Product name <sup class="text-danger">*</sup></label>
                                        <div data-controller="input" data-input-mask="">
                                            <input class="form-control" name="name" type="text" max="255" required="required" title="Product name" placeholder="Type the product name here" value="Abra" id="field-name-3898511a8e888bb3bcb768023fabb414f5d97397">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <button data-controller="anibutton" data-turbo="true" data-anibutton-animated="true" data-anibutton-label="Saving..." data-action="anibutton#animate" class="btn btn-default" type="submit" form="post-form" formaction="http://localhost:8000/dashboard/products/19/edit/store">
                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="1em" height="1em" viewBox="0 0 32 32" class="me-2" role="img" fill="currentColor" componentName="orchid-icon">
                                    <path d="M16 0c-8.836 0-16 7.163-16 16s7.163 16 16 16c8.837 0 16-7.163 16-16s-7.163-16-16-16zM16 30.032c-7.72 0-14-6.312-14-14.032s6.28-14 14-14 14 6.28 14 14-6.28 14.032-14 14.032zM22.386 10.146l-9.388 9.446-4.228-4.227c-0.39-0.39-1.024-0.39-1.415 0s-0.391 1.023 0 1.414l4.95 4.95c0.39 0.39 1.024 0.39 1.415 0 0.045-0.045 0.084-0.094 0.119-0.145l9.962-10.024c0.39-0.39 0.39-1.024 0-1.415s-1.024-0.39-1.415 0z"></path>
                                </svg>
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </fieldset>
            <input type="hidden" name="_token" value="DrxuWS7kYgDOMAShNzlimhC2d4YB0MenqrTY8DiE">
            <div class="modal fade" id="confirm-dialog" data-controller="confirm" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title text-black fw-light">Are you sure?</h4>
                            <button type="button" class="btn-close" title="Close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="p-4" data-confirm-target="message"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
                            <div data-confirm-target="button"></div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
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
            <<<OUTPUT
            <ul>
                <li>
                    <input type="text">
                </li>
                <li>
                    <input type="text">
                </li>
                <li>
                    <input type="text"/>
                </li>
                <li>
                    <input type="text"/>
                </li>
            </ul>
            OUTPUT,
        ];
        yield [
            <<<INPUT
                <h1>This is text</h1><h2>This is some other text <span class="nowrap">inside span</span>.</h2>
                <h3> This is text</h3><h4> This is some other text<span class="nowrap">inside span</span> . </h4>
                <h5>This is some other text <span class="nowrap">inside span</span> . </h5>
            INPUT,
            <<<OUTPUT
            <h1>This is text</h1>
            <h2>This is some other text <span class="nowrap">inside span</span>.</h2>
            <h3>This is text</h3>
            <h4>This is some other text<span class="nowrap">inside span</span> .</h4>
            <h5>This is some other text <span class="nowrap">inside span</span> .</h5>
            OUTPUT,
        ];
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
                <meta charset  ="utf-8 ">
                <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
            <meta name='viewport   ' content=" width=device-width,    initial-scale=1,
                shrink-to-fit=no">
            <meta name='   auth' content=1
                    id="auth">
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
            '<!DOCTYPE html><html><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge">' .
                '<meta name=\'viewport\' content="width=device-width, initial-scale=1, shrink-to-fit=no"><meta name=\'auth\'' .
                ' content=1 id="auth"><script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3"' .
                ' type="text/javascript"></script><meta name=\'auth\' content=1 id="auth"><title>Edit product</title><script>' .
                'Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement(\'style\'), rxEsc = /([.*+?^$()|\[\]\/\])/g' .
                '</script><meta name="robots" content="noindex"/>></head><body><pre>   something' .
                PHP_EOL . '        comes' .
                PHP_EOL . '        here </pre><textarea>' .
                PHP_EOL . '            something comes' .
                PHP_EOL . '            here too </textarea>' .
                '<div class="container-fluid" data-controller="base"><div class="row"><svg xmlns="http://www.w3.org/2000/svg" ' .
                'xmlns:xlink="http://www.w3.org/1999/xlink" reserveAspectRatio="xMidYMid meet" viewBox="0 0 111.62013 21.110666">' .
                '<g class="a"><path d="m30.286 20.4727h-4.264v-19.8613h4.264z"/></g></svg></body></html>',
        ];
    }
}
