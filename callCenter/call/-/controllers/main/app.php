<?php namespace ewma\callCenter\call\controllers\main;

class App extends \Controller
{
    public function readData()
    {
        $call = $this->unpackModel('call');

        return _j($call->data);
    }

    public function writeData()
    {
        $call = $this->unpackModel('call');

        $call->data = j_($this->data('data'));
        $call->save();
    }

    public function setCat()
    {
        $cat = \ewma\callCenter\models\Cat::find($this->data('cat_id'));
        $call = \ewma\callCenter\models\Call::find($this->data('call_id'));

        if ($cat && $call) {
            $catIdBefore = $call->cat_id;

            $call->cat()->associate($cat);
            $call->save();

            $this->e('ewma/callCenter/calls/update/cat', [
                'call_id' => $call->id,
                'cat_id'  => $catIdBefore
            ])->trigger(['call' => $call]);
        }
    }
}
