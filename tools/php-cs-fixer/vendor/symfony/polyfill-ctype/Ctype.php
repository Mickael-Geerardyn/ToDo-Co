<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Polyfill\Ctype;

/**
 * Ctype implementation through regex.
 *
 * @internal
 *
 * @author Gert de Pagter <BackEndTea@gmail.com>
 */
final class Ctype
{
    /**
     * Returns TRUE if every character in text is either a letter or a digit, FALSE otherwise.
     *
     * @see https://php.net/ctype-alnum
     *
     *
     * @return bool
     */
    public static function ctype_alnum(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^A-Za-z0-9]/', $text);
    }

    /**
     * Returns TRUE if every character in text is a letter, FALSE otherwise.
     *
     * @see https://php.net/ctype-alpha
     *
     *
     * @return bool
     */
    public static function ctype_alpha(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^A-Za-z]/', $text);
    }

    /**
     * Returns TRUE if every character in text is a control character from the current locale, FALSE otherwise.
     *
     * @see https://php.net/ctype-cntrl
     *
     *
     * @return bool
     */
    public static function ctype_cntrl(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^\x00-\x1f\x7f]/', $text);
    }

    /**
     * Returns TRUE if every character in the string text is a decimal digit, FALSE otherwise.
     *
     * @see https://php.net/ctype-digit
     *
     *
     * @return bool
     */
    public static function ctype_digit(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^0-9]/', $text);
    }

    /**
     * Returns TRUE if every character in text is printable and actually creates visible output (no white space), FALSE otherwise.
     *
     * @see https://php.net/ctype-graph
     *
     *
     * @return bool
     */
    public static function ctype_graph(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^!-~]/', $text);
    }

    /**
     * Returns TRUE if every character in text is a lowercase letter.
     *
     * @see https://php.net/ctype-lower
     *
     *
     * @return bool
     */
    public static function ctype_lower(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^a-z]/', $text);
    }

    /**
     * Returns TRUE if every character in text will actually create output (including blanks). Returns FALSE if text contains control characters or characters that do not have any output or control function at all.
     *
     * @see https://php.net/ctype-print
     *
     *
     * @return bool
     */
    public static function ctype_print(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^ -~]/', $text);
    }

    /**
     * Returns TRUE if every character in text is printable, but neither letter, digit or blank, FALSE otherwise.
     *
     * @see https://php.net/ctype-punct
     *
     *
     * @return bool
     */
    public static function ctype_punct(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^!-\/\:-@\[-`\{-~]/', $text);
    }

    /**
     * Returns TRUE if every character in text creates some sort of white space, FALSE otherwise. Besides the blank character this also includes tab, vertical tab, line feed, carriage return and form feed characters.
     *
     * @see https://php.net/ctype-space
     *
     *
     * @return bool
     */
    public static function ctype_space(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^\s]/', $text);
    }

    /**
     * Returns TRUE if every character in text is an uppercase letter.
     *
     * @see https://php.net/ctype-upper
     *
     *
     * @return bool
     */
    public static function ctype_upper(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^A-Z]/', $text);
    }

    /**
     * Returns TRUE if every character in text is a hexadecimal 'digit', that is a decimal digit or a character from [A-Fa-f] , FALSE otherwise.
     *
     * @see https://php.net/ctype-xdigit
     *
     *
     * @return bool
     */
    public static function ctype_xdigit(mixed $text)
    {
        $text = self::convert_int_to_char_for_ctype($text, __FUNCTION__);

        return \is_string($text) && '' !== $text && !preg_match('/[^A-Fa-f0-9]/', $text);
    }

    /**
     * Converts integers to their char versions according to normal ctype behaviour, if needed.
     *
     * If an integer between -128 and 255 inclusive is provided,
     * it is interpreted as the ASCII value of a single character
     * (negative values have 256 added in order to allow characters in the Extended ASCII range).
     * Any other integer is interpreted as a string containing the decimal digits of the integer.
     *
     * @param string $function
     * @return mixed
     */
    private static function convert_int_to_char_for_ctype(mixed $int, $function)
    {
        if (!\is_int($int)) {
            return $int;
        }

        if ($int < -128 || $int > 255) {
            return (string) $int;
        }

        @trigger_error($function.'(): Argument of type int will be interpreted as string in the future', \E_USER_DEPRECATED);

        if ($int < 0) {
            $int += 256;
        }

        return \chr($int);
    }
}
