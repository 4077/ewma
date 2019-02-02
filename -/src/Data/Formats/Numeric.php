<?php namespace ewma\Data\Formats;

class Numeric
{
    public static function parseInteger($value)
    {
        $value = round($value);

        return $value;
    }

    public static function parseDecimal($value, $decimals = null)
    {
        $value = str_replace([' ', '&nbsp;'], '', $value);
        $value = (float)str_replace(',', '.', $value);

        if (is_integer($decimals)) {
            $value = number_format($value, $decimals, '.', '');
        }

        return $value;
    }

    public static function trimZeros($value)
    {
        return strpos($value, '.') ? rtrim(rtrim(rtrim($value, '0'), '.'), ',') : $value;
    }
}
