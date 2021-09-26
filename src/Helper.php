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

    /**
     * Test if array is an associative array
     *
     * Note that this function will return false if an array is empty. Meaning
     * empty arrays will be treated as if they are not associative arrays.
     *
     * @param mixed[] $array
     *
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        return 0 < count($array) && count(array_filter(array_keys($array), 'is_string')) == count($array);
    }

    /**
     * If the given value is not an array, wrap it in one.
     *
     * @param mixed $value
     *
     * @return array
     */
    public static function wrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }
}
