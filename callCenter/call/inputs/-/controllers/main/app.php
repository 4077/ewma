<?php namespace ewma\callCenter\call\inputs\controllers\main;

class App extends \Controller
{
    public function readData()
    {
        if ($input = \ewma\callCenter\call\inputs\Input::unpack($this->data('input'))) {
            return $input->value->data;
        }
    }

    public function writeData()
    {
        if ($input = \ewma\callCenter\call\inputs\Input::unpack($this->data('input'))) {
            $callInputs = _j($input->call->inputs);

            $callInputs[$input->number]['value']['data'] = $this->data('data');

            $input->call->inputs = j_($callInputs);
            $input->call->save();

            $this->e('ewma/callCenter/calls/update/inputs', ['call_id' => $input->call->id])->trigger(['call' => $input->call]);
        }
    }
}
