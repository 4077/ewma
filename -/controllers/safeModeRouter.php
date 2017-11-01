<?php namespace ewma\controllers;

use ewma\Interfaces\RouterInterface;

class SafeModeRouter extends \Controller implements RouterInterface
{
    public function getResponse()
    {
        $this->route('safe-mode/*')->to(':safeMode');

        return $this->routeResponse();
    }

    public function safeMode()
    {
        $this->route('cache*')->to(':cache');

        return $this->routeResponse();
    }

    public function cache()
    {
        $this->route('reset{type}')->to(':cacheReset');

        return $this->routeResponse();
    }

    public function cacheReset()
    {
        $resetData = [];
        if ($type = $this->data('type')) {
            $resetData[$type] = true;
        }

        return $this->c('cache~:reset', $resetData);
    }
}
