<?php namespace ewma\callCenter\controllers\main\output;

class App extends \Controller
{
    public function readOutputData()
    {
        if ($call = $this->unpackModel('call')) {
            return _j($call->last_output);
        }
    }

    public function writeOutputData()
    {
        if ($call = $this->unpackModel('call')) {
            $call->last_output = j_($this->data('data'));
            $call->save();
        }
    }
}
