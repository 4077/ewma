<?php namespace ewma\remoteCall\controllers;

class Router extends \Controller implements \ewma\Interfaces\RouterInterface
{
    public function getResponse()
    {
        $settings = \std\data\sets\Svc::get('ewma/remoteCall:');

        $this->route($settings['route'])->to('~:handle');

        return $this->routeResponse();
    }
}
