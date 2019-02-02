<?php namespace ewma\controllers\main;

class Multirequest extends \Controller
{
    public $allow = self::XHR;

    public function handle()
    {
        if ($this->dataHas('calls array')) {
            foreach ($this->data['calls'] as $call) {
                $this->_call($call)->perform(\ewma\Controllers\Controller::XHR);
            }
        }
    }
}
