<?php namespace ewma\installer;

class Main implements \ewma\Interfaces\ModuleInstallerInterface
{
    public function install()
    {
        $c = appc('\ewma~');

        $dir = $c->_module()->dir;

        $copyList = [
            '/-/data/install/favicons' => '-/ewma/favicons'
        ];

        foreach ($copyList as $source => $target) {
            copy_dir(abs_path($dir, $source), public_path($target));
        }
    }
}
