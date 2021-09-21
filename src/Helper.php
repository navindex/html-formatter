<?php

namespace Navindex\HtmlFormatter;

use Navindex\HtmlFormatter\Pattern;

abstract class Helper
{
    /**
     * Creates placeholder from a word.
     *
     * @param string $word
     *
     * @return string
     */
    public static function placeholder(string $word): string
    {
        $word = mb_strtolower(str_replace(' ', '', $word));

        return Pattern::MARKER . $word . ':%s:' . $word . Pattern::MARKER;
    }

    /**
     * Caps a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $cap
     *
     * @return string
     */
    public static function finish(string $value, string $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:' . $quoted . ')+$/u', '', $value) . $cap;
    }
}
