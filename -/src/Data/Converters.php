<?php namespace ewma\Data;

/*
 * P - path
 * A - array
 * F - flat array
 * L - list
 * S - string
 */

class Converters
{
    /**
     * P -> A
     *
     * @param            $path
     * @param bool|false $separator
     *
     * @return array
     */
    public static function pathToArray($path, $separator = false)
    {
        if ($path === false || $path === '') {
            return [];
        }

        $separator = $separator ? '/' . $separator . '/' : '/';

        return explode($separator, $path);
    }

    /**
     * A -> P
     *
     * @param            $array
     * @param bool|false $separator
     *
     * @return string
     */
    public static function arrayToPath($array, $separator = false)
    {
        $separator = $separator ? '/' . $separator . '/' : '/';

        return implode($separator, $array);
    }

    /**
     * A -> F
     *
     * @param $array
     *
     * @return array
     */
    public static function arrayToFlat($array)
    {
        self::$arrayToFlatOutput = [];

        if ($array) {
            self::arrayToFlatRecursion($array);
        }

        return self::$arrayToFlatOutput;
    }

    private static $arrayToFlatOutput = [];

    public static function arrayToFlatRecursion($input, $path = [])
    {
        // empty array considered as value
        if (is_array($input) && $input) {
            foreach ($input as $node => $nodeArray) {
                $path[] = $node;
                self::arrayToFlatRecursion($nodeArray, $path);
                array_pop($path);
            }
        } else {
            self::$arrayToFlatOutput[implode('/', $path)] = $input;
        }
    }

    /**
     * F -> A
     *
     * @param $flat
     *
     * @return array
     */
    public static function flatToArray($flat)
    {
        $output = [];

        if (is_array($flat)) {
            foreach ($flat as $path => $value) {
                Modifiers::arrayNodeAccess($output, $path, $value);
            }
        }

        return $output;
    }

    /**
     * A -> L
     *
     * @param $input
     *
     * @return string
     */
    public static function arrayToList($input)
    {
        return implode(', ', $input);
    }

    /**
     * L -> A
     *
     * @param $input
     *
     * @return array
     */
    public static function listToArray($input)
    {
        $output = [];

        if (is_string($input) && strlen($input)) {
            $output = array_map('trim', explode(',', $input));
        } elseif (is_array($input) && count($input) == 1) {
            $input = array_values($input);

            if (is_string($input[0]) && strlen($input[0])) {
                $output = array_map('trim', explode(',', $input[0]));
            }

            if (is_array($input[0])) {
                $output = $input[0];
            }

            if (is_numeric($input[0])) {
                $output = [$input[0]];
            }
        } else {
            $output = (array)$input;
        }

        return $output;
    }

    /**
     * A -> S
     *
     * @param $array
     *
     * @return string
     */
    public static function arrayToString($array, $cliFormat = false)
    {
        $output = [];

        $flat = static::arrayToFlat($array);

        foreach ($flat as $path => $value) {
            if (empty($value)) {
                $value = '';
            }

            $output[] = $path . '=' . ($cliFormat ? str_replace([' ', '|'], ['\ ', '\|'], $value) : $value);
        }

        return implode(' ', $output);
    }

    /**
     * S -> A
     *
     * @param $string
     *
     * @return string
     */
    public static function stringToArray($string)
    {
        return []; // todo
    }
}