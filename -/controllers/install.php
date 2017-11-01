<?php namespace ewma\controllers;

class Install extends \Controller
{
    public function run()
    {
        $dir = $this->_module()->getDir();

        $copyList = [
            '/-/install/favicons' => '-/ewma/favicons'
        ];

        foreach ($copyList as $source => $target) {
            copy_dir(abs_path($dir, $source), public_path($target));
        }
    }
}
