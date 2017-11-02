<?php namespace ewma\controllers\main;

class Css extends \Controller
{
    private $d;

    public function __create()
    {
        $this->d = &$this->d();
    }

    public function getVersion()
    {
        return $this->d['version'];
    }

    public function increaseVersion()
    {
        return ++$this->d['version'];
    }

    public function enableCompiler()
    {
        $this->d['compiler']['enabled'] = true;
    }

    public function disableCompiler()
    {
        $this->d['compiler']['enabled'] = false;
    }

    public function toggleCompiler()
    {
        invert($this->d['compiler']['enabled']);

        return $this->d['compiler']['enabled'];
    }

    public function toggleCombiner()
    {
        invert($this->d['combiner']['enabled']);

        return $this->d['combiner']['enabled'];
    }
}
