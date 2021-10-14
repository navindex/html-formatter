<?php

namespace Navindex\HtmlFormatter;

abstract class Pattern
{
    const MARKER = 'ᐃ';

    const PRE = '/<(%s)\b[^>]*>([\s\S]*?)<\/\1>/mi';
    const INLINE = '/<(%s)\b([^>]*)>([^<]*)<\/\1>/mi';
    const ATTRIBUTE = '/([a-z0-9_:-]+)\s*=\s*(["\'])([\s\S]*?)\2/mi';
    const CDATA = '/<!\[CDATA\[([\s\S]*?)\]\]>/mi';
    const WHITESPACE = '/(\s+)/mi';

    const IS_DOCTYPE = '/^<!([^>]*)>/';
    const IS_BLOCK = '/^<(\w+)\b[^>]*>([^<]*?)<\/\1>/';
    const IS_SELFCLOSING = '/^<(%s)\b[^>]*>([^<]*?<\/\1>)?/';
    const IS_OPENING = '/^<(\w+)\b[^>]*>/';
    const IS_CLOSING = '/^<\/([^>]*)>/';
    const IS_TEXT = '/^[^\sᐃ<]+[^ᐃ<]*(?=\s?(?:ᐃ|<))/';
    const IS_WHITESPACE = '/^(\s+)/';
    const IS_MARKER = '/^ᐃ(\w+)\b:[0-9]+:\1ᐃ/';

    const TRAILING_SPACE_IN_OPENING_TAG = '/(<[^>]*?)\h+(\/?>)/mi';
    const SPACE_BEFORE_CLOSING_TAG = '/(>[^>\v]*?)\h+(<\/)/mi';
    const SPACE_AFTER_OPENING_TAG = '/(<\w+\b[^>]*>)\h+(\S)/mi';
    const TRAILING_LINE_SPACE = '/(\S*)\h*(\v)/mi';
    const MOVE_TO_LEFT = '/^\h+(<(%s)\b[^>]*?>\v[\s\S]*?(?:<\/\2>))/mi';
}
