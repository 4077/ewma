<?php namespace ewma\access\controllers;

use ewma\Interfaces\RouterInterface;

class Router extends \Controller implements RouterInterface
{
    public function getResponse()
    {
        $this->route('users')->to('ui/users~:view');
        $this->route('groups')->to('ui/groups~:view');
        $this->route('permissions')->to('ui/permissions~:view');

        return $this->routeResponse();
    }
}
