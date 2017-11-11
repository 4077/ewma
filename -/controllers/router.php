<?php namespace ewma\controllers;

use ewma\Interfaces\RouterInterface;

class Router extends \Controller implements RouterInterface
{
    public function getResponse()
    {
        $this->route('dev/cp')->to('\dev\cp~:view');
        $this->route('dev/routers')->to('\ewma\routers\ui~:view');
        $this->route('dev/modules')->to('\dev~:view');
        $this->route('dev/call-center')->to('\ewma\callCenter~:view');
        $this->route('dev/data-sets')->to('\ewma\dataSets~:view');
        $this->route('dev/access/*')->to('\ewma\access router:getResponse');
        $this->route('dev/handlers')->to('\ewma\handlers\ui~:view');
        $this->route('dev/routers')->to('\ewma\routers\ui~:view');

        $this->route('dev/cache-reset')->to('\ewma~cache:reset', [], function ($response) {
            return implode('<br>', $response);
        });

        $this->route('login')->to('\std\auth login:view');

        return $this->routeResponse();
    }
}
