<?php namespace ewma\controllers\main\process;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function pause($xpid)
    {
        if ($process = $this->app->processDispatcher->openByXpid($xpid)) {
            $process->pause();
        }
    }

    public function resume($xpid)
    {
        if ($process = $this->app->processDispatcher->openByXpid($xpid)) {
            $process->resume();
        }
    }

    public function break($xpid)
    {
        if ($process = $this->app->processDispatcher->openByXpid($xpid)) {
            $process->break();
        }
    }
}
