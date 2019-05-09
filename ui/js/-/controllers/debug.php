<?php namespace ewma\ui\js\controllers;

class Debug extends \Controller
{
    public function view()
    {
        $v = $this->v();

        $directory = new \RecursiveDirectoryIterator(public_path('js'));
        $iterator = new \RecursiveIteratorIterator($directory);

        $basePath = public_path() . '/';

        foreach ($iterator as $node) {
            if ($node->isFile()) {
                $path = $this->app->paths->getRelativePath($node->getRealPath(), $basePath);

                $this->app->html->headAppend($this->c('\std\ui tag:view:meta', [
                                                 'attrs' => [
                                                     'type' => 'text/javascript',
                                                     'src'  => $path
                                                 ]
                                             ]) . "\n");
            }
        }

        return $v;
    }
}
