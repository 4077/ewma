<?php namespace ewma\controllers\main;

class Process extends \Controller
{
    public function clearTerminatedXPids()
    {
        $this->app->processDispatcher->clearTerminatedXPids();
    }
}
