# HTML Formatter

<!-- [![Build Status](https://travis-ci.org/gajus/html formatter.png?branch=master)](https://travis-ci.org/navindex/html-formatter)
[![Coverage Status](https://coveralls.io/repos/gajus/html formatter/badge.png?branch=master)](https://coveralls.io/r/navindex/html-formatter?branch=master)
[![Latest Stable Version](https://poser.pugx.org/gajus/html formatter/version.png)](https://packagist.org/packages/navindex/html-formatter)
[![License](https://poser.pugx.org/gajus/html formatter/license.png)](https://packagist.org/packages/navindex/html-formatter) -->

<!-- [![Latest Version](https://img.shields.io/github/release/spatie/analytics-statistics.svg?style=flat-square)](https://github.com/spatie/analytics-statistics/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/analytics-statistics/master.svg?style=flat-square)](https://travis-ci.org/spatie/analytics-statistics)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/analytics-statistics.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/analytics-statistics)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/analytics-statistics.svg?style=flat-square)](https://packagist.org/packages/spatie/analytics-statistics) -->

## 1. What Is It

HTML Formatter will beautify or minify your HTML string for development and testing. Dedicated for those who suffer from reading a template engine produced markup.

## 2. What Is It Not

HTML Formatter will not sanitize or otherwise manipulate your output beyond indentation and whitespace replacement. HTML Formatter will only add indentation, without otherwise affecting the markup.

If you are looking to remove malicious code or make sure that your document is standards compliant, consider the following alternatives:

-   [HTML Purifier](https://github.com/Exercise/HTMLPurifierBundle)
-   [DOMDocument::$formatOutput](http://www.php.net/manual/en/class.domdocument.php)
-   [Tidy](http://www.php.net/manual/en/book.tidy.php)
-   [Chrome DevTools](https://developers.google.com/chrome-developer-tools/)

If you need to format your code in the development environment, beware that earlier mentioned libraries will attempt to fix your markup.

## 3. Installation

This package can be installed through [Composer](https://getcomposer.org/).

```bash
composer require navindex/html-formatter
```

## 4. Usage

```php
use Navindex\HTMLFormatter\Formatter;

$input = 'This is your HTML code.';
$options = [
    'tab' => '    ',
    'empty_tags' => [],
    'inline_tags' => [],
    'attribute_trim' => true,
    'attribute_cleanup' => true,
    'cdata_cleanup' => true,
];

$formatter = new Formatter($options);
$output = $formatter->beautify($input);
```

## 5. Options

`Formatter` constructor accepts the following options that control indentation:

| Name                | Data type | Default    | Description                                                                       |
| :------------------ | :-------- | :--------- | :-------------------------------------------------------------------------------- |
| `tab`               | string    | _4 spaces_ | Character(s) used for indentation. Defaults to 4 spaces.                          |
| `empty_tags`        | array     | see below  | Here you can add more self-closing tags.                                          |
| `inline_tags`       | array     | see below  | Here you can add more inline tags, or change any block tags and make them inline. |
| `attribute_trim`    | boolean   | _false_    | Remove leading and trailing whitespace in the attribute values.                   |
| `attribute_cleanup` | boolean   | _false_    | Replace all whitespaces with a single space in the attribute values.              |
| `cdata_cleanup`     | boolean   | _false_    | Replace all whitespaces with a single space in CDATA.                             |

### 5.1. Inline/block elements

HTML elements are either "inline" elements or "block-level" elements.

An inline element occupies only the space bounded by the tags that define the inline element. The following example demonstrates the inline element's influence:

```html
<p>This is an <span>inline</span> element within a block element.</p>
```

A block-level element occupies the entire space of its parent element (container), thereby creating a "block." Browsers typically display the block-level element with a new line both before and after the element. The following example demonstrates the block-level element's influence:

```html
<div>
    <p>This is a block element within a block element.</p>
</div>
```

HTML Formatter identifies the following elements as "inline":

-   `a`, `abbr`, `acronym`, `b`, `bdo`, `big`, `br`, `button`, `cite`, `code`, `dfn`, `em`,
-   `i`, `img`, `kbd`, `label`, `samp`, `small`, `span`, `strong`, `sub`, `sup`, `tt`, `var`

This is a subset of the inline elements defined in the [MDN](https://developer.mozilla.org/en-US/docs/Web/HTML/Inline_elements).
All other elements are treated as block.

You can set additional inline elements by adding them to the `inline_tags` option.

```php
use Navindex\HTMLFormatter\Formatter;

$formatter = new Formatter();
$options = [
    'inline_tags' => ['foo', 'bar'],
];
$formatter->options($options);
```

### 5.2. Self-closing (empty) elements

An empty element is an element from HTML, SVG, or MathML that cannot have any child nodes (i.e., nested elements or text nodes).

HTML Formatter identifies the following elements as "inline":

-   `area`, `base`, `br`, `col`, `command`, `embed`, `hr`, `img`, `input`, `keygen`,
-   `link`, `menuitem`, `meta`, `meta`, `param`, `path`, `source`, `track`, `use`, `wbr`

This is a subset of the empty elements defined in the [MDN](https://developer.mozilla.org/en-US/docs/Glossary/empty_element).
All other elements require closing tag.

You can set additional self-closing elements by adding them to the `empty_tags` option.

```php
use Navindex\HTMLFormatter\Formatter;

$options = [
    'empty_tags' => ['foo', 'bar'],
];
$formatter = new Formatter($options);
```

## 6. Methods

| Name          | Attributes  | Description                          | Example                                            |
| :------------ | :---------- | :----------------------------------- | :------------------------------------------------- |
| `constructor` | array\|null | Creates a `Formatter` class instance | `$formatter = new Formatter(['tag' => "\t"]);`     |
| `options`     | array       | Configuration setter                 | `$formatter->options(['attribute_trim' => true]);` |
| `beautify`    | string      | Beautifies the input string          | `$output = $formatter->beautify($html);`           |
| `minify`      | string      | Minifies the input string            | `$output = $formatter->minify($html);`             |

<!--
# CLI

HTML Formatter can be used via the CLI script `./bin/html formatter.php`.

```sh
php ./bin/html formatter.php

Indent HTML.

Options:
    --input=./input_file.html
        Input file
    --indentation_character="    "
        Character(s) used for indentation. Defaults to 4 whitespace characters.
    --inline
        A list of comma separated "inline" element names.
    --block
        A list of comma separated "block" element names.

Examples:
    ./html formatter.php --input="./input.html"
        Indent "input.html" file and print the output to STDOUT.

    ./html formatter.php --input="./input.html" | tee ./output.html
        Indent "input.html" file and dump the output to "output.html".

    ./html formatter.php --input="./input.html" --indentation_character="\t"
        Indent "input.html" file using tab to indent the markup.

    ./html formatter.php --input="./input.html" --inline="div,p"
        Indent "input.html" file treating <div> and <p> elements as inline.

    ./html formatter.php --input="./input.html" --block="span,em"
        Indent "input.html" file treating <span> and <em> elements as block.
``` -->

## 7. Known issues

As any recent repository, it could have several issues. Use it wisely.

## 8. Credits

Thanks to **Gajus Kuizinas** for originally creating [gajus/dindent](https://github.com/gajus/dindent) and all the other developers who are tirelessly working on it. HTML Formatter was heavily influenced by Dindent.
