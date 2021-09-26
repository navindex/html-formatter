<?php

declare(strict_types=1);

namespace Navindex\HtmlFormatter\Tests;

use Iterator;
use Navindex\HtmlFormatter\Formatter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Navindex\HtmlFormatter\Formatter
 */
final class FormatterTest extends TestCase
{
    /**
     * @dataProvider providerGetConfig
     *
     * @param null|array <string, mixed> $config
     * @param array      <string, mixed> $expected
     *
     * @return void
     */
    public function testGetConfig(?array $config, array $expected)
    {
        $f = new Formatter($config);
        $this->assertSame($expected, $f->getConfig()->toArray());
    }

    // /**
    //  * @dataProvider providerContent
    //  *
    //  * @param string                     $html
    //  * @param null|array <string, mixed> $config
    //  * @param string                     $expected
    //  *
    //  * @return void
    //  */
    // public function testBeautify(string $html, array $config, string $expected)
    // {
    //     $f = new Formatter($config);
    //     $this->assertSame($expected, $f->beautify($html));
    // }

    // /**
    //  * @dataProvider providerContent
    //  *
    //  * @param string                     $html
    //  * @param null|array <string, mixed> $config
    //  * @param string                     $expected
    //  *
    //  * @return void
    //  */
    // public function testMinify(string $html, array $config, string $expected)
    // {
    //     $f = new Formatter($config);
    //     $this->assertSame($expected, $f->minify($html));
    // }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string|array>>
     */
    public function providerGetConfig(): Iterator
    {
        $config = (new class() extends Formatter
        {
            /**
             * @return null|array <string, mixed>
             */
            public function _getConfig(): ?array
            {
                return $this->config->toArray();
            }
        })->_getConfig();

        yield [null, $config];
        yield [[], $config];
        yield [$config, $config];
        yield [
            [
                'tab'         => 'x',
                'empty_tags'  => ['new_empty_tag'],
                'inline_tags' => ['new_inline_tag'],
                'keep_format' => ['new_preformatted_tag'],
                'attribute_trim' => true,
                'attribute_cleanup' => true,
                'cdata_cleanup' => true,
            ],
            [
                'tab'         => 'x',
                'empty_tags'  => ['new_empty_tag'],
                'inline_tags' => ['new_inline_tag'],
                'keep_format' => ['new_preformatted_tag'],
                'attribute_trim' => true,
                'attribute_cleanup' => true,
                'cdata_cleanup' => true,
            ]
        ];
    }





















    // /**
    //  * Data provider.
    //  *
    //  * @return \Iterator <int, array <int, string|array>>
    //  */
    // public function providerPreformats(): Iterator
    // {
    //     $options = ['keep_format' => ['script', 'pre', 'textarea']];

    //     yield [
    //         <<<INPUT
    //         <html>
    //             <head>
    //                 <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript"></script>
    //                 <meta name='auth' content=1 id="auth">
    //                 <title>
    //                     Edit product
    //                 </title>
    //                 <script> Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement('style'), rxEsc = /([.*+?^$()|\[\]\/\\])/g</script>

    //                 <meta name="robots" content="noindex" />>
    //             </head>
    //             <body>
    //                 <pre>   something
    //                 comes
    //                 here </pre>
    //                 <textarea>
    //                     something comes
    //                     here too </textarea>
    //             </body></html>
    //         INPUT,
    //         $options,
    //         [
    //             '<script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript"></script>',
    //             '<script> Sfdump = window.Sfdump || (function (doc) \{ var refStyle = doc.createElement(\'style\'), rxEsc = /([.*+?^$()|\[\]\/\\])/g</script>',
    //             "<pre>   something\n        comes\n        here </pre>",
    //             "<textarea>\n            something comes\n            here too </textarea>",
    //         ],
    //         <<<OUTPUT
    //         <html>
    //             <head>
    //                 ᐃpre:0:preᐃ
    //                 <meta name='auth' content=1 id="auth">
    //                 <title>
    //                     Edit product
    //                 </title>
    //                 ᐃpre:1:preᐃ

    //                 <meta name="robots" content="noindex" />>
    //             </head>
    //             <body>
    //                 ᐃpre:2:preᐃ
    //                 ᐃpre:3:preᐃ
    //             </body></html>
    //         OUTPUT,
    //     ];
    // }

    // /**
    //  * Data provider.
    //  *
    //  * @return \Iterator <int, array <int, string|array>>
    //  */
    // public function providerAttributes(): Iterator
    // {
    //     yield [
    //         <<<INPUT
    //         <html lang = "en_AU" data-controller="html-load">
    //             <head>
    //                 <meta charset  ="utf-8  ">
    //                 <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
    //                 <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    //                 <script src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3" type="text/javascript" defer ></script>
    //                 <meta name='auth' content=1
    //                         id="auth">
    //                 <title>
    //                     Edit product
    //                 </title>
    //                 <link rel="stylesheet" href="http://localhost/common-vendor.css">
    //                 <link rel="stylesheet" href="http://localhost/css/dashboard.css">
    //                 <meta name="robots" content="noindex" />>
    //             </head>
    //             <body>
    //                 <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
    //                 <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" reserveAspectRatio="xMidYMid meet" viewBox="0 0 111.62013 21.110666" >
    //                     <g class="a">
    //                         <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
    //                     </g>
    //                 </svg>
    //                 <button data-anibutton-label="Deleting..." data-action="click->anibutton#confirm" data-anibutton-confirm="Are you sure you want to delete this product?">
    //                     Delete
    //                 </button>
    //             </body></html>
    //         INPUT,
    //         [
    //             'lang = "en_AU"',
    //             'data-controller="html-load"',
    //             'charset  ="utf-8  "',
    //             'http-equiv=   "X-UA-Compatible"',
    //             'content="IE=edge"',
    //             'name="viewport"',
    //             'content="width=device-width, initial-scale=1, shrink-to-fit=no"',
    //             'src="http://localhost/js/manifest.js?id=8f036cd511d2b70af1d3"',
    //             'type="text/javascript"',
    //             'name=\'auth\'',
    //             'id="auth"',
    //             'rel="stylesheet"',
    //             'href="http://localhost/common-vendor.css"',
    //             'rel="stylesheet"',
    //             'href="http://localhost/css/dashboard.css"',
    //             'name="robots"',
    //             'content="noindex"',
    //             'class="  header-brand   order-last "',
    //             'href="http://localhost/dashboard/"',
    //             'xmlns="http://www.w3.org/2000/svg"',
    //             'xmlns:xlink="http://www.w3.org/1999/xlink"',
    //             'reserveAspectRatio="xMidYMid meet"',
    //             'viewBox="0 0 111.62013 21.110666"',
    //             'class="a"',
    //             'd="m30.286 20.4727h-4.264v-19.8613h4.264z"',
    //             'data-anibutton-label="Deleting..."',
    //             'data-action="click->anibutton#confirm"',
    //             'data-anibutton-confirm="Are you sure you want to delete this product?"',
    //         ],
    //         <<<OUTPUT
    //         <html ᐃattr:0:attrᐃ ᐃattr:1:attrᐃ>
    //             <head>
    //                 <meta ᐃattr:2:attrᐃ>
    //                 <meta ᐃattr:3:attrᐃ ᐃattr:4:attrᐃ>
    //                 <meta ᐃattr:5:attrᐃ ᐃattr:6:attrᐃ>
    //                 <script ᐃattr:7:attrᐃ ᐃattr:8:attrᐃ defer ></script>
    //                 <meta ᐃattr:9:attrᐃ content=1
    //                         ᐃattr:10:attrᐃ>
    //                 <title>
    //                     Edit product
    //                 </title>
    //                 <link ᐃattr:11:attrᐃ ᐃattr:12:attrᐃ>
    //                 <link ᐃattr:13:attrᐃ ᐃattr:14:attrᐃ>
    //                 <meta ᐃattr:15:attrᐃ ᐃattr:16:attrᐃ />>
    //             </head>
    //             <body>
    //                 <a ᐃattr:17:attrᐃ ᐃattr:18:attrᐃ >   Dashboard   </a>
    //                 <svg ᐃattr:19:attrᐃ ᐃattr:20:attrᐃ ᐃattr:21:attrᐃ ᐃattr:22:attrᐃ >
    //                     <g ᐃattr:23:attrᐃ>
    //                         <path ᐃattr:24:attrᐃ />
    //                     </g>
    //                 </svg>
    //                 <button ᐃattr:25:attrᐃ ᐃattr:26:attrᐃ ᐃattr:27:attrᐃ>
    //                     Delete
    //                 </button>
    //             </body></html>
    //         OUTPUT,
    //     ];
    // }

    // /**
    //  * Data provider.
    //  *
    //  * @return \Iterator <int, array <int, string|array>>
    //  */
    // public function providerAttributeOptions(): Iterator
    // {
    //     yield [
    //         <<<INPUT
    //         <html lang = "  en_AU">
    //             <head>
    //                 <meta charset  ="utf-8 ">
    //                 <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
    //                 <meta name='viewport   ' content=" width=device-width,    initial-scale=1,
    //                     shrink-to-fit=no">
    //                 <meta name='   auth' content=1
    //                         id="auth">
    //             </head>
    //             <body>
    //                 <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
    //             </body></html>
    //         INPUT,
    //         [
    //             'attribute_trim' => true,
    //         ],
    //         <<<OUTPUT
    //         <html lang = "en_AU">
    //             <head>
    //                 <meta charset  ="utf-8">
    //                 <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
    //                 <meta name='viewport' content="width=device-width,    initial-scale=1,
    //                     shrink-to-fit=no">
    //                 <meta name='auth' content=1
    //                         id="auth">
    //             </head>
    //             <body>
    //                 <a class="header-brand   order-last" href="http://localhost/dashboard/" >   Dashboard   </a>
    //             </body></html>
    //         OUTPUT,
    //     ];
    // }

    // /**
    //  * Data provider.
    //  *
    //  * @return \Iterator <int, array <int, string|array>>
    //  */
    // public function providerCdata(): Iterator
    // {
    //     yield [
    //         <<<INPUT
    //         <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    //             <style><![CDATA[
    //                 .a {
    //                     fill: #28231d;
    //                 }
    //             ]]></style>
    //             <g class="a">
    //                 <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
    //                 <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
    //             </g>
    //         </svg>
    //         INPUT,
    //         [
    //             "<![CDATA[\n        .a {\n            fill: #28231d;\n        }\n    ]]>",
    //         ],
    //         <<<OUTPUT
    //         <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    //             <style>ᐃcdata:0:cdataᐃ</style>
    //             <g class="a">
    //                 <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" />
    //                 <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
    //             </g>
    //         </svg>
    //         OUTPUT,
    //     ];
    // }

    // /**
    //  * Data provider.
    //  *
    //  * @return \Iterator <int, array <int, string|array>>
    //  */
    // public function providerInlines(): Iterator
    // {
    //     yield [
    //         <<<INPUT
    //         <body>
    //             <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
    //             <br><br />
    //             <span>This is <strong>bold</strong>.</span>
    //             <button data-anibutton-label="Deleting..." data-action="click->anibutton#confirm" data-anibutton-confirm="Are you sure you want to delete this product?">
    //                 Delete
    //             </button>
    //         </body>
    //         INPUT,
    //         [
    //             '<a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>',
    //             '<strong>bold</strong>',
    //             '<button data-anibutton-label="Deleting..." data-action="click->anibutton#confirm" ' .
    //                 'data-anibutton-confirm="Are you sure you want to delete this product?">' .
    //                 "\n        Delete\n    </button>",
    //             '<span>This is ᐃinline:1:inlineᐃ.</span>'
    //         ],
    //         <<<OUTPUT
    //         <body>
    //             ᐃinline:0:inlineᐃ
    //             <br><br />
    //             ᐃinline:3:inlineᐃ
    //             ᐃinline:2:inlineᐃ
    //         </body>
    //         OUTPUT,
    //     ];
    // }

    // /**
    //  * Data provider.
    //  *
    //  * @return \Iterator <int, string[]>
    //  */
    // public function providerWhitespace(): Iterator
    // {
    //     yield [
    //         <<<INPUT
    //         <svg
    //             xmlns="http://www.w3.org/2000/svg"
    //             xmlns:xlink="http://www.w3.org/1999/xlink" >
    //             <style><![CDATA[
    //                 .a {
    //                     fill: #28231d;
    //                 }
    //             ]]></style>
    //             <g      class="a">
    //                 <path d="m30.286 20.4727h-4.264v-19.8613h4.264z"              />
    //                 <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
    //             </g>
    //         </svg>
    //         INPUT,
    //         '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" > ' .
    //             '<style><![CDATA[ .a { fill: #28231d; } ]]></style> ' .
    //             '<g class="a"> <path d="m30.286 20.4727h-4.264v-19.8613h4.264z" /> ' .
    //             '<path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" /> </g> </svg>',
    //     ];
    //     yield [
    //         <<<INPUT
    //         <html lang = "  en_AU">
    //             <head>
    //                 <meta charset  ="utf-8  ">
    //                 <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
    //                 <meta name="viewport" content=" width=device-width,    initial-scale=1,
    //                     shrink-to-fit=no">
    //                 <meta name="auth" content="1 "
    //                         id="auth">
    //             </head>
    //             <body>
    //                 <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
    //             </body></html>
    //         INPUT,
    //         '<html lang = " en_AU"> <head> <meta charset ="utf-8 "> <meta http-equiv= "X-UA-Compatible" content="IE=edge"> ' .
    //             '<meta name="viewport" content=" width=device-width, initial-scale=1, shrink-to-fit=no"> ' .
    //             '<meta name="auth" content="1 " id="auth"> </head> <body> ' .
    //             '<a class=" header-brand order-last " href="http://localhost/dashboard/" > Dashboard </a> ' .
    //             '</body></html>',
    //     ];
    // }

    // /**
    //  * Data provider.
    //  *
    //  * @return \Iterator <int, array<int, string|array>>
    //  */
    // public function providerIndent(): Iterator
    // {
    //     yield [
    //         <<<INPUT
    //         <svg
    //             xmlns="http://www.w3.org/2000/svg"
    //             xmlns:xlink="http://www.w3.org/1999/xlink" >
    //         <g class="a"><path d="m30.286 20.4727h-4.264v-19.8613h4.264z"              />
    //             <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" /></g>
    //         </svg>
    //         INPUT,
    //         <<<OUTPUT
    //         <svg
    //             xmlns="http://www.w3.org/2000/svg"
    //             xmlns:xlink="http://www.w3.org/1999/xlink" >
    //             <g class="a">
    //                 <path d="m30.286 20.4727h-4.264v-19.8613h4.264z"              />
    //                 <path d="m33.8616 20.472v-19.8613h4.264v16.56h8.6107v3.3013z" />
    //             </g>
    //         </svg>
    //         OUTPUT,
    //     ];
    //     yield [
    //         <<<INPUT
    //         <html lang = "  en_AU">
    //             <head>
    //                 <meta charset  ="utf-8  ">
    //                     <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
    //                     <meta name="viewport" content=" width=device-width,    initial-scale=1,
    //                         shrink-to-fit=no">
    //                 <meta name="auth" content="1 "
    //                         id="auth">
    //             </head><body>
    //             <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
    //             </body></html>
    //         INPUT,
    //         <<<OUTPUT
    //         <html lang = "  en_AU">
    //             <head>
    //                 <meta charset  ="utf-8  ">
    //                 <meta http-equiv=   "X-UA-Compatible" content="IE=edge">
    //                 <meta name="viewport" content=" width=device-width,    initial-scale=1,
    //                         shrink-to-fit=no">
    //                 <meta name="auth" content="1 "
    //                         id="auth">
    //             </head>
    //             <body>
    //                 <a class="  header-brand   order-last " href="http://localhost/dashboard/" >   Dashboard   </a>
    //             </body>
    //         </html>
    //         OUTPUT,
    //     ];
    // }

    // /**
    //  * Data provider.
    //  *
    //  * @return \Iterator <int, array<int, string|array>>
    //  */
    // public function providerIndentWithLog(): Iterator
    // {
    //     yield [
    //         <<<INPUT
    //         <html>
    //         <head> something </head>
    //         <body> something,
    //         something else </body>
    //         </html>
    //         INPUT,
    //         [
    //             [
    //                 'rule'    => 'INCREASE INDENT',
    //                 'pattern' => 'OPENING TAG',
    //                 'subject' => "<html>\n<head> something </head>\n<body> something,\nsomething else </body>\n</html>",
    //                 'matches' => "<html>",
    //             ],
    //             [
    //                 'rule'    => 'DISCARD',
    //                 'pattern' => 'WHITESPACE',
    //                 'subject' => "\n<head> something </head>\n<body> something,\nsomething else </body>\n</html>",
    //                 'matches' => "\n",
    //             ],
    //             [

    //                 'rule'    => 'KEEP INDENT',
    //                 'pattern' => 'BLOCK TAG',
    //                 'subject' => "<head> something </head>\n<body> something,\nsomething else </body>\n</html>",
    //                 'matches' => "<head> something </head>",
    //             ],
    //             [
    //                 'rule' => 'DISCARD',
    //                 'pattern' => 'WHITESPACE',
    //                 'subject' => "\n<body> something,\nsomething else </body>\n</html>",
    //                 'matches' => "\n",
    //             ],
    //             [
    //                 'rule' => 'KEEP INDENT',
    //                 'pattern' => 'BLOCK TAG',
    //                 'subject' => "<body> something,\nsomething else </body>\n</html>",
    //                 'matches' => "<body> something,\nsomething else </body>",
    //             ],
    //             [
    //                 'rule' => 'DISCARD',
    //                 'pattern' => 'WHITESPACE',
    //                 'subject' => "\n</html>",
    //                 'matches' => "\n",
    //             ],
    //             [
    //                 'rule' => 'DECREASE INDENT',
    //                 'pattern' => 'CLOSING TAG',
    //                 'subject' => "</html>",
    //                 'matches' => "</html>",
    //             ],
    //         ],
    //         <<<OUTPUT
    //         <html>
    //             <head>
    //                 something
    //             </head>
    //             <body>
    //                 something,
    //                 something else
    //             </body>
    //         </html>
    //         OUTPUT,

    //     ];
    // }

    // /**
    //  * Data provider.
    //  *
    //  * @return \Iterator <int, array <int, bool|string>>
    //  */
    // public function providerWhen(): Iterator
    // {
    //     yield [true,  'abc', 'xyz', 'xyz'];
    //     yield [false, 'abc', 'xyz', 'abc'];
    // }
}
