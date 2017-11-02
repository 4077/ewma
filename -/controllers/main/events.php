<?php namespace ewma\controllers\main;

class Events extends \Controller
{
    public function bindDispatchers()
    {
        if ($dispatchers = $this->_config('eventDispatchers')) {
            foreach ($dispatchers as $dispatcher) {
                $this->app->c($dispatcher . ':bind');
            }
        }
    }
}
