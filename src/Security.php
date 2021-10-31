<?php

namespace Attla;

use voku\helper\AntiXSS;

class Security
{
    /**
     * Filters an input and prevents sql injection, XSS attacks etc
     *
     * @param mixed $str
     * @return mixed
     */
    public static function filterInput($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                unset($str[$key]);
                $str[self::filterKey($key)] = self::filterInput($value);
            }

            return $str;
        }

        $str = stripslashes($str);
        $str = strip_tags($str);
        $str = filter_var($str, FILTER_SANITIZE_STRING);
        $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\xC2\xA0]/', '', $str);
        $str = i('\voku\helper\AntiXSS')->xss_clean($str);
        $str = preg_replace('/\\\\+0+/', '', $str);
        return $str;
    }

    /**
     * Filters an array keys
     *
     * @param string|int $key
     * @return string|int
     */
    public static function filterKey($key)
    {
        if (is_string($key) && is_numeric($key)) {
            return trim($key);
        } elseif (is_int($key)) {
            return $key;
        }

        return preg_replace('/[^\p{L}a-zA-Z0-9\.\/\_\-\(\)]+/u', '_', $key);
    }
}
