<?php namespace ewma\ui\js\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function increaseVersion()
    {
        $this->c('\ewma~js:increaseVersion');

        $this->reload();
    }

    //
    // compiler
    //

    public function toggleCompiler()
    {
        $this->c('\ewma~js:toggleCompiler');

        $this->reload();
    }

    public function toggleCompilerDevMode()
    {
        $compiler = &$this->d('\ewma~js:compiler');
        invert($compiler['dev_mode']);

        $this->c('\ewma~cache:reset', [
            'jsCompiler' => true
        ]);

        $this->reload();
    }

    public function toggleCompilerMinify()
    {
        $compiler = &$this->d('\ewma~js:compiler');
        invert($compiler['minify']);

        $this->reload();
    }

    public function compilerDirSet()
    {
        $compiler = &$this->d('\ewma~js:compiler');

        $txt = \std\ui\Txt::value($this);

        if ($txt->value) {
            $compiler['dir'] = $txt->value;
            $txt->response();
        } else {
            $txt->response($compiler['dir']);
        }
    }

    public function compilerDevModeDirSet()
    {
        $compiler = &$this->d('\ewma~js:compiler');

        $txt = \std\ui\Txt::value($this);

        if ($txt->value) {
            $compiler['dev_mode_dir'] = $txt->value;
            $txt->response();
        } else {
            $txt->response($compiler['dir']);
        }
    }

    //
    // combiner
    //

    public function toggleCombiner()
    {
        $this->c('\ewma~js:toggleCombiner');

        $this->reload();
    }

    public function toggleCombinerUse()
    {
        $combiner = &$this->d('\ewma~js:combiner');
        invert($combiner['use']);

        $this->reload();
    }

    public function toggleCombinerMinify()
    {
        $combiner = &$this->d('\ewma~js:combiner');
        invert($combiner['minify']);

        $this->reload();
    }

    public function combinerDirSet()
    {
        $combiner = &$this->d('\ewma~js:combiner');

        $txt = \std\ui\Txt::value($this);

        if ($txt->value) {
            $combiner['dir'] = $txt->value;
            $txt->response();
        } else {
            $txt->response($combiner['dir']);
        }
    }

    //
    //
    //

    private function reload()
    {
        $this->c('~:reload');
    }
}
