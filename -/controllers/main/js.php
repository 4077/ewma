<?php namespace ewma\controllers\main;

class Js extends \Controller
{
    private $d;

    public function __create()
    {
        $this->d = &$this->d();
    }

    public function getData()
    {
        return $this->d;
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

    public function toggleCombinerUse()
    {
        invert($this->d['combiner']['use']);

        return $this->d['combiner']['use'];
    }
}
