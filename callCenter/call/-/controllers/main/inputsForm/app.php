<?php namespace ewma\callCenter\call\controllers\main\inputsForm;

class App extends \Controller
{
    public function readData()
    {
        $s = $this->s('<|');

        return ap($s['inputs'], $this->data('path'));
    }

    public function writeData()
    {
        $s = &$this->s('<|');

        ap($s['inputs'], $this->data('path'), $this->data('data'));
    }
}
