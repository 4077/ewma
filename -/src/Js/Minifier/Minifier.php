<?php namespace ewma\Js\Minifier;

use MatthiasMullie\Minify\JS;

class Minifier
{
    public static function minify($code)
    {
        $minifier = new JS();
        $minifier->add($code);

        return $minifier->minify();
    }
}
