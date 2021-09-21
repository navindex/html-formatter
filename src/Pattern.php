<?php

namespace Navindex\HtmlFormatter;

abstract class Pattern
{
    const MARKER = 'ᐃ';

    const PRE    = '/<(%s)\b[^>]*>([\s\S]*?)<\/\1>/mi';
    const INLINE = '/<(%s)[^>]*>(?:[^<]*)<\/\1>/mi';
    const ATTRIBUTE = '/([a-z0-9_-]+)\s*=\s*(["\'])((?:.|\n)*?)\2/mi';
    const CDATA = '/<!\[CDATA\[(?:.|\n)*?\]\]>/mi';
    const WHITESPACE = '/(\s+)/mi';

    const IS_DOCTYPE = '/^<!([^>]*)>/';
    const IS_BLOCK = '/^(<([a-z]+)(?:[^>]*)>(?:[^<]*)<\/(?:\2)>)/';
    const IS_EMPTY_OPENING = '/^<(%s)([^>]*)>/';
    const IS_EMPTY_CLOSING = '/^<(.+)\/>/';
    const IS_OPENING = '/^<[^\/]([^>]*)>/';
    const IS_CLOSING = '/^<\/([^>]*)>/';
    const IS_TEXT = '/^([^<ᐃ]+)/';
    const IS_WHITESPACE = '/^(\s+)/';
    const IS_MARKER = '/^(ᐃ([a-z]+):[0-9]+:\2ᐃ)/';
}
