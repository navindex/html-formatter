# HTML Formatter [![Latest Version](https://img.shields.io/github/release/navindex/html-formatter?sort=semver&label=version)](https://raw.githubusercontent.com/navindex/html-formatter/master/CHANGELOG.md)

[![Unit tests](https://github.com/navindex/html-formatter/actions/workflows/test.yml/badge.svg?branch=master)](https://github.com/navindex/html-formatter/actions/workflows/test.yml)
[![Code analysis](https://github.com/navindex/html-formatter/actions/workflows/analysis.yml/badge.svg)](https://github.com/navindex/html-formatter/actions/workflows/analysis.yml)
[![Build Status](https://img.shields.io/travis/navindex/html-formatter?branch=master)](https://app.travis-ci.com/navindex/html-formatter)
[![Coverage Status](https://coveralls.io/repos/github/navindex/html-formatter/badge.svg?branch=master)](https://coveralls.io/github/navindex/html-formatter?branch=master)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue)](https://opensource.org/licenses/MIT)

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
$formatter = new Formatter();
$output = $formatter->beautify($input);
```

## 5. Configuration

`Formatter` is using **[Simple config](https://github.com/navindex/simple-config)** to adjust its configuration settings.

| Name                | Type    | Description                                                                                         |
| :------------------ | :------ | :-------------------------------------------------------------------------------------------------- |
| `tab`               | string  | Character(s) used for indentation. Defaults to 4 spaces.                                            |
| `self-closing.tag`  | array   | Here you can add more self-closing tags. [see below](#5-1)                                          |
| `inline.tag`        | array   | Here you can add more inline tags, or change any block tags and make them inline. [see below](#5-2) |
| `formatted.tag`     | array   | Here you can add more tags to be exluded of formatting. [see below](#5-3)                           |
| `attributes.trim`   | boolean | Remove leading and trailing whitespace in the attribute values.                                     |
| `attribute.cleanup` | boolean | Replace all whitespaces with a single space in the attribute values.                                |
| `cdata.cleanup`     | boolean | Replace all whitespaces with a single space in CDATA.                                               |
| `cdata.trim`        | boolean | Remove leading and trailing whitespace in CDATA.                                                    |

<a name='5-1'></a>

### 5.1. Inline and block elements

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

You can set additional inline elements by adding them to the `inline.tag` option.

```php
use Navindex\HTMLFormatter\Formatter;

$formatter = new Formatter();
$config = $formatter->getConfig();
$config->append('inline.tag', 'foo')->subtract('inline.tag', ['bar', 'baz']);
$formatter->setConfig($config);
```

<a name='5-2'></a>

### 5.2. Self-closing (empty) elements

An empty element is an element from HTML, SVG, or MathML that cannot have any child nodes (i.e., nested elements or text nodes).

HTML Formatter identifies the following elements as "inline":

-   `area`, `base`, `br`, `col`, `command`, `embed`, `hr`, `img`, `input`,
-   `keygen`, `link`, `menuitem`, `meta`, `param`, `source`, `track`, `wbr`,
-   `animate`, `stop`, `path`, `circle`, `line`, `polyline`, `rect`, `use`

This is a subset of the empty elements defined in the [MDN](https://developer.mozilla.org/en-US/docs/Glossary/empty_element).
All other elements require closing tag.

You can set additional self-closing elements by adding them to the `self-closing.tag` option.

```php
use Navindex\HTMLFormatter\Formatter;

$formatter = new Formatter();
$formatter->setConfig($formatter->getConfig()->append('self-closing.tag', ['foo', 'bar']));
```

<a name='5-3'></a>

### 5.3. Preformatted elements</a>

Specific element will be not touched by the formatter. The built in preformatted elements are

-   `script`, `pre`, `textarea`.

You can exclude additional elements by adding them to the `formatted.tag` option.

There settings for all the formatted tags are the following:

| Setting                       | Type    | Description                                                 |
| :---------------------------- | :------ | :---------------------------------------------------------- |
| `formatted.tag.cleanup-empty` | boolean | Removes the inner content if it has only whitespace.        |
| `formatted.tag.opening-break` | boolean | Inserts a line break after the opening tag.                 |
| `formatted.tag.closing-break` | boolean | Inserts a line break before the closing tag.                |
| `formatted.tag.trim`          | boolean | Removes the leading and trailing whitespace of the content. |

You can also change the settings for a specific tag. For example, disabling the `cleanup-empty` setting for the `script` tag looks like this:

```php
use Navindex\HTMLFormatter\Formatter;

$formatter = new Formatter();
$config = $formatter->getConfig();
$config->set('formatted.tag.script.cleanup-empty', false);
$formatter->setConfig($config);
```

### 5.4. Attributes

Additional settings for formatted tags are the following:

| Setting         | Type    | Description                                                 |
| :-------------- | :------ | :---------------------------------------------------------- |
| `cleanup-empty` | boolean | Removes the inner content if it has only whitespace.        |
| `opening-break` | boolean | Inserts a line break after the opening tag.                 |
| `closing-break` | boolean | Inserts a line break before the closing tag.                |
| `trim`          | boolean | Removes the leading and trailing whitespace of the content. |

## 6. Methods

| Name             | Attributes          | Description                          | Example                           |
| :--------------- | :------------------ | :----------------------------------- | :-------------------------------- |
| `constructor`    | Config\|array\|null | Creates a `Formatter` class instance | `$f = new Formatter($config);`    |
| `getConfig`      | -                   | Configuration getter                 | `$config = $f->getConfig();`      |
| `getConfigArray` | -                   | Configuration getter                 | `$config = $f->getConfigArray();` |
| `setConfig`      | Config\|array\|null | Configuration setter                 | `$f->setConfig($config);`         |
| `beautify`       | string              | Beautifies the input string          | `$output = $f->beautify($html);`  |
| `minify`         | string              | Minifies the input string            | `$output = $f->minify($html);`    |

<!-- CLI is currently not available -->

## 7. Known issues

As any recent repository, it could have several issues. Use it wisely.

| TODO list                                                        |
| :--------------------------------------------------------------- |
| Remove whitespace before `>` in the opening tags                 |

## 8. Credits

Thanks to **[Gajus Kuizinas](https://github.com/gajus)** for originally creating [gajus/dindent](https://github.com/gajus/dindent) and all the other developers who are tirelessly working on it. HTML Formatter was inspired by Dindent.

## 9. About Navindex

Navindex is a web development agency in Melbourne, Australia. You'll find an overview of our cmpany [on our website](https://www.navindex.com.au).
