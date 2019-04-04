<?php

namespace tubalmartin\CssMin;

class Utils
{
    /**
     * Clamps a number between a minimum and a maximum value.
     * @param int|float $n the number to clamp
     * @param int|float $min the lower end number allowed
     * @param int|float $max the higher end number allowed
     * @return int|float
     */
    public static function clampNumber($n, $min, $max)
    {
        return min(max($n, $min), $max);
    }

    /**
     * Clamps a RGB color number outside the sRGB color space
     * @param int|float $n the number to clamp
     * @return int|float
     */
    public static function clampNumberSrgb($n)
    {
        return self::clampNumber($n, 0, 255);
    }

    /**
     * Escapes backreferences such as \1 and $1 in a regular expression replacement string
     * @param $string
     * @return string
     */
    public static function escapeReplacementString($string)
    {
        return addcslashes($string, '\\$');
    }

    /**
     * Converts a HSL color into a RGB color
     * @param array $hslValues
     * @return array
     */
    public static function hslToRgb($hslValues)
    {
        $h = floatval($hslValues[0]);
        $s = floatval(str_replace('%', '', $hslValues[1]));
        $l = floatval(str_replace('%', '', $hslValues[2]));

        // Wrap and clamp, then fraction!
        $h = ((($h % 360) + 360) % 360) / 360;
        $s = self::clampNumber($s, 0, 100) / 100;
        $l = self::clampNumber($l, 0, 100) / 100;

        if ($s == 0) {
            $r = $g = $b = self::roundNumber(255 * $l);
        } else {
            $v2 = $l < 0.5 ? $l * (1 + $s) : ($l + $s) - ($s * $l);
            $v1 = (2 * $l) - $v2;
            $r = self::roundNumber(255 * self::hueToRgb($v1, $v2, $h + (1/3)));
            $g = self::roundNumber(255 * self::hueToRgb($v1, $v2, $h));
            $b = self::roundNumber(255 * self::hueToRgb($v1, $v2, $h - (1/3)));
        }

        return array($r, $g, $b);
    }

    /**
     * Tests and selects the correct formula for each RGB color channel
     * @param $v1
     * @param $v2
     * @param $vh
     * @return mixed
     */
    public static function hueToRgb($v1, $v2, $vh)
    {
        $vh = $vh < 0 ? $vh + 1 : ($vh > 1 ? $vh - 1 : $vh);

        if ($vh * 6 < 1) {
            return $v1 + ($v2 - $v1) * 6 * $vh;
        }

        if ($vh * 2 < 1) {
            return $v2;
        }

        if ($vh * 3 < 2) {
            return $v1 + ($v2 - $v1) * ((2 / 3) - $vh) * 6;
        }

        return $v1;
    }

    /**
     * PHP port of Javascript's "indexOf" function for strings only
     * Author: Tubal Martin
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $offset index (optional)
     * @return int
     */
    public static function indexOf($haystack, $needle, $offset = 0)
    {
        $index = strpos($haystack, $needle, $offset);

        return ($index !== false) ? $index : -1;
    }

    /**
     * Convert strings like "64M" or "30" to int values
     * @param mixed $size
     * @return int
     */
    public static function normalizeInt($size)
    {
        if (is_string($size)) {
            $letter = substr($size, -1);
            $size = intval($size);
            switch ($letter) {
                case 'M':
                case 'm':
                    return (int) $size * 1048576;
                case 'K':
                case 'k':
                    return (int) $size * 1024;
                case 'G':
                case 'g':
                    return (int) $size * 1073741824;
            }
        }
        return (int) $size;
    }

    /**
     * Converts a string containing and RGB percentage value into a RGB integer value i.e. '90%' -> 229.5
     * @param $rgbPercentage
     * @return int
     */
    public static function rgbPercentageToRgbInteger($rgbPercentage)
    {
        if (strpos($rgbPercentage, '%') !== false) {
            $rgbPercentage = self::roundNumber(floatval(str_replace('%', '', $rgbPercentage)) * 2.55);
        }

        return intval($rgbPercentage, 10);
    }

    /**
     * Converts a RGB color into a HEX color
     * @param array $rgbColors
     * @return array
     */
    public static function rgbToHex($rgbColors)
    {
        $hexColors = array();

        // Values outside the sRGB color space should be clipped (0-255)
        for ($i = 0, $l = count($rgbColors); $i < $l; $i++) {
            $hexColors[$i] = sprintf("%02x", self::clampNumberSrgb(self::rgbPercentageToRgbInteger($rgbColors[$i])));
        }

        return $hexColors;
    }

    /**
     * Rounds a number to its closest integer
     * @param $n
     * @return int
     */
    public static function roundNumber($n)
    {
        return intval(round(floatval($n)), 10);
    }

    /**
     * PHP port of Javascript's "slice" function for strings only
     * Author: Tubal Martin
     *
     * @param string   $str
     * @param int      $start index
     * @param int|bool $end index (optional)
     * @return string
     */
    public static function strSlice($str, $start = 0, $end = false)
    {
        if ($end !== false && ($start < 0 || $end <= 0)) {
            $max = strlen($str);

            if ($start < 0) {
                if (($start = $max + $start) < 0) {
                    return '';
                }
            }

            if ($end < 0) {
                if (($end = $max + $end) < 0) {
                    return '';
                }
            }

            if ($end <= $start) {
                return '';
            }
        }

        $slice = ($end === false) ? substr($str, $start) : substr($str, $start, $end - $start);
        return ($slice === false) ? '' : $slice;
    }
}
