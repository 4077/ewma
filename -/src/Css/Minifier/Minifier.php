<?php namespace ewma\Css\Minifier;

use MatthiasMullie\Minify\CSS;

class Minifier
{
    public static function minify($code)
    {
        $minifier = new CSS();
        $minifier->add($code);

        return $minifier->minify();
    }
}
